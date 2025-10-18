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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->enum('type', ['room', 'inventory'])->default('room');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
