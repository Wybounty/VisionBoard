<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vision_board_briefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vision_board_id')->constrained()->cascadeOnDelete();
            $table->text('summary');
            $table->json('data');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vision_board_briefs');
    }
};
