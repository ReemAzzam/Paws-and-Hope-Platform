<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\PostLike;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class CommunityPostController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityPost::with([
            'category', 
            'animal:id,name,type',
            'likedByUsers' 
        ])->withCount('likes');

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%");
        }

        if ($request->filled('category_slug') && $request->category_slug !== 'all') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        if ($request->filled('pet_type') && $request->pet_type !== 'all') {
            $query->whereHas('animal', function($q) use ($request) {
                $q->where('type', $request->pet_type); 
            });
        }

        $posts = $query->latest()->paginate();

        $posts->getCollection()->transform(function($post) {
            $post->is_liked_by_me = $post->likedByUsers->contains(Auth::id());
            return $post;
        });

        return response()->json([
            'success' => true,
            'posts'   => $posts->items(),
            'meta'    => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(), 
            ]
        ], 200);
    }

    public function toggleLike($id)
    {
        CommunityPost::findOrFail($id); 

        $userId = auth('sanctum')->id(); 

        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $like = PostLike::where('user_id', $userId)->where('post_id', $id)->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'success' => true,
                'liked'   => false,
                'message' => 'Like removed.'
            ], 200);
        } else {
            PostLike::create([
                'user_id' => $userId,
                'post_id' => $id
            ]);
            return response()->json([
                'success' => true,
                'liked'   => true,
                'message' => 'Post liked.'
            ], 201);
        }
    }

    public function getPostLikesData($id)
    {
        $post = CommunityPost::with(['likedByUsers' => function($query) {
            $query->latest('post_likes.created_at'); 
        }])
        ->withCount('likes')
        ->findOrFail($id);

        return response()->json([
            'success'     => true,
            'post_id'     => $post->id,
            'total_likes' => $post->likes_count, 
            'liked_by'    => $post->likedByUsers 
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'animal_id'   => 'required|exists:animals,id',
            'category_id' => 'required|exists:post_categories,id',
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'image'       => 'required|image|mimes:jpeg,png,jpg|max:4096', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $imagePath = $request->file('image')->store('community_posts', 'public');
            $imageUrl = asset('storage/' . $imagePath);

            $post = CommunityPost::create([
                'user_id'     => Auth::id(), 
                'animal_id'   => $request->animal_id,
                'category_id' => $request->category_id,
                'title'       => $request->title,
                'content'     => $request->content,
                'image_path'  => $imageUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post published successfully by Admin.',
                'post'    => $post
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error publishing post.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'content'     => 'sometimes|string',
            'category_id' => 'sometimes|exists:post_categories,id',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $post->fill($request->only(['title', 'content', 'category_id', 'animal_id']));

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $post->image_path));
            }

            $path = $request->file('image')->store('community_posts', 'public');
            $post->image_path = '/storage/' . $path;
        }

        $post->save();

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully!',
            'data'    => $post
        ], 200);
    }

    public function destroy($id)
    {
        $post = CommunityPost::findOrFail($id);

        if ($post->image_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $post->image_path));
        }

        DB::table('post_likes')->where('post_id', $id)->delete();

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post and its interactions deleted successfully.'
        ], 200);
    }

    public function categories()
    {
        $categories = PostCategory::all();
        return response()->json(['success' => true, 'categories' => $categories], 200);
    }

}