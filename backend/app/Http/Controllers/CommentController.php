<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:255',
        ]);
    
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);
    
        return response()->json([
            'comment' => $comment->load('user') // ✅ ユーザー情報も含める
        ]);
    } 
    
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete comment', 'error' => $e->getMessage()], 500);
        }
    }
}