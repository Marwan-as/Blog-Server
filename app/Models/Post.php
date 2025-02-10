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

    protected $appends = ['imagePath', 'publishedAt'];

    public function getImagePathAttribute()
    {
        return isset($this->attributes['imagePath']) && $this->attributes['imagePath']
            ? asset('storage/' . $this->attributes['imagePath'])
            : '';
    }


    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
        );
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucfirst($value),
        );
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function getPublishedAtAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        if ($this->updated_at && $this->updated_at >= $this->created_at) {
            return Carbon::parse($this->updated_at)->diffForHumans();
        }

        return Carbon::parse($this->created_at)->diffForHumans();
    }
}
