<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDrawer extends Model
{
    use HasFactory;

    protected $fillable = [
        'opened_by',
        'closed_by',
        'opening_balance',
        'closing_balance',
        'opened_at',
        'closed_at',
        'status',
        'notes',
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
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'qr_sales' => 'decimal:2',
        'bank_transfer_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'cash_drops' => 'decimal:2',
        'cash_expenses' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'cash_refunds' => 'decimal:2',
        'total_refunds' => 'decimal:2',
        'variance' => 'decimal:2',
        'denomination_breakdown' => 'array',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who opened the cash drawer.
     */
    public function openedByUser()
    {
        return $this->belongsTo(User::class, 'opened_by', 'id');
    }

    /**
     * Get the user who closed the cash drawer.
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by', 'id');
    }

    /**
     * Get the manager/admin who approved a variance on this drawer.
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    /**
     * Get expenses for this cash drawer.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get manual cash in/out/drop movements for this cash drawer.
     */
    public function cashMovements()
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Get refunds attributed to this cash drawer.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get sales rung up during this cash drawer session.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope to get open drawers.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope to get closed drawers.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope to get drawers whose variance still needs manager/admin approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)->whereNull('approved_at');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('opened_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('opened_by', $userId)->orWhere('closed_by', $userId);
    }

    /**
     * Whether the given user is allowed to close/manage this drawer:
     * the cashier who opened it, or any Admin/Manager.
     */
    public function canBeManagedBy(User $user): bool
    {
        return in_array($user->role_type, ['Admin', 'Manager']) || $this->opened_by === $user->id;
    }
}
