<?php

use App\Models\User;
use App\Models\VisionBoard;
use App\Models\VisionBoardBrief;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as AssertableInertia;

uses()->group('vision-board-brief');

test('it shows the latest vision board brief', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $visionBoard = VisionBoard::factory()->create([
        'title' => 'Brief board',
        'year' => 2026,
    ]);
    $visionBoard->users()->attach($user);

    VisionBoardBrief::create([
        'vision_board_id' => $visionBoard->id,
        'summary' => 'Résumé important',
        'data' => [
            'themes' => [
                [
                    'title' => 'Santé',
                    'description' => 'Mieux manger et bouger régulièrement',
                    'motivational_phrase' => 'Chaque jour compte',
                ],
            ],
        ],
        'created_at' => now()->subMinutes(5),
    ]);

    $response = $this->actingAs($user)->get(route('vision-boards.brief.show', $visionBoard));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('visionBoard.id', $visionBoard->id)
        ->where('latestBrief.summary', 'Résumé important')
        ->where('latestBrief.data.themes.0.title', 'Santé')
    );
});

test('it stores a vision board brief and redirects to the show page', function () {
    Http::fake([
        '*' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'summary' => 'Résumé de mon brief',
                            'data' => [
                                'themes' => [
                                    [
                                        'title' => 'Bien-être',
                                        'description' => 'Prendre soin de ma santé mentale et physique',
                                        'motivational_phrase' => 'Chaque petit pas compte',
                                    ],
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
        ], 200),
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $visionBoard = VisionBoard::factory()->create([
        'title' => 'Store brief board',
        'year' => 2026,
    ]);
    $visionBoard->users()->attach($user);

    $response = $this->actingAs($user)->post(route('vision-boards.brief.store', $visionBoard), [
        'brief' => 'Je souhaite construire un vision board pour ma santé, ma carrière et mes voyages, avec une orientation famille et bien-être.',
    ]);

    $response->assertRedirect(route('vision-boards.brief.show', $visionBoard));
    $response->assertSessionHas('success', 'Votre brief a ete analyse et enregistre.');

    $this->assertDatabaseHas('vision_board_briefs', [
        'vision_board_id' => $visionBoard->id,
        'summary' => 'Résumé de mon brief',
    ]);

    $storedBrief = VisionBoardBrief::where('vision_board_id', $visionBoard->id)->first();

    expect($storedBrief)->not()->toBeNull();
    expect($storedBrief->data['themes'][0]['title'])->toBe('Bien-être');
});

test('it returns validation errors when the brief is too short', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $visionBoard = VisionBoard::factory()->create([
        'title' => 'Validation board',
        'year' => 2026,
    ]);
    $visionBoard->users()->attach($user);

    $response = $this->actingAs($user)->post(route('vision-boards.brief.store', $visionBoard), [
        'brief' => 'Trop court',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['brief']);
});

test('it returns an error message when the analyzer fails', function () {
    Http::fake([
        '*' => Http::response(['error' => ['message' => 'OpenAI failure']], 500),
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $visionBoard = VisionBoard::factory()->create([
        'title' => 'Failure board',
        'year' => 2026,
    ]);
    $visionBoard->users()->attach($user);

    $response = $this->actingAs($user)->post(route('vision-boards.brief.store', $visionBoard), [
        'brief' => 'Je veux un brief crédible et long enough pour passer la validation.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['brief']);
});
