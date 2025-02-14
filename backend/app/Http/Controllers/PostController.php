<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(10);

        return Inertia::render('Dashboard', [
            'user' => auth()->user(),
            'posts' => Post::with('user')->latest()->paginate(10),
                ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);

        return redirect()->route('dashboard');
    }

    public function destroy(Post $post)
    {
    if (auth()->id() !== $post->user_id) {
        return redirect()->route('dashboard')->with('error', '権限がありません');
    }

    $post->delete(); //  `deleted_at` に削除日時を記録（論理削除）

    return redirect()->route('dashboard')->with('success', '投稿を削除しました');
    }

    public function update(Request $request, Post $post)
    {
        // 投稿者本人以外は編集不可
        if (auth()->id() !== $post->user_id) {
            return redirect()->route('dashboard')->with('error', '権限がありません');
        }
    
        // バリデーション
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
    
        // 投稿内容を更新
        $post->update([
            'title' => $request->title,
            'body' => $request->body,
        ]);
    
        return redirect()->route('dashboard')->with('success', '投稿を更新しました');
    }

}