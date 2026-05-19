<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;

class OrderDetailController extends Controller
{
    public function index() {
        return OrderDetail::all();
    }

    public function store(Request $request) {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
            'unit_price_snapshot' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:200'
        ]);

        $detalle = new OrderDetail();
        $detalle->order_id = $request->order_id;
        $detalle->product_id = $request->product_id;
        $detalle->quantity = $request->quantity;
        $detalle->unit_price_snapshot = $request->unit_price_snapshot;
        $detalle->notes = $request->notes;
        $detalle->save();
        
        return $detalle;
    }

    public function show($id) {
        return OrderDetail::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $detalle = OrderDetail::findOrFail($id);

        $request->validate([
            'order_id' => 'required|integer|exists:orders,order_id',
            'product_id' => 'required|integer|exists:products,product_id',
            'quantity' => 'required|integer|min:1',
            'unit_price_snapshot' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:200'
        ]);

        $detalle->order_id = $request->order_id;
        $detalle->product_id = $request->product_id;
        $detalle->quantity = $request->quantity;
        $detalle->unit_price_snapshot = $request->unit_price_snapshot;
        $detalle->notes = $request->notes;
        $detalle->save();
        
        return $detalle;
    }

    public function destroy($id) {
        $detalle = OrderDetail::findOrFail($id);
        $detalle->delete();
        return ["mensaje" => "Detalle de orden eliminado con exito", "id_borrado" => $id];
    }
}