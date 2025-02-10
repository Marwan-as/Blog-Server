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

    protected $appends = ['imagePath', 'savedAt'];

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
            get: fn(string | null $value) => $value ? ucfirst($value) : null,
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

    public function getSavedAtAttribute()
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
