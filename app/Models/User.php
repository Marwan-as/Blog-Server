<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\Draft;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'biography',
        'profileImagePath',
        'coverImagePath',
        'showEmail',
        'isAdmin',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }


    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(Draft::class);
    }


    protected  $appends = ['profileImagePath', 'coverImagePath'];

    public function getProfileImagePathAttribute()
    {
        return isset($this->attributes['profileImagePath']) && $this->attributes['profileImagePath']
            ? asset('storage/' . $this->attributes['profileImagePath'])
            : '';
    }

    public function getCoverImagePathAttribute()
    {
        return isset($this->attributes['coverImagePath']) && $this->attributes['coverImagePath']
            ? asset('storage/' . $this->attributes['coverImagePath'])
            : '';
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

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => ucfirst($value),
            get: fn(string $value) => ucfirst($value)
        );
    }
}
