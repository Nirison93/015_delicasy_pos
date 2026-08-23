<?php

namespace App\Services;

use App\Models\CashDrawer;

class CashDrawerReconciliationService
{
    /**
     * Normalize any payment_method string (from sales, expenses, or refunds)
     * into one of a fixed set of report buckets.
     */
    public function bucketPaymentMethod(?string $method): string
    {
        $m = strtolower(trim((string) $method));

        return match (true) {
            $m === 'cash' => 'cash',
            $m === 'card' => 'card',
            in_array($m, ['qr', 'qr code', 'qrcode']) => 'qr',
            in_array($m, ['bank transfer', 'bank_transfer', 'online']) => 'bank_transfer',
            default => 'other',
        };
    }

    /**
     * The single source of truth for cash drawer reconciliation math.
     * Always recomputed fresh from sales/expenses/refunds/cash_movements
     * scoped to this drawer — never trusts client-submitted totals.
     *
     * Expected Cash = Opening Balance + Cash Sales + Cash In
     *                 - Cash Refunds - Cash Expenses - Cash Drops - Cash Out
     */
    public function computeSnapshot(CashDrawer $cashDrawer): array
    {
        $salesByBucket = [
            'cash' => 0.0,
            'card' => 0.0,
            'qr' => 0.0,
            'bank_transfer' => 0.0,
            'other' => 0.0,
        ];
        $salesCountByBucket = [
            'cash' => 0,
            'card' => 0,
            'qr' => 0,
            'bank_transfer' => 0,
            'other' => 0,
        ];

        $cashDrawer->sales()->select('payment_method', 'total_amount')->get()
            ->each(function ($sale) use (&$salesByBucket, &$salesCountByBucket) {
                $bucket = $this->bucketPaymentMethod($sale->payment_method);
                $salesByBucket[$bucket] += (float) $sale->total_amount;
                $salesCountByBucket[$bucket]++;
            });

        $expenses = $cashDrawer->expenses()->select('payment_method', 'amount')->get();
        $totalExpenses = (float) $expenses->sum('amount');
        $cashExpenses = (float) $expenses->filter(
            fn($e) => $this->bucketPaymentMethod($e->payment_method) === 'cash'
        )->sum('amount');

        $refunds = $cashDrawer->refunds()->select('payment_method', 'amount')->get();
        $totalRefunds = (float) $refunds->sum('amount');
        $cashRefunds = (float) $refunds->filter(
            fn($r) => $this->bucketPaymentMethod($r->payment_method) === 'cash'
        )->sum('amount');

        $movements = $cashDrawer->cashMovements()->select('type', 'amount')->get();
        $cashIn = (float) $movements->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $movements->where('type', 'cash_out')->sum('amount');
        $cashDrops = (float) $movements->where('type', 'cash_drop')->sum('amount');

        $openingBalance = (float) $cashDrawer->opening_balance;
        $cashSales = (float) $salesByBucket['cash'];

        $expectedCash = round(
            $openingBalance + $cashSales + $cashIn - $cashRefunds - $cashExpenses - $cashDrops - $cashOut,
            2
        );

        return [
            'opening_balance' => round($openingBalance, 2),
            'cash_sales' => round($cashSales, 2),
            'card_sales' => round($salesByBucket['card'], 2),
            'qr_sales' => round($salesByBucket['qr'], 2),
            'bank_transfer_sales' => round($salesByBucket['bank_transfer'], 2),
            'other_sales' => round($salesByBucket['other'], 2),
            'sales_count' => $salesCountByBucket,
            'cash_in' => round($cashIn, 2),
            'cash_out' => round($cashOut, 2),
            'cash_drops' => round($cashDrops, 2),
            'cash_expenses' => round($cashExpenses, 2),
            'total_expenses' => round($totalExpenses, 2),
            'cash_refunds' => round($cashRefunds, 2),
            'total_refunds' => round($totalRefunds, 2),
            'expected_cash' => $expectedCash,
        ];
    }
}
