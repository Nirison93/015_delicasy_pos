<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'cash_drawer_id',
        'user_id',
        'reason',
        'description',
        'amount',
        'date',
        'user_role',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function cashDrawer()
    {
        return $this->belongsTo(CashDrawer::class);
    }
}
