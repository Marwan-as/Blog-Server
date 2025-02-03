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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the created_at date in a human-readable format like '1 hour ago', '2 days ago'.
     * @return Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }


    /**
     * Get the updated_at date in a formatted way.
     * @return Attribute
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => Carbon::parse($value)->diffForHumans(),
        );
    }
}
