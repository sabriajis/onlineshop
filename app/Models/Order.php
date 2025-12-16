<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'province_id',    // provinsi tujuan
        'city_id',        // kota tujuan
        'district_id',    // kecamatan tujuan
        'total_price',
        'shipping_cost',
        'courier',
        'status',
    ];

    /**
     * Order milik user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Detail item dalam order
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * 1 order = 1 transaksi
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * 1 order = 1 pengiriman
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * Relasi ke provinsi
     */
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    /**
     * Relasi ke kota
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    /**
     * Relasi ke kecamatan
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }
}
