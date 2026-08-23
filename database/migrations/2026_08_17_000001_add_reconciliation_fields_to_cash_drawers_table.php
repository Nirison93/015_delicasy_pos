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
        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->decimal('expected_cash', 10, 2)->nullable()->after('closing_balance');
            $table->decimal('cash_sales', 10, 2)->default(0)->after('expected_cash');
            $table->decimal('card_sales', 10, 2)->default(0)->after('cash_sales');
            $table->decimal('qr_sales', 10, 2)->default(0)->after('card_sales');
            $table->decimal('bank_transfer_sales', 10, 2)->default(0)->after('qr_sales');
            $table->decimal('other_sales', 10, 2)->default(0)->after('bank_transfer_sales');
            $table->decimal('cash_in', 10, 2)->default(0)->after('other_sales');
            $table->decimal('cash_out', 10, 2)->default(0)->after('cash_in');
            $table->decimal('cash_drops', 10, 2)->default(0)->after('cash_out');
            $table->decimal('cash_expenses', 10, 2)->default(0)->after('cash_drops');
            $table->decimal('total_expenses', 10, 2)->default(0)->after('cash_expenses');
            $table->decimal('cash_refunds', 10, 2)->default(0)->after('total_expenses');
            $table->decimal('total_refunds', 10, 2)->default(0)->after('cash_refunds');
            $table->decimal('variance', 10, 2)->nullable()->after('total_refunds');
            $table->enum('variance_status', ['balanced', 'over', 'short'])->nullable()->after('variance');
            $table->json('denomination_breakdown')->nullable()->after('variance_status');
            $table->text('closing_notes')->nullable()->after('denomination_breakdown');
            $table->boolean('requires_approval')->default(false)->after('closing_notes');
            $table->unsignedBigInteger('approved_by')->nullable()->after('requires_approval');
            $table->dateTime('approved_at')->nullable()->after('approved_by');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'expected_cash',
                'cash_sales',
                'card_sales',
                'qr_sales',
                'bank_transfer_sales',
                'other_sales',
                'cash_in',
                'cash_out',
                'cash_drops',
                'cash_expenses',
                'total_expenses',
                'cash_refunds',
                'total_refunds',
                'variance',
                'variance_status',
                'denomination_breakdown',
                'closing_notes',
                'requires_approval',
                'approved_by',
                'approved_at',
            ]);
        });
    }
};
