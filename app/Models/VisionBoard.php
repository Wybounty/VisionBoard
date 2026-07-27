<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VisionBoard extends Model
{
    /** @use HasFactory<\Database\Factories\VisionBoardFactory> */
    use HasFactory;

    protected $fillable = ['title', 'year'];

    protected static function booted(): void
    {
        static::creating(function (self $visionBoard): void {
            if (blank($visionBoard->slug)) {
                $visionBoard->slug = static::generateUniqueSlug($visionBoard->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The briefs attached to the vision board.
     */
    public function briefs(): HasMany
    {
        return $this->hasMany(VisionBoardBrief::class);
    }

    /**
     * The users associated with the vision board.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vision_boards')
            ->withTimestamps();
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'vision-board';
        $slug = $baseSlug;
        $counter = 2;

        while (static::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
