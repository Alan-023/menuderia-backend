<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RestaurantTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiningSessionController;
use App\Http\Controllers\SessionTableController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::post('/webhooks/mercadopago', [App\Http\Controllers\OrderController::class, 'webhookMercadoPago']);

Route::middleware('auth:sanctum')->group(function () {

    // llamamos al bote de basura
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas productos
    Route::get('/products', [App\Http\Controllers\ProductController::class, 'index']);
    Route::post('/products', [App\Http\Controllers\ProductController::class,'store']);
    Route::get('/products/{id}', [App\Http\Controllers\ProductController::class,'show']);
    Route::put('/products/{id}', [App\Http\Controllers\ProductController::class,'update']);
    Route::delete('/products/{id}', [App\Http\Controllers\ProductController::class, 'destroy']);

    // Rutas categories
    Route::get('/categories', [App\Http\Controllers\CategoryController::class, 'index']);
    Route::post('/categories', [App\Http\Controllers\CategoryController::class, 'store']);
    Route::get('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'show']);
    Route::put('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [App\Http\Controllers\CategoryController::class, 'destroy']);

    // Rutas roles
    Route::get('/roles', [App\Http\Controllers\RoleController::class, 'index']);
    Route::post('/roles', [App\Http\Controllers\RoleController::class, 'store']);
    Route::get('/roles/{id}', [App\Http\Controllers\RoleController::class, 'show']);
    Route::put('/roles/{id}', [App\Http\Controllers\RoleController::class, 'update']);
    Route::delete('/roles/{id}', [App\Http\Controllers\RoleController::class, 'destroy']);

    // Rutas mesas
    Route::get('/tables', [App\Http\Controllers\RestaurantTableController::class, 'index']);
    Route::post('/tables', [App\Http\Controllers\RestaurantTableController::class, 'store']);
    Route::get('/tables/{id}', [App\Http\Controllers\RestaurantTableController::class, 'show']);
    Route::put('/tables/{id}', [App\Http\Controllers\RestaurantTableController::class, 'update']);
    Route::delete('/tables/{id}', [App\Http\Controllers\RestaurantTableController::class, 'destroy']);

    // Rutas user (las del mesero)
    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::get('/users/{id}', [App\Http\Controllers\UserController::class, 'show']);
    Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{id}', [App\Http\Controllers\UserController::class, 'destroy']);

    // Rutas sesion dinning
    Route::get('/sessions', [App\Http\Controllers\DiningSessionController::class, 'index']);
    Route::post('/sessions', [App\Http\Controllers\DiningSessionController::class, 'store']);
    Route::get('/sessions/{id}', [App\Http\Controllers\DiningSessionController::class, 'show']);
    Route::put('/sessions/{id}', [App\Http\Controllers\DiningSessionController::class, 'update']);
    Route::delete('/sessions/{id}', [App\Http\Controllers\DiningSessionController::class, 'destroy']);

    // Ruta enlace entre sesion y mesa
    Route::get('/session-tables', [App\Http\Controllers\SessionTableController::class, 'index']);
    Route::post('/session-tables', [App\Http\Controllers\SessionTableController::class, 'store']);
    Route::get('/session-tables/{id}', [App\Http\Controllers\SessionTableController::class, 'show']);
    Route::put('/session-tables/{id}', [App\Http\Controllers\SessionTableController::class, 'update']);
    Route::delete('/session-tables/{id}', [App\Http\Controllers\SessionTableController::class, 'destroy']);

    // Rutas ordenes (CRUD base)
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index']);
    Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store']);
    Route::get('/orders/{id}', [App\Http\Controllers\OrderController::class, 'show']);
    Route::put('/orders/{id}', [App\Http\Controllers\OrderController::class, 'update']);
    Route::delete('/orders/{id}', [App\Http\Controllers\OrderController::class, 'destroy']);

    // Rutas detalles orden (CRUD base)
    Route::get('/order-details', [App\Http\Controllers\OrderDetailController::class, 'index']);
    Route::post('/order-details', [App\Http\Controllers\OrderDetailController::class, 'store']);
    Route::get('/order-details/{id}', [App\Http\Controllers\OrderDetailController::class, 'show']);
    Route::put('/order-details/{id}', [App\Http\Controllers\OrderDetailController::class, 'update']);
    Route::delete('/order-details/{id}', [App\Http\Controllers\OrderDetailController::class, 'destroy']);


    // ZONA MESERO
    Route::prefix('mesero')->middleware('role:mesero')->group(function () {
        // Pedidos
        Route::post('/pedidos', [OrderController::class, 'crearPedidoMesero']);
        Route::get('/pedidos', [OrderController::class, 'historialMesero']);
        Route::get('/pedidos/detalle/{order_id}', [OrderController::class, 'detallePedidoMesero']);
        Route::put('/pedidos/{order_id}/cancelar', [OrderController::class, 'cancelarPedidoMesero']);
        Route::post('/pedidos/{order_id}/pagar', [OrderController::class, 'generarPago']);

        // Sesiones del mesero
        Route::get('/mis-sesiones', [DiningSessionController::class, 'misSesiones']);
        Route::post('/sesiones', [DiningSessionController::class, 'crearSesionMesero']);
        Route::post('/sesiones/{session_id}/mesas', [SessionTableController::class, 'vincularMesaMesero']);
        Route::get('/sesiones/{session_id}/detalle', [DiningSessionController::class, 'detalleSesionMesero']);
        Route::put('/sesiones/{session_id}/cerrar', [DiningSessionController::class, 'cerrarSesionMesero']);
        Route::post('/sesiones/{session_id}/pagar', [OrderController::class, 'generarPagoSesion']);
    });

    // ZONA ADMIN
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        //pedidos de todos los meseros
        Route::get('/pedidos', [OrderController::class, 'index']);
        Route::get('/pedidos/{id}', [OrderController::class, 'show']); 
        Route::get('/meseros/{mesero_id}/pedidos', [OrderController::class, 'historialMesero']); 
        Route::put('/pedidos/{id}/cancelar', [OrderController::class, 'cancelarPedidoAdmin']);
    });

});