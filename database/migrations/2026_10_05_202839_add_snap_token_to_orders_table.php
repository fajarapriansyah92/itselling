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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('snap_token')->nullable()->after('payment_channel');
            $table->text('snap_redirect_url')->nullable()->after('snap_token');
            $table->string('va_number')->nullable()->after('snap_redirect_url');
            $table->string('payment_bank')->nullable()->after('va_number');
            $table->text('qr_code_url')->nullable()->after('payment_bank');
            $table->json('payment_gateway_payload')->nullable()->after('qr_code_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'snap_token',
                'snap_redirect_url',
                'va_number',
                'payment_bank',
                'qr_code_url',
                'payment_gateway_payload',
            ]);
        });
    }
};
