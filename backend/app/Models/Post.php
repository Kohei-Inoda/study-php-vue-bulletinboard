<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['title', 'body', 'user_id'];
    protected $dates = ['deleted_at'];

    // 投稿は1人のユーザーに属する（ユーザーと関連付け）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 投稿には複数のコメントがつく（コメントとの関連付け）
    public function comments(): HasMany 
    {
        return $this->hasMany(Comment::class);
    }

}