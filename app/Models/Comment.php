<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'body',
        'user_id',
        'post_id',
    ];

    protected $appends = ['commentedAt'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }

    public function getCommentedAtAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        if ($this->updated_at && $this->updated_at >= $this->created_at) {
            return Carbon::parse($this->updated_at)->format('Y-m-d');
        }

        return Carbon::parse($this->created_at)->format('Y-m-d');
    }
}
