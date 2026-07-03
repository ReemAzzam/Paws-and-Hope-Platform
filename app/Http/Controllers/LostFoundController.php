<?php

namespace App\Http\Controllers;

use App\Models\LostFound;
use App\Models\LostFoundView;
use App\Models\LostFoundPhoto;
use App\Services\LostFoundService;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class LostFoundController extends Controller
{
    protected $service;

    public function __construct(LostFoundService $service)
    {
        $this->service = $service;
    }

    // ====================== قائمة المنشورات ======================
    public function index(Request $request)
    {
        $query = LostFound::with(['user', 'photos'])
            ->where('status', 'open')
            ->latest();

        if ($request->filled('post_type')) {
            $query->where('post_type', $request->post_type);
        }
        if ($request->filled('animal_type')) {
            $query->where('animal_type', $request->animal_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('location_description', 'like', "%$search%");
            });
        }

        $posts = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data'    => $posts
        ]);
    }

    // ====================== إنشاء منشور ======================

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'post_type'            => 'required|in:lost,found',
        'animal_type'          => 'required|in:dog,cat,bird,rabbit,other',
        'name'                 => 'nullable|string|max:100',
        'breed'                => 'nullable|string',
        'gender'               => 'nullable|in:male,female,unknown',
        'size'                 => 'nullable|in:small,medium,large',
        'age'                  => 'nullable|string',
        'color'                => 'nullable|string',
        'description'          => 'required|string|min:30',
        'location_description' => 'required|string|min:10',
        'latitude'             => 'required|numeric|between:-90,90',
        'longitude'            => 'required|numeric|between:-180,180',
        'contact_phone'        => 'nullable|string',
        'distinctive_marks'    => 'nullable|string',
        'collar_tags'          => 'nullable|string',
        'microchipped'         => 'boolean',
        'neutered'             => 'boolean',
        'temperament'          => 'nullable|string',
        'images'               => 'nullable|array',
        'images.*'             => 'image|mimes:jpeg,png,jpg,gif|max:5120', // ← تعديل مهم
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors'  => $validator->errors()
        ], 422);
    }

    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated. Check your Bearer Token.'
        ], 401);
    }

    $postData = $request->except('images');
    $postData['user_id'] = $user->id;

    $post = $this->service->createPost($postData);

    // رفع الصور كملفات عادية
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('lost-found/' . $post->id, 'public');

            LostFoundPhoto::create([
                'lost_found_id' => $post->id,
                'photo_url'     => Storage::url($path),
                'is_main'       => $index === 0,
                'order_number'  => $index,
            ]);
        }
    }

    $post->load('photos');

    return response()->json([
        'success' => true,
        'message' => 'تم نشر المنشور بنجاح',
        'data'    => $post
    ], 201);
}


    // ====================== عرض منشور واحد ======================
      public function show(LostFound $lostFound)
{
    $lostFound->load(['user', 'photos']);

    // زيادة المشاهدات
    if (auth()->check()) {
        LostFoundView::firstOrCreate([
            'lost_found_id' => $lostFound->id,
            'user_id'       => auth()->id(),
        ], [
            'ip_address' => request()->ip(),
        ]);
    } else {
        LostFoundView::firstOrCreate([
            'lost_found_id' => $lostFound->id,
            'ip_address'    => request()->ip(),
        ]);
    }

    $lostFound->increment('views');
    $lostFound->refresh();

    $data = [
        'id'                => $lostFound->id,
        'type'              => ucfirst($lostFound->animal_type),
        'status'            => $lostFound->post_type === 'lost' ? 'LOST PET' : 'FOUND PET',
        'name'              => $lostFound->name ?? 'Unknown',
        'breed'             => $lostFound->breed,
        'gender'            => ucfirst($lostFound->gender ?? 'Unknown'),
        'size'              => ucfirst($lostFound->size ?? 'Unknown'),
        'age'               => $lostFound->age,
        'color'             => $lostFound->color,
        'views'             => $lostFound->views,
        'distinctiveMarks'  => $lostFound->distinctive_marks,
        'collarTags'        => $lostFound->collar_tags,
        'microchipped'      => $lostFound->microchipped ? 'Yes' : 'No',
        'neutered'          => $lostFound->neutered ? 'Yes' : 'No',
        'temperament'       => $lostFound->temperament,
        'description'       => $lostFound->description,

        'location' => [
            'address'     => $lostFound->location_description,
            'subNotes'    => '',
            'date'        => $lostFound->created_at->format('M d, Y'),
            'time'        => $lostFound->created_at->format('g:i A'),
            'coordinates' => [$lostFound->latitude, $lostFound->longitude]
        ],

        'publisher' => [
            'name'          => $lostFound->user->full_name ?? $lostFound->user->name,
            'avatar'        => '/images/default-avatar.png',
            'joined'        => $lostFound->user->created_at->format('M Y'),
            'postsCount'    => 0,
            'reunitedCount' => 0,
            'phone'         => $lostFound->contact_phone,
            'email'         => $lostFound->user->email
        ],

        // مسار الصور من جدول lost_found_photos
        'images' => $lostFound->photos->map(function ($photo) {
            $filename = basename($photo->photo_url);
            return "/images/" . $filename;   // الشكل المطلوب
        })->toArray()
    ];

    return response()->json([
        'success' => true,
        'data'    => $data
    ]);
}
    // ====================== Similar Posts ======================
    public function similarPosts(LostFound $lostFound)
    {
        $similar = LostFound::where('id', '!=', $lostFound->id)
            ->where('status', 'open')
            ->where('animal_type', $lostFound->animal_type)
            ->with('photos')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $similar
        ]);
    }

    // ====================== تغيير حالة المنشور ======================
    public function updateStatus(Request $request, LostFound $lostFound)
    {
        if ($lostFound->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا المنشور'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,resolved,closed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $lostFound->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة المنشور بنجاح',
            'data'    => $lostFound
        ]);
    }

    // ====================== حذف منشور ======================
    public function destroy(Request $request, LostFound $lostFound)
    {
        if ($lostFound->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا المنشور'
            ], 403);
        }

        // حذف الصور من التخزين
        foreach ($lostFound->photos as $photo) {
            $path = str_replace('/storage/', '', $photo->photo_url);
            Storage::disk('public')->delete($path);
        }

        $lostFound->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنشور بنجاح'
        ]);
    }
}
