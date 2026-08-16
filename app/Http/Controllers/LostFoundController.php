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

    // استخدام through لتنسيق الصور داخل كل عنصر في القائمة
    $posts = $query->paginate(12)->through(function ($post) {
        // إضافة مصفوفة images تحتوي على الروابط الكاملة
        $post->images = $post->photos->map(function ($photo) {
            return asset('storage/' . ltrim($photo->photo_url, '/'));
        })->values()->toArray();

        return $post;
    });

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
        'incident_at'          => 'required|date|before_or_equal:now',
        'latitude'             => 'required|numeric|between:-90,90',
        'longitude'            => 'required|numeric|between:-180,180',
        'contact_phone'        => 'nullable|string',
        'contact_email'        => 'nullable|string|email|max:255',
        'distinctive_marks'    => 'nullable|string',
        'collar_tags'          => 'nullable|string',
        'microchipped'         => 'boolean',
        'neutered'             => 'boolean',
        'temperament'          => 'nullable|string',
        'images'               => 'nullable|array',
        'images.*'             => 'image|mimes:jpeg,png,jpg,gif,avif|max:5120', // ← تعديل مهم
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

    $postData['incident_at'] = $request->incident_at;
    $postData['contact_email'] = $request->contact_email;
    $postData['contact_phone'] = $request->contact_phone;
    $postData['status'] = 'open';
    $post = $this->service->createPost($postData);


    // رفع الصور كملفات عادية
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $image) {

        $path = $image->store('lost-found/' . $post->id, 'public');

        LostFoundPhoto::create([
            'lost_found_id' => $post->id,
            'photo_url'     => $path,
            'is_main'       => $index === 0,
            'order_number'  => $index,
        ]);
        }
    }

$post->load('photos');

return response()->json([
    'success' => true,
    'message' => 'تم نشر المنشور بنجاح',
    'data'    => [
        'id'                   => $post->id,
        'post_type'            => $post->post_type,
        'status'               => $post->status, // open | resolved | closed
        'animal_type'          => $post->animal_type,
        'name'                 => $post->name,
        'breed'                => $post->breed,
        'gender'               => $post->gender,
        'size'                 => $post->size,
        'age'                  => $post->age,
        'color'                => $post->color,
        'description'          => $post->description,
        'location_description' => $post->location_description,
        'latitude'             => $post->latitude,
        'longitude'            => $post->longitude,
        'incident_at'          => $post->incident_at,
        'contact_phone'        => $post->contact_phone,
        'contact_email'        => $post->contact_email,
        'distinctive_marks'    => $post->distinctive_marks,
        'collar_tags'          => $post->collar_tags,
        'microchipped'         => (bool) $post->microchipped,
        'neutered'             => (bool) $post->neutered,
        'temperament'          => $post->temperament,
        'user_id'              => $post->user_id,
        'created_at'           => $post->created_at,
        'updated_at'           => $post->updated_at,

        // روابط الصور الكاملة
        'images' => $post->photos->map(function ($photo) {
            return asset('storage/' . ltrim($photo->photo_url, '/'));
        })->values()->toArray(),
    ]
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
        'contact_phone'     => $lostFound->contact_phone,
        'contact_email'     => $lostFound->contact_email,

        'location' => [
            'address'     => $lostFound->location_description,
            'subNotes'    => '',
            'date'        => optional($lostFound->incident_at)->format('M d, Y')
                        ?? $lostFound->created_at->format('M d, Y'),
            'time'        => optional($lostFound->incident_at)->format('g:i A')
                        ?? $lostFound->created_at->format('g:i A'),
            'coordinates' => [$lostFound->latitude, $lostFound->longitude]
            
        ],

        'publisher' => [
            'name'          => $lostFound->user->full_name ?? $lostFound->user->name,
           'avatar' => $lostFound->user?->photo
                ? asset('storage/' . ltrim($lostFound->user->photo, '/'))
                : null,
            'joined'        => $lostFound->user->created_at->format('M Y'),
            'postsCount'    => 0,
            'reunitedCount' => 0,
            'phone'         => $lostFound->contact_phone,
            'email'         => $lostFound->user->email
        ],

      'images' => $lostFound->photos->map(function ($photo) {
                return asset('storage/' . ltrim($photo->photo_url, '/'));
            })->values()->toArray(),
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
        ->latest()
        ->limit(6)
        ->get();

    $data = $similar->map(function ($post) {
        return [
            'id' => $post->id,
            'type' => ucfirst($post->animal_type),
            'status' => $post->post_type === 'lost' ? 'LOST PET' : 'FOUND PET',
            'name' => $post->name ?? 'Unknown',
            'breed' => $post->breed,
            'gender' => ucfirst($post->gender ?? 'Unknown'),
            'size' => ucfirst($post->size ?? 'Unknown'),
            'age' => $post->age,
            'color' => $post->color,
            'description' => $post->description,

           'images' => $post->photos->map(function ($photo) {
                 return asset('storage/' . ltrim($photo->photo_url, '/'));
              })->values()->toArray(),
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $data
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
          Storage::disk('public')->delete($photo->photo_url);
        }

        $lostFound->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنشور بنجاح'
        ]);
    }
    // all my lost&found posts i have posted
    /**
 * كل منشورات Lost & Found الخاصة بالمستخدم الحالي (بدون فلاتر)
 * GET /api/v1/lost-found/my-posts
 */
    public function myPosts(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated. Check your Bearer Token.'
            ], 401);
        }

        $posts = LostFound::with(['photos'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id'                   => $post->id,
                    'post_type'            => $post->post_type,
                    'status'               => $post->status,
                    'animal_type'          => $post->animal_type,
                    'name'                 => $post->name,
                    'breed'                => $post->breed,
                    'gender'               => $post->gender,
                    'size'                 => $post->size,
                    'age'                  => $post->age,
                    'color'                => $post->color,
                    'description'          => $post->description,
                    'location_description' => $post->location_description,
                    'latitude'             => $post->latitude,
                    'longitude'            => $post->longitude,
                    'incident_at'          => $post->incident_at,
                    'contact_phone'        => $post->contact_phone,
                    'distinctive_marks'    => $post->distinctive_marks,
                    'collar_tags'          => $post->collar_tags,
                    'microchipped'         => (bool) $post->microchipped,
                    'neutered'             => (bool) $post->neutered,
                    'temperament'          => $post->temperament,
                    'views'                => $post->views,
                    'images'               => $post->photos->map(function ($photo) {
                        return asset('storage/' . ltrim($photo->photo_url, '/'));
                    })->values()->toArray(),
                    'created_at'           => $post->created_at,
                    'updated_at'           => $post->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $posts->count(),
            'data'    => $posts,
        ]);
    }
}
