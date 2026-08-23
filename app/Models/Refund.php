<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'order_id',
        'cash_drawer_id',
        'amount',
        'payment_method',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function cashDrawer()
    {
        return $this->belongsTo(CashDrawer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
