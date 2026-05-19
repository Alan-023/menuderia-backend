<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiningSession extends Model
{
    protected $primaryKey = 'session_id';
    
    public $timestamps = false;

    protected $fillable = [
        'session_token',
        'titular_name',
        'status',
        'opened_by_user_id',
        'closed_at'
    ];

    // Relacion: el mesero que abrió esta sesión
    public function mesero()
    {
        return $this->belongsTo(User::class, 'opened_by_user_id', 'user_id');
    }

    // Relacion: mesas asignadas a esta sesión (pivot session_tables)
    public function sessionTables()
    {
        return $this->hasMany(SessionTable::class, 'session_id', 'session_id');
    }

    // Relacion: órdenes de esta sesión
    public function orders()
    {
        return $this->hasMany(Order::class, 'session_id', 'session_id');
    }
}