<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $timestamps = false; 

    protected $fillable = [
        'session_id', 
        'user_id',
        'total_amount', 
        'status',
        'created_at'
    ];

    // el pd del puente
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function mesero()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id'); 
    }

    public function diningSession()
    {
        return $this->belongsTo(DiningSession::class, 'session_id', 'session_id');
    }
}