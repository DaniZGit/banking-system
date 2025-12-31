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
        Schema::create("accounts", function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum("type", ["personal", "savings", "business"]);
            $table->enum("currency", ["EUR", "USD", "YEN"]);
            $table->unsignedBigInteger("balance")->default(0);
            $table->enum("status", ["active", "blocked", "closed"]);
            $table->foreignUuid('customer_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("accounts");
    }
};
