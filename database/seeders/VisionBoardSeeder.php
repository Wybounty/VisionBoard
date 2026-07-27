<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VisionBoard;
use Illuminate\Database\Seeder;

class VisionBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        $visionBoards = [
            [
                'title' => 'Vision Board 2024',
                'year' => 2024,
            ],
            [
                'title' => 'Objectifs 2025',
                'year' => 2025,
            ],
            [
                'title' => 'Developpement Personnel',
                'year' => 2026,
            ],
            [
                'title' => 'Carriere & Business',
                'year' => 2027,
            ],
            [
                'title' => 'Reves & Voyages',
                'year' => 2028,
            ],
            [
                'title' => 'FL goat',
                'year' => 2028,
            ],
        ];

        $visionBoardIds = [];

        foreach ($visionBoards as $visionBoard) {
            $visionBoardIds[] = VisionBoard::create($visionBoard)->id;
        }

        $user->visionBoards()->syncWithoutDetaching($visionBoardIds);
    }
}
