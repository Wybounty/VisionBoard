<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisionBoardBriefRequest;
use App\Models\VisionBoard;
use App\Models\VisionBoardBrief;
use App\Services\OpenAI\VisionBoardBriefAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Throwable;

class VisionBoardBriefController extends Controller
{
    public function show(Request $request, VisionBoard $visionBoard)
    {
        $visionBoard = $this->ensureUserOwnsVisionBoard($request, $visionBoard);

        $latestBrief = $visionBoard->briefs()
            ->latest('created_at')
            ->first();

        return Inertia::render('vision-boards/show', [
            'visionBoard' => [
                'id' => $visionBoard->id,
                'slug' => $visionBoard->slug,
                'title' => $visionBoard->title,
                'year' => $visionBoard->year,
            ],
            'latestBrief' => $latestBrief ? [
                'id' => $latestBrief->id,
                'summary' => $latestBrief->summary,
                'data' => $latestBrief->data,
                'created_at' => $latestBrief->created_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function store(
        StoreVisionBoardBriefRequest $request,
        VisionBoard $visionBoard,
        VisionBoardBriefAnalyzer $analyzer,
    ) {
        $visionBoard = $this->ensureUserOwnsVisionBoard($request, $visionBoard);

        try {
            $analysis = $analyzer->analyze($request->validated()['brief']);

            VisionBoardBrief::create([
                'vision_board_id' => $visionBoard->id,
                'summary' => $analysis['summary'],
                'data' => $analysis['data'],
                'created_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            report($throwable);

            return back()
                ->withErrors([
                    'brief' => 'Impossible d\'analyser le brief pour le moment. Reessayez dans un instant.',
                ])
                ->withInput();
        }

        return Redirect::route('vision-boards.brief.show', $visionBoard)
            ->with('success', 'Votre brief a ete analyse et enregistre.');
    }

    protected function ensureUserOwnsVisionBoard(Request $request, VisionBoard $visionBoard): VisionBoard
    {
        abort_unless(
            $request->user()?->visionBoards()->whereKey($visionBoard->getKey())->exists(),
            404,
        );

        return $visionBoard;
    }
}
