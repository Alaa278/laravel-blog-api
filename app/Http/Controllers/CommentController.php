<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id'   => 'required|exists:posts,id',
            'content'   => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id'   => auth()->id(),
            'post_id'   => $request->post_id,
            'content'   => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => [
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
            ],
        ], 201);

    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment,
        ]);
    }
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Allow deletion only for the comment owner
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // delete nested replies
        $comment->replies()->delete();

        // delete basic comment
        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }

}
