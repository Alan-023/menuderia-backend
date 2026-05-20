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
        Schema::create('dining_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 64);
            $table->string('titular_name', 100);
            $table->enum('status', ['ACTIVE', 'PAYING', 'CLOSED'])->default('ACTIVE');
            $table->integer('opened_by_user_id')->nullable();
            $table->timestamp('opened_at')->nullable()->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dining_sessions');
    }
};
