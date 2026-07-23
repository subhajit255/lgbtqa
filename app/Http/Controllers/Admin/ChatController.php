<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Events\NewNotification;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $admin = auth()->user();

        // Get all users for starting new chat (excluding the current admin)
        $users = User::where('id', '!=', $admin->id)->get();

        // Get chats where admin is a participant
        $chats = Chat::whereHas('participants', function ($query) use ($admin) {
            $query->where('user_id', $admin->id);
        })
            ->with(['users' => function ($q) {
                $q->select('users.id', 'name', 'profile_image');
            }])
            ->with('latestMessage')
            ->latest('updated_at')
            ->get();

        return view('admin.chat.index', compact('chats', 'users'));
    }

    public function getMessages(Chat $chat)
    {
        $messages = $chat->messages()
            ->with(['sender:id,name,profile_image', 'replyToMessage', 'forwardedFromMessage'])
            ->get();

        return response()->json(['status' => true, 'data' => $messages]);
    }

    public function getParticipants(Chat $chat)
    {
        $participants = $chat->users()->select('users.id', 'name', 'profile_image', 'email')->get();
        return response()->json(['status' => true, 'data' => $participants]);
    }

    public function sendMessage(Request $request, Chat $chat)
    {
        $request->validate([
            'type' => 'required|in:text,image,video,audio,recorded_audio',
            'message' => 'nullable|string',
            'reply_to_message_id' => 'nullable|exists:messages,id',
            'attachment' => 'nullable|file',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('public/chat_attachments', $fileName);
            $attachmentPath = str_replace('public/', '', $attachmentPath);
        }

        $message = Message::create([
            'chat_id' => $chat->id,
            'sender_id' => auth()->user()->id,
            'type' => $request->type,
            'message' => $request->message,
            'reply_to_message_id' => $request->reply_to_message_id,
            'attachment' => $attachmentPath,
        ]);

        $chat->touch();

        broadcast(new MessageSent($message))->toOthers();

        // Send notification to other participants
        $otherParticipants = $chat->participants()->where('user_id', '!=', auth()->id())->get();
        foreach ($otherParticipants as $participant) {
            $notification = Notification::create([
                'user_id' => $participant->user_id,
                'for' => 1, // Assuming 1 is for admin notifications
                'title' => 'New message from ' . auth()->user()->name,
                'description' => $message->message ?? 'Sent an attachment',
                'is_read' => 0,
                'chat_id' => $chat->id,
                'type' => 'chat_message'
            ]);

            broadcast(new NewNotification($notification))->toOthers();
        }

        return response()->json([
            'status' => true,
            'data' => $message->load(['sender:id,name,profile_image', 'replyToMessage']),
        ]);
    }

    public function editMessage(Request $request, Message $message)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

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

    public function deleteMessage(Request $request, Message $message)
    {
        $message->delete();

        return response()->json([
            'status' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function togglePinMessage(Request $request, Message $message)
    {
        if ($message->type !== 'text') {
            return response()->json([
                'status' => false,
                'message' => 'Only text messages can be pinned'
            ], 422);
        }

        $message->is_pinned = !$message->is_pinned;
        $message->save();

        return response()->json([
            'status' => true,
            'message' => $message->is_pinned ? 'Message pinned successfully' : 'Message unpinned successfully',
            'is_pinned' => $message->is_pinned,
            'data' => $message
        ]);
    }

    public function forwardMessage(Request $request, Message $message)
    {
        $request->validate([
            'target_chat_ids' => 'required|array|min:1',
            'target_chat_ids.*' => 'exists:chats,id',
        ]);

        $forwarded = [];
        $adminId = auth()->id();

        foreach ($request->target_chat_ids as $chatId) {
            $newMessage = Message::create([
                'chat_id' => $chatId,
                'sender_id' => $adminId,
                'type' => $message->type,
                'message' => $message->message,
                'attachment' => $message->attachment,
                'is_forwarded' => true,
                'forwarded_from_message_id' => $message->id,
            ]);

            Chat::find($chatId)?->touch();
            broadcast(new MessageSent($newMessage))->toOthers();
            $forwarded[] = $newMessage;
        }

        return response()->json([
            'status' => true,
            'message' => 'Message forwarded successfully',
            'data' => $forwarded
        ]);
    }

    public function startChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $admin = auth()->user();
        $otherUserId = $request->user_id;

        // Check if 1-on-1 chat already exists
        $chat = Chat::where('is_group', false)
            ->whereHas('participants', function ($q) use ($admin) {
                $q->where('user_id', $admin->id);
            })
            ->whereHas('participants', function ($q) use ($otherUserId) {
                $q->where('user_id', $otherUserId);
            })
            ->with(['users' => function ($q) {
                $q->select('users.id', 'name', 'profile_image');
            }])
            ->first();

        if (! $chat) {
            $chat = Chat::create([
                'is_group' => false,
            ]);
            $chat->participants()->createMany([
                ['user_id' => $admin->id],
                ['user_id' => $otherUserId],
            ]);

            $chat = Chat::with(['users' => function ($q) {
                $q->select('users.id', 'name', 'profile_image');
            }])->find($chat->id);
        }

        return response()->json([
            'status' => true,
            'data' => $chat,
        ]);
    }
}
