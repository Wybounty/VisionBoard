<?php

use App\Models\User;
use App\Models\VisionBoard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as AssertableInertia;

uses(RefreshDatabase::class);

test('it lists only the authenticated user vision boards', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $userBoard = VisionBoard::factory()->create([
        'title' => 'My board',
        'year' => 2026,
    ]);
    $userBoard->users()->attach($user);

    $otherBoard = VisionBoard::factory()->create([
        'title' => 'Other board',
        'year' => 2025,
    ]);
    $otherBoard->users()->attach($otherUser);

    $response = $this->actingAs($user)->get(route('vision-boards.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('visionBoards.0.title', 'My board')
        ->where('visionBoards.0.year', 2026)
        ->has('visionBoards', 1)
    );
});

test('it stores a vision board and attaches the current user', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('vision-boards.store'), [
        'title' => 'New vision board',
        'year' => 2027,
    ]);

    $response->assertRedirect(route('vision-boards.index'));

    $visionBoard = VisionBoard::query()->where('title', 'New vision board')->firstOrFail();

    expect($visionBoard->year)->toBe(2027);
    expect($visionBoard->users()->pluck('users.id')->all())->toContain($user->id);
});

test('it updates and deletes a vision board', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $visionBoard = VisionBoard::factory()->create([
        'title' => 'Before edit',
        'year' => 2024,
    ]);
    $visionBoard->users()->attach($user);

    $this->actingAs($user)->put(route('vision-boards.update', $visionBoard), [
        'title' => 'After edit',
        'year' => 2028,
    ])->assertRedirect(route('vision-boards.index'));

    $visionBoard->refresh();

    expect($visionBoard->title)->toBe('After edit');
    expect($visionBoard->year)->toBe(2028);

    $this->actingAs($user)->delete(route('vision-boards.destroy', $visionBoard))
        ->assertRedirect(route('vision-boards.index'));

    $this->assertDatabaseMissing('vision_boards', [
        'id' => $visionBoard->id,
    ]);
});
