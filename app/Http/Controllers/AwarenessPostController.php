<?php

namespace App\Http\Controllers;

use App\Models\AwarenessPost;
use App\Models\Veterinarian;
use App\Models\AwarenessPostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AwarenessPostController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This route is restricted to authorized medical practitioners only.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

      $imagePath = null;

        if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('awareness_posts', 'public');
            }
            $post = AwarenessPost::create([
                'veterinarian_id' => $vet->id,
                'title'           => $request->title,
                'content'         => $request->content,
                'image_url'       => $imagePath,
            ]);
        return response()->json([
            'success' => true,
            'message' => 'Educational post compiled and broadcasted to public feeds successfully.',
            'data'    => $post
        ], 201);
    }

    public function update(Request $request, $id)
{
    $user = $request->user();

    $vet = Veterinarian::where('user_id', $user->id)
        ->where('is_approved', true)
        ->first();

    if (!$vet) {
        return response()->json([
            'success' => false,
            'message' => 'Access denied. Restricted to verified veterinarians.'
        ], 403);
    }

    $post = AwarenessPost::findOrFail($id);

    if ($post->veterinarian_id !== $vet->id) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized action. You can only edit your own posts.'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'title'   => 'sometimes|required|string|max:255',
        'content' => 'sometimes|required|string',
        'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,avif|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    // ✅ تعريف المصفوفة أولاً
    $updateData = [];

    if ($request->filled('title')) {
        $updateData['title'] = $request->title;
    }

    if ($request->filled('content')) {
        $updateData['content'] = $request->content;
    }

    if ($request->hasFile('image')) {
        if ($post->image_url) {
            Storage::disk('public')->delete($post->image_url);
        }

        $path = $request->file('image')->store('awareness_posts', 'public');
        $updateData['image_url'] = $path;
    }

    if (empty($updateData)) {
        return response()->json([
            'success' => false,
            'message' => 'No data provided to update.'
        ], 422);
    }

    $post->update($updateData);
    $post->refresh();

    return response()->json([
        'success' => true,
        'message' => 'Educational post updated successfully.',
        'data'    => [
            'id'         => $post->id,
            'title'      => $post->title,
            'content'    => $post->content,
            'image_url'  => $post->image_url
                ? asset('storage/' . ltrim($post->image_url, '/'))
                : null,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
        ]
    ], 200);
}

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $vet = Veterinarian::where('user_id', $user->id)->where('is_approved', true)->first();

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Restricted to verified veterinarians.'
            ], 403);
        }

        $post = AwarenessPost::findOrFail($id);

        if ($post->veterinarian_id !== $vet->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. You can only delete your own articles.'
            ], 403);
        }

        if ($post->image_url) {
        if (Storage::disk('public')->exists($post->image_url)) {
            Storage::disk('public')->delete($post->image_url);
        }
    }
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Educational post and associated media assets cleared from the system.'
        ], 200);
    }

    public function toggleLike(Request $request, $id)
    {
        $userId = $request->user()->id;

        $post = AwarenessPost::findOrFail($id);

        $existingLike = AwarenessPostLike::where('user_id', $userId)
                                        ->where('awareness_post_id', $id)
                                        ->first();

        if ($existingLike) {
            $existingLike->delete();
            $message = 'Like removed successfully.';
            $liked = false;
        } else {
            AwarenessPostLike::create([
                'user_id'           => $userId,
                'awareness_post_id' => $id
            ]);
            $message = 'Post liked successfully.';
            $liked = true;
        }

        return response()->json([
            'success'     => true,
            'message'     => $message,
            'liked'       => $liked,
            'likes_count' => $post->likes()->count()
        ], 200);
    }

    public function index()
    {
        $posts = AwarenessPost::with(['veterinarian.user:id,full_name,photo'])
            ->withCount('likes')
            ->latest()
            ->paginate(10)
            ->through(function ($post) {
                return [
                    'id'         => $post->id,
                    'title'      => $post->title,
                    'content'    => $post->content,
                    'image_url'  => $post->image_url
                        ? asset('storage/' . ltrim($post->image_url, '/'))
                        : null,
                    'likes_count'=> $post->likes_count,
                    'created_at' => $post->created_at,
                    'veterinarian' => [
                        'id'        => $post->veterinarian?->id,
                        'full_name' => $post->veterinarian?->user?->full_name,
                        'photo'     => $post->veterinarian?->user?->photo
                            ? asset('storage/' . ltrim($post->veterinarian->user->photo, '/'))
                            : null,
                    ],
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $posts,
        ], 200);
    }

    public function getPostLikers($id)
    {
        $post = AwarenessPost::findOrFail($id);

        $likers = $post->likedByUsers()->latest('awareness_post_likes.created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $likers
        ], 200);
    }

    public function getPostAwarenessLikesCount($postId)
        {
            $post = AwarenessPost::find($postId);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'The provided post ID does not exist.'
                ], 404);
            }

            $likesCount = AwarenessPostLike::where('awareness_post_id', $postId)->count();

            return response()->json([
                'success'   => true,
                'post_id'   => $postId,
                'likes_count' => $likesCount
            ], 200);
        }

    
   public function getVeterinarianTips($vetId)
    {
        $vet = \App\Models\Veterinarian::with('user:id,full_name,photo')->find($vetId);

        if (!$vet) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian not found.'
            ], 404);
        }
        $tipsCount = \App\Models\AwarenessPost::query()
            ->where('veterinarian_id', $vet->id)->count();
        $tips = \App\Models\AwarenessPost::query()
            ->where('veterinarian_id', $vet->id)
            // إذا البوستات مربوطة بـ user_id استخدم هذا بدل السطر فوق:
            // ->where('user_id', $vet->user_id)
            ->latest()
            ->paginate(12)
            ->through(function ($tip) {
                return [
                    'id'         => $tip->id,
                    'title'      => $tip->title ?? null,
                    'content'    => $tip->content ?? $tip->body ?? null,
                    'created_at' => $tip->created_at,
                    'updated_at' => $tip->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'total' => $tipsCount,
            'data' => [
                'veterinarian' => [
                    'id'        => $vet->id,
                    'full_name' => $vet->user?->full_name,
                    'photo'     => $vet->user?->photo
                        ? asset('storage/' . ltrim($vet->user->photo, '/'))
                        : null,
                ],
                'tips' => $tips,
            ]
        ]);}

}
