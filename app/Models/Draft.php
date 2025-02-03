<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Draft extends Model
{
    protected $fillable = [
        'title',
        'body',
        'imagePath',
        'privacy',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $appends = ['imagePath', 'title', 'body'];

    public function getImagePathAttribute()
    {
        return isset($this->attributes['imagePath']) && $this->attributes['imagePath']
            ? asset('storage/' . $this->attributes['imagePath'])
            : '';
    }

    public function getTitleAttribute()
    {
        return isset($this->attributes['title']) && $this->attributes['title'] ? ucfirst($this->attributes['title']) : '';
    }

    public function getBodyAttribute()
    {
        return isset($this->attributes['body']) && $this->attributes['body'] ? ucfirst($this->attributes['body']) : '';
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
