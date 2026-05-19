<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index() {
        return Category::all();
    }

    public function store(Request $request) {
        // EL CADENERO
        $request->validate([
            'name' => 'required|string|max:50',
            'display_order' => 'nullable|integer'
        ]);

        $categoriaNueva = new Category();
        $categoriaNueva->name = $request->name;
        $categoriaNueva->display_order = $request->display_order;
        $categoriaNueva->save();
        
        return $categoriaNueva;
    }

    public function show($id) {
        // EL SEGURO
        return Category::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $categoriaAEditar = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:50',
            'display_order' => 'nullable|integer'
        ]);

        $categoriaAEditar->name = $request->name;
        $categoriaAEditar->display_order = $request->display_order;
        $categoriaAEditar->save();
        
        return $categoriaAEditar;
    }

    public function destroy($id) {
        $categoriaAEliminar = Category::findOrFail($id);
        $categoriaAEliminar->delete();
        return ["mensaje" => "Categoria eliminada con exito", "id_borrado" => $id];
    }
}