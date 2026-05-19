<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $primaryKey = 'table_id';
    public $timestamps = false;

    protected $fillable = [
        'table_number',
        'qr_code_uuid',
        'capacity'
    ];
}
