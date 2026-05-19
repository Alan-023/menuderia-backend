<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTable extends Model
{
    protected $primaryKey = 'link_id';
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'table_id'
    ];

    public function session()
    {
        return $this->belongsTo(DiningSession::class, 'session_id', 'session_id');
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id', 'table_id');
    }
}