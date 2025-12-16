<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierAvailability extends Model
{
    protected $table = 'courier_availability'; // tabel sesuai migration
    protected $fillable = ['province_id', 'city_id', 'courier', 'available'];
}
