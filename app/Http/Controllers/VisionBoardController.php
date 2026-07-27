<?php

namespace App\Http\Controllers;

use App\Http\Requests\VisionBoardRequest;
use App\Models\VisionBoard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisionBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $visionBoards = $request->user()
            ->visionBoards()
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('vision-boards/index', [
            'visionBoards' => $visionBoards->map(fn (VisionBoard $visionBoard) => [
                'id' => $visionBoard->id,
                'slug' => $visionBoard->slug,
                'title' => $visionBoard->title,
                'year' => $visionBoard->year,
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VisionBoardRequest $request)
    {
        $visionBoard = VisionBoard::create($request->validated());
        $visionBoard->users()->attach($request->user()->id);

        return redirect()->route('vision-boards.index')->with('success', 'Vision board created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VisionBoardRequest $request, VisionBoard $visionBoard)
    {
        $visionBoard = $request->user()->visionBoards()->find($visionBoard->id);

        if ($visionBoard === null) {
            abort(404);
        }

        $visionBoard->update($request->validated());
        $visionBoard->users()->syncWithoutDetaching([$request->user()->id]);

        return redirect()->route('vision-boards.index')->with('success', 'Vision board updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, VisionBoard $visionBoard)
    {
        $visionBoard = $request->user()->visionBoards()->find($visionBoard->id);

        if ($visionBoard === null) {
            abort(404);
        }

        $visionBoard->users()->detach($request->user()->id);

        if ($visionBoard->users()->count() === 0) {
            $visionBoard->delete();
        }

        return redirect()->route('vision-boards.index')->with('success', 'Vision board deleted successfully.');
    }
}
