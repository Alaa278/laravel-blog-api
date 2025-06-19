<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('user')->get();

        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|array',
            'content'  => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $slug = Str::slug($request->title[app()->getLocale()]);

        $count = Post::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $post = Post::create([
            'title'   => $request->title,
            'content' => $request->content,
            'slug'    => $slug,
            'user_id' => auth()->id(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $post->addMedia($image)->toMediaCollection('images');
            }
        }

        return response()->json([
            'message' => 'Post created successfully',
            'post'    => $post,
            'images'  => $post->getMedia('images')->map(fn($media) => $media->getUrl()),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        return response()->json([
            'post' => [
                'id'       => $post->id,
                'title'    => $post->getTranslations('title'),
                'content'  => $post->getTranslations('content'),
                'slug'     => $post->slug,
                'author'   => $post->user->name,
                'images'   => $post->getMedia('images')->map(fn($m) => [
                    'id'  => $m->id,
                    'url' => $m->getUrl(),
                ]),
                'comments' => $post->comments()
                    ->whereNull('parent_id')
                    ->with(['user', 'replies.user'])
                    ->get()
                    ->map(function ($comment) {
                        return [
                            'id'      => $comment->id,
                            'content' => $comment->content,
                            'author'  => $comment->user->name,
                            'replies' => $comment->replies->map(function ($reply) {
                                return [
                                    'id'      => $reply->id,
                                    'content' => $reply->content,
                                    'author'  => $reply->user->name,
                                ];
                            }),
                        ];
                    }),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->filled('title')) {
            $newTitle = $post->getTranslations('title');
            foreach ($request->title as $lang => $value) {
                $newTitle[$lang] = $value;
            }
            $post->setTranslations('title', $newTitle);
        }

        if ($request->filled('content')) {
            $newContent = $post->getTranslations('content');
            foreach ($request->content as $lang => $value) {
                $newContent[$lang] = $value;
            }
            $post->setTranslations('content', $newContent);
        }

        if ($request->filled('slug') && $request->slug !== $post->slug) {
            $newSlug = Str::slug($request->slug);

            $count = Post::where('slug', $newSlug)
                ->where('id', '!=', $post->id)
                ->count();

            if ($count) {
                $newSlug .= '-' . ($count + 1);
            }

            $post->slug = $newSlug;
        }

        $post->save();

        return response()->json([
            'message' => 'Post updated successfully',
            'post'    => $post,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    public function deleteImage($slug, $mediaId)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $media = $post->media()->where('id', $mediaId)->first();

        if (! $media) {
            return response()->json(['error' => 'Image not found for this post'], 404);
        }

        $media->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

}
