<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisionBoardBrief extends Model
{
    /** @use HasFactory<\Database\Factories\VisionBoardBriefFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'vision_board_id',
        'summary',
        'data',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function visionBoard(): BelongsTo
    {
        return $this->belongsTo(VisionBoard::class);
    }
}
