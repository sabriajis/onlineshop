<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'payment_type',
        'transaction_status',
        'gross_amount',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
