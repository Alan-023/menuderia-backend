<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; 

class ProductController extends Controller
{
    public function index() {
        return Product::all();
    }

    public function store(Request $request) {
        // EL CADENERO
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'is_available' => 'nullable|boolean'
        ]);

        $productoNuevo = new Product();
        $productoNuevo->name = $request->name;
        $productoNuevo->description = $request->description;
        $productoNuevo->price = $request->price;
        $productoNuevo->category_id = $request->category_id;
        
        $productoNuevo->is_available = $request->has('is_available') ? $request->is_available : 1; 
        
        $productoNuevo->save();
        return $productoNuevo;
    }

    public function show($id) {
        // EL SEGURO
        return Product::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $productoAEditar = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'is_available' => 'nullable|boolean'
        ]);

        $productoAEditar->name = $request->name;
        $productoAEditar->description = $request->description;
        $productoAEditar->price = $request->price;
        $productoAEditar->category_id = $request->category_id;
        $productoAEditar->is_available = $request->has('is_available') ? $request->is_available : $productoAEditar->is_available;
        
        $productoAEditar->save();
        return $productoAEditar;
    }

    public function destroy($id) {
        $productoAEliminar = Product::findOrFail($id);
        $productoAEliminar->delete();
        return ["mensaje" => "Platillo eliminado con exito", "id_borrado" => $id];
    }
}
