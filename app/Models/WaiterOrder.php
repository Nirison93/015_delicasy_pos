<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaiterOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_id',
        'waiter_id',
        'products',
        'status',
        'order_id',
        'cancellation_reason',
    ];

    protected $casts = [
        'products' => 'json',
    ];

    public function waiter()
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    public function table()
    {
        return $this->belongsTo(Shift::class, 'table_id');
    }
}
