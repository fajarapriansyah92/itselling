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
        Schema::create('api_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('method', 10);
            $table->string('endpoint');
            $table->integer('status_code');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('client_type')->default('Unknown Client');
            $table->integer('response_time_ms')->default(0);
            $table->text('request_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
