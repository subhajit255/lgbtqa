<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Bug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BugController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/bug/report",
     *     summary="Report a Bug",
     *     description="Allows an authenticated user to report a bug with a description text and an optional image upload.",
     *     tags={"Bug Reporting"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"text"},
     *                 @OA\Property(property="text", type="string", description="Description of the bug"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Optional bug screenshot (max 100MB, format: jpeg/png/webp)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bug reported successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Bug reported successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="response_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The text field is required.")
     *         )
     *     )
     * )
     */
    public function reportBug(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:102400',
        ]);

        if ($validator->fails()) {
            return $this->responseJson(false, 422, $validator->errors()->first(), []);
        }

        try {
            $user = auth()->user();
            $imageName = null;

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_bug_' . uniqid() . '.' . $image->getClientOriginalExtension();
                if (!file_exists(storage_path('app/public/bugs'))) {
                    mkdir(storage_path('app/public/bugs'), 0777, true);
                }
                $image->move(storage_path('app/public/bugs'), $imageName);
            }

            $bug = Bug::create([
                'user_id' => $user->id,
                'text' => $request->text,
                'image' => $imageName,
                'status' => 'pending',
            ]);

            return $this->responseJson(true, 200, 'Bug reported successfully.', $bug);
        } catch (\Throwable $th) {
            return $this->responseJson(false, 500, config('constants.CATCH_ERROR_MSG'), errorLogAndReturn($th));
        }
    }
}
