<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'title',
        'body',
        'imagePath',
        'privacy',
        'user_id'
    ];

    protected $appends = ['imagePath', 'title', 'body'];

    public function getImagePathAttribute()
    {
        return isset($this->attributes['imagePath']) && $this->attributes['imagePath']
            ? asset('storage/' . $this->attributes['imagePath'])
            : '';
    }

    public function getTitleAttribute()
    {
        return ucfirst($this->attributes['title']);
    }

    public function getBodyAttribute()
    {
        return ucfirst($this->attributes['body']);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
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
}
