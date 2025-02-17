<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Services\GeminiService;
use Inertia\Inertia;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(10);

        return Inertia::render('Dashboard', [
            'user' => auth()->user(),
            'posts' => Post::with(['user', 'comments.user'])->latest()->paginate(10),
                ]);
    }

    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
        ]);
        // **Gemini AI を使って自動コメントを生成**
        $generatedComment = $this->geminiService->generateComment($request->body);

        $comment = null;

        // **AI のコメントを保存**
        if ($generatedComment) {
            $comment = Comment::create([
                'post_id' => $post->id,  // ✅ `$post` をしっかり定義
                'user_id' => 1, // AIユーザーの ID
                'body' => $generatedComment,
            ]);
        }        

        return response()->json([
            'post' => $post,
            'comment' => $comment,
        ]);
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

    public function show(Post $post)
    {
    return response()->json([
        'post' => $post->load('user', 'comments.user')
    ]);
    }

    

}