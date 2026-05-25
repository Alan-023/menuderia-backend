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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->bigInteger('session_id');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['PENDING', 'COOKING', 'SERVED', 'CANCELLED'])->default('PENDING');
            $table->timestamp('created_at')->nullable()->useCurrent();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
