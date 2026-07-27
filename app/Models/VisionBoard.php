<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisionBoard extends Model
{
    /** @use HasFactory<\Database\Factories\VisionBoardFactory> */
    use HasFactory;

    protected $fillable = ['title', 'year'];

    /**
     * The users associated with the vision board.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vision_boards')
            ->withTimestamps();
    }
}
