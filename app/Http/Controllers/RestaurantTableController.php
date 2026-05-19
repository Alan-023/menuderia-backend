<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RestaurantTable;

class RestaurantTableController extends Controller
{
    public function index() {
        $tables = RestaurantTable::all();
        $activeTableIds = \App\Models\SessionTable::whereHas('session', function($q) {
            $q->where('status', 'ACTIVE');
        })->pluck('table_id')->toArray();

        foreach ($tables as $table) {
            $table->status = in_array($table->table_id, $activeTableIds) ? 'occupied' : 'available';
        }
        return $tables;
    }

    public function store(Request $request) {
        // EL CADENERO
        $request->validate([
            'table_number' => 'required|string|max:10|unique:restaurant_tables,table_number',
            'qr_code_uuid' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1'
        ]);

        $mesa = new RestaurantTable();
        $mesa->table_number = $request->table_number;
        $mesa->qr_code_uuid = $request->qr_code_uuid;
        $mesa->capacity = $request->capacity;
        $mesa->save();
        
        return $mesa;
    }

    public function show($id) {
        // EL SEGURO
        return RestaurantTable::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $mesa = RestaurantTable::findOrFail($id);

        $request->validate([
            'table_number' => 'required|string|max:10|unique:restaurant_tables,table_number,' . $id . ',table_id',
            'qr_code_uuid' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1'
        ]);

        $mesa->table_number = $request->table_number;
        $mesa->qr_code_uuid = $request->qr_code_uuid;
        $mesa->capacity = $request->capacity;
        $mesa->save();
        
        return $mesa;
    }

    public function destroy($id) {
        $mesa = RestaurantTable::findOrFail($id);
        $mesa->delete();
        return ["mensaje" => "Mesa eliminada con exito", "id_borrado" => $id];
    }
}