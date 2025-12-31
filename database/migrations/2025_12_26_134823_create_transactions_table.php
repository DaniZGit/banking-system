<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum("type", ["deposit", "withdrawal", "transfer"]);
            $table->bigInteger("amount")->default(0);
            $table->foreignUuid("source_account_id")->nullable()->constrained(table: 'accounts');
            $table->foreignUuid("target_account_id")->nullable()->constrained(table: 'accounts');
            $table->enum("status", ["success", "rejected"]);
            $table->text("rejection_reason")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
