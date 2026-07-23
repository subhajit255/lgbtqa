<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ChatMessageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/chats/{chat}/messages",
     *     summary="Get Messages in a Chat",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Messages retrieved successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, Chat $chat)
    {
        // Check if user is in chat
        if (!$chat->participants()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $perPage = $request->input('per_page') ?? 20;
        $page = $request->input('page_number') ?? $request->input('page_no') ?? $request->input('page') ?? 1;

        $messagesPaginator = $chat->messages()
            ->with(['sender:id,name,profile_image', 'replyToMessage', 'forwardedFromMessage', 'reactions'])
            ->latest()
            ->paginate($perPage, ['*'], 'page_number', $page);

        return $this->responseJsonPaginated(
            true,
            200,
            'Messages retrieved successfully',
            $messagesPaginator
        );
    }

    /**
     * @OA\Post(
     *     path="/api/chats/{chat}/messages",
     *     summary="Send a Message (or Reply)",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="chat",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"type"},
     *                 @OA\Property(property="type", type="string", enum={"text", "image", "video", "audio", "recorded_audio", "file", "document"}),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="reply_to_message_id", type="integer", description="Optional ID of message to reply to"),
     *                 @OA\Property(property="attachment", type="string", format="binary")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Message sent successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=422, description="Invalid message data"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request, Chat $chat)
    {
        if (!$chat->participants()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        if ($chat->is_locked && !auth()->user()->hasRole('superadmin')) {
            return response()->json(['status' => false, 'message' => 'This chat is locked by admin'], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:text,image,video,audio,recorded_audio,file,document',
            'message' => 'nullable|string',
            'reply_to_message_id' => 'nullable|exists:messages,id',
            'attachment' => 'nullable|file|max:102400',
        ]);

        if ($validator->fails() || ($request->type === 'text' && !$request->message && !$request->hasFile('attachment'))) {
            return response()->json(['status' => false, 'message' => 'Invalid message data'], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('public/chat_attachments', $fileName);
            $attachmentPath = str_replace('public/', '', $attachmentPath);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $request->user()->id,
            'type' => $request->type,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
            'attachment' => $attachmentPath
        ]);

        $chat->touch();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load(['sender:id,name,profile_image', 'replyToMessage'])
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/{message}",
     *     summary="Edit a Message",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"message"},
     *             @OA\Property(property="message", type="string", example="Updated text message")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Message updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(Request $request, Message $message)
    {
        $user = $request->user();
        if ($message->sender_id !== $user->id && !$user->hasRole('superadmin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $message->update([
            'message' => $request->message,
            'is_edited' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message updated successfully',
            'data' => $message->load(['sender:id,name,profile_image', 'replyToMessage', 'forwardedFromMessage'])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/messages/{message}",
     *     summary="Delete a Message",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Message deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access")
     * )
     */
    public function destroy(Request $request, Message $message)
    {
        $user = $request->user();
        $isParticipant = $message->chat->participants()->where('user_id', $user->id)->exists();

        if (!$isParticipant) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        if ($message->sender_id !== $user->id && !$user->hasRole('superadmin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $message->delete();

        return response()->json([
            'status' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/{message}/pin",
     *     summary="Pin or Unpin a Message (Text messages only)",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Message pin status toggled successfully"),
     *     @OA\Response(response=422, description="Only text messages can be pinned"),
     *     @OA\Response(response=403, description="Unauthorized Access")
     * )
     */
    public function togglePin(Request $request, Message $message)
    {
        $user = $request->user();
        if (!$message->chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        if ($message->type !== 'text') {
            return response()->json(['status' => false, 'message' => 'Only text messages can be pinned'], 422);
        }

        $message->is_pinned = !$message->is_pinned;
        $message->save();

        return response()->json([
            'status' => true,
            'message' => $message->is_pinned ? 'Message pinned successfully' : 'Message unpinned successfully',
            'is_pinned' => $message->is_pinned,
            'data' => $message->load(['sender:id,name,profile_image'])
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/{message}/forward",
     *     summary="Forward a Message to target chat(s)",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID to forward",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"target_chat_ids"},
     *             @OA\Property(property="target_chat_ids", type="array", @OA\Items(type="integer"), example={2,5})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Message forwarded successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function forward(Request $request, Message $message)
    {
        $user = $request->user();
        if (!$message->chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'target_chat_ids' => 'required|array|min:1',
            'target_chat_ids.*' => 'exists:chats,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $forwardedMessages = [];

        foreach ($request->target_chat_ids as $chatId) {
            $targetChat = Chat::find($chatId);
            if ($targetChat && $targetChat->participants()->where('user_id', $user->id)->exists()) {
                $newMessage = Message::create([
                    'chat_id' => $targetChat->id,
                    'sender_id' => $user->id,
                    'type' => $message->type,
                    'message' => $message->message,
                    'attachment' => $message->attachment,
                    'is_forwarded' => true,
                    'forwarded_from_message_id' => $message->id,
                ]);

                $targetChat->touch();
                broadcast(new MessageSent($newMessage))->toOthers();
                $forwardedMessages[] = $newMessage->load(['sender:id,name,profile_image', 'forwardedFromMessage']);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Message forwarded successfully',
            'data' => $forwardedMessages
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/{message}/read-by",
     *     summary="Get users who have read the message",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Users retrieved successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access")
     * )
     */
    public function getReadBy(Request $request, Message $message)
    {
        $user = $request->user();
        if (!$message->chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $participants = $message->chat->participants()
            ->where('last_read_message_id', '>=', $message->id)
            ->where('user_id', '!=', $user->id)
            ->with('user:id,name,profile_image')
            ->get();

        $readByUsers = $participants->map(function ($participant) {
            $userData = $participant->user;
            $userData->read_at = $participant->last_read_at;
            return $userData;
        });

        return response()->json([
            'status' => true,
            'message' => 'Read by list retrieved successfully',
            'data' => $readByUsers
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages/{message}/react",
     *     summary="React to a Message",
     *     tags={"Chat Message"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="message",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reaction"},
     *             @OA\Property(property="reaction", type="string", example="👍")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reaction updated successfully"),
     *     @OA\Response(response=403, description="Unauthorized Access"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function react(Request $request, Message $message)
    {
        $user = $request->user();
        if (!$message->chat->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'reaction' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $existingReaction = \App\Models\MessageReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('reaction', $request->reaction)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
            $action = 'removed';
            
            // Broadcast the removal if needed, but for now we just remove it
            // broadcast(new \App\Events\MessageReacted($existingReaction))->toOthers(); // Event might not support deleted reactions explicitly
        } else {
            $newReaction = \App\Models\MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'reaction' => $request->reaction,
            ]);
            $action = 'added';
            
            broadcast(new \App\Events\MessageReacted($newReaction))->toOthers();
        }

        return response()->json([
            'status' => true,
            'message' => 'Reaction ' . $action . ' successfully'
        ]);
    }
}
