<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('status');
            $table->string('payment_status')->nullable()->after('transaction_id');
            $table->timestamp('payment_date')->nullable()->after('payment_status');
        });

        // Modify ENUM using raw SQL as Laravel requires doctrine/dbal for changing enum columns
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'COOKING', 'SERVED', 'CANCELLED', 'PAID') DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_status', 'payment_date']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'COOKING', 'SERVED', 'CANCELLED') DEFAULT 'PENDING'");
    }
};
