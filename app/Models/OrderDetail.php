<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;

    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'unit_price_snapshot', 
        'notes'
    ];

    // puente regreso a la orden
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // hacia el producto
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id'); 
    }
}