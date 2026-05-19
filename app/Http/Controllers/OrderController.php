<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB; // transacciones db
use App\Models\User; // sacar el nombre del mesero
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    //            admin
    public function index() {
        return response()->json(Order::with(['details', 'diningSession.sessionTables.table'])->get(), 200);
    }

    public function show($id) {
        $orden = Order::with('details.product')->find($id);
        if (!$orden) return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        
        return response()->json($orden, 200);
    }

    public function cancelarPedidoAdmin($id) {
        $orden = Order::find($id);
        if (!$orden) return response()->json(['mensaje' => 'Pedido no encontrado'], 404);

        $orden->status = 'CANCELLED';
        $orden->save();

        return response()->json(['mensaje' => 'Pedido cancelado por el Administrador', 'pedido' => $orden], 200);
    }
        // mesero

    // pedido por Mesero
    public function crearPedidoMesero(Request $request) {
        $mesero = $request->user();
        $mesero_id = $mesero->user_id;
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);

        $request->validate([
            'session_id' => 'required|integer', 
            'productos' => 'required|array|min:1', 
            'productos.*.product_id' => 'required|integer',
            'productos.*.quantity' => 'required|integer|min:1',
            'productos.*.unit_price_snapshot' => 'required|numeric|min:0',
            'productos.*.notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($request->productos as $prod) {
                $total += ($prod['unit_price_snapshot'] * $prod['quantity']);
            }

            $orden = new Order();
            $orden->session_id = $request->session_id;
            $orden->total_amount = $total;
            $orden->status = 'PENDING';
            $orden->user_id = $mesero_id;
            $orden->save(); // guardamos y ponemos order_id

            foreach ($request->productos as $prod) {
                $detalle = new OrderDetail();
                $detalle->order_id = $orden->order_id; 
                $detalle->product_id = $prod['product_id'];
                $detalle->quantity = $prod['quantity'];
                $detalle->unit_price_snapshot = $prod['unit_price_snapshot'];
                $detalle->notes = isset($prod['notes']) ? $prod['notes'] : null;
                $detalle->save();
            }

            DB::commit();
            
            // Retornamos la orden incluyendo quién la atendió
            $ordenGuardada = Order::with('details')->find($orden->order_id);
            return response()->json([
                'mensaje' => 'Pedido creado exitosamente',
                'atendido_por' => $mesero->full_name,
                'pedido' => $ordenGuardada
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['mensaje' => 'Hubo un error al procesar tu pedido', 'error' => $e->getMessage()], 500);
        }
    }

    // 2. Ver historial de pedidos
    public function historialMesero(Request $request, $mesero_id = null) {
        if (!$mesero_id) {
            $mesero = $request->user();
            $mesero_id = $mesero->user_id;
        } else {
            $mesero = User::find($mesero_id);
        }
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);

        $pedidos = Order::with('details')
                        ->where('user_id', $mesero_id)
                        ->get();
        
        if ($pedidos->isEmpty()) {
            return response()->json(['mensaje' => 'No hay pedidos registrados para este mesero'], 404);
        }
        
        return response()->json([
            'historial_solicitado_por' => $mesero->full_name,
            'pedidos' => $pedidos
        ], 200);
        // Validar que el pedido realmente le pertenezca a este mesero
        if ($orden->user_id != $mesero_id) {
            return response()->json(['mensaje' => 'No tienes permiso. Este pedido pertenece a otro mesero.'], 403);
        }
    }

    // 3. Ver detalle pedido de un mesero
    public function detallePedidoMesero(Request $request, $order_id) {
        $mesero = $request->user();
        $mesero_id = $mesero->user_id;
        $orden = Order::with('details.product')->find($order_id);
        
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);
        if (!$orden) return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        
        return response()->json([
            'atendido_por' => $mesero->full_name,
            'detalle' => $orden
        ], 200);
    }

    // 4. Cancelar pedido por mesero
    public function cancelarPedidoMesero(Request $request, $order_id) {
        $mesero = $request->user();
        $mesero_id = $mesero->user_id;
        $orden = Order::find($order_id);
        
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);
        if (!$orden) return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        
        if ($orden->status == 'COOKING' || $orden->status == 'SERVED') {
            return response()->json(['mensaje' => 'No puedes cancelar un pedido que ya está en cocina o servido'], 403);
        }

        $orden->status = 'CANCELLED';
        $orden->save();

        return response()->json([
            'mensaje' => 'Cancelaste este pedido correctamente', 
            'cancelado_por' => $mesero->full_name,
            'pedido' => $orden
        ], 200);
        
        if ($orden->user_id != $mesero_id) {
            return response()->json(['mensaje' => 'No tienes permiso. Este pedido pertenece a otro mesero.'], 403);
        }
    }
    // 5. Generar Pago Mercado Pago
    public function generarPago(Request $request, $order_id) {
        $mesero = $request->user();
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);

        $orden = Order::with('details.product')->find($order_id);
        if (!$orden) return response()->json(['mensaje' => 'Pedido no encontrado'], 404);
        if ($orden->status == 'PAID') return response()->json(['mensaje' => 'El pedido ya está pagado'], 400);

        $items = [];
        foreach ($orden->details as $detalle) {
            $items[] = [
                "title" => $detalle->product ? $detalle->product->name : "Producto",
                "quantity" => (int) $detalle->quantity,
                "unit_price" => (float) $detalle->unit_price_snapshot,
                "currency_id" => "MXN"
            ];
        }

        $preferenceData = [
            "items" => $items,
            "external_reference" => (string) $orden->order_id,
            "back_urls" => [
                "success" => env('FRONTEND_URL', "http://localhost:8000"), 
                "failure" => env('FRONTEND_URL', "http://localhost:8000"),
                "pending" => env('FRONTEND_URL', "http://localhost:8000")
            ],
            "notification_url" => env('APP_URL', "http://localhost:8000") . "/api/webhooks/mercadopago"
        ];

        try {
            $token = env('MERCADOPAGO_ACCESS_TOKEN');
            Log::info('Generando pago MP con token: ' . ($token ? 'Presente' : 'Nulo'));
            Log::info('Payload MP: ' . json_encode($preferenceData));
            
            $response = Http::withToken($token)
                ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

            if ($response->successful()) {
                return response()->json([
                    'init_point' => $response['sandbox_init_point'], // sandbox url
                    'preference_id' => $response['id']
                ]);
            }

            Log::error('MP Error: ' . json_encode($response->json()));
            return response()->json(['mensaje' => 'Error al generar preferencia de pago', 'mp_error' => $response->json()], 500);

        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error de conexión con Mercado Pago', 'error' => $e->getMessage()], 500);
        }
    }

    // 5.5 Generar Pago de Sesión Completa Mercado Pago
    public function generarPagoSesion(Request $request, $session_id) {
        $mesero = $request->user();
        if (!$mesero) return response()->json(['mensaje' => 'Mesero no encontrado'], 404);

        $ordenes = Order::with('details.product')
            ->where('session_id', $session_id)
            ->where('status', '!=', 'PAID')
            ->where('status', '!=', 'CANCELLED')
            ->get();
            
        if ($ordenes->isEmpty()) {
            return response()->json(['mensaje' => 'No hay órdenes pendientes de pago en esta sesión'], 400);
        }

        $items = [];
        foreach ($ordenes as $orden) {
            foreach ($orden->details as $detalle) {
                $items[] = [
                    "title" => $detalle->product ? $detalle->product->name : "Producto",
                    "quantity" => (int) $detalle->quantity,
                    "unit_price" => (float) $detalle->unit_price_snapshot,
                    "currency_id" => "MXN"
                ];
            }
        }

        $preferenceData = [
            "items" => $items,
            "external_reference" => "session-" . $session_id,
            "back_urls" => [
                "success" => env('FRONTEND_URL', "http://localhost:8000"),
                "failure" => env('FRONTEND_URL', "http://localhost:8000"),
                "pending" => env('FRONTEND_URL', "http://localhost:8000")
            ],
            "notification_url" => env('APP_URL', "http://localhost:8000") . "/api/webhooks/mercadopago"
        ];

        try {
            $token = env('MERCADOPAGO_ACCESS_TOKEN');
            $response = Http::withToken($token)
                ->post('https://api.mercadopago.com/checkout/preferences', $preferenceData);

            if ($response->successful()) {
                return response()->json([
                    'init_point' => $response['sandbox_init_point'],
                    'preference_id' => $response['id']
                ]);
            }
            Log::error('MP Session Error: ' . json_encode($response->json()));
            return response()->json(['mensaje' => 'Error al generar preferencia de pago de sesión', 'mp_error' => $response->json()], 500);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error de conexión con Mercado Pago', 'error' => $e->getMessage()], 500);
        }
    }

    // 6. Webhook Mercado Pago
    public function webhookMercadoPago(Request $request) {
        $topic = $request->query('topic') ?? $request->input('type');
        $id = $request->query('id') ?? ($request->input('data') ? $request->input('data.id') : null);

        if ($topic === 'payment' && $id) {
            try {
                $response = Http::withToken(env('MERCADOPAGO_ACCESS_TOKEN'))
                    ->get("https://api.mercadopago.com/v1/payments/{$id}");

                if ($response->successful()) {
                    $payment = $response->json();
                    
                    if (isset($payment['status']) && $payment['status'] === 'approved') {
                        $ref = $payment['external_reference'];
                        
                        if (str_starts_with($ref, 'session-')) {
                            // Pago de sesión completa
                            $session_id = str_replace('session-', '', $ref);
                            $ordenes = Order::where('session_id', $session_id)
                                ->where('status', '!=', 'PAID')
                                ->where('status', '!=', 'CANCELLED')
                                ->get();
                                
                            foreach ($ordenes as $orden) {
                                $orden->status = 'PAID';
                                $orden->transaction_id = $payment['id'];
                                $orden->payment_status = $payment['status'];
                                $orden->payment_date = date('Y-m-d H:i:s', strtotime($payment['date_approved']));
                                $orden->save();
                            }
                            Log::info("Sesión {$session_id} pagada vía Mercado Pago. Transacción: {$payment['id']}");
                        } else {
                            // Pago de orden individual
                            $order_id = $ref;
                            $orden = Order::find($order_id);

                            if ($orden && $orden->status !== 'PAID') {
                                $orden->status = 'PAID';
                                $orden->transaction_id = $payment['id'];
                                $orden->payment_status = $payment['status'];
                                $orden->payment_date = date('Y-m-d H:i:s', strtotime($payment['date_approved']));
                                $orden->save();
                                Log::info("Orden {$order_id} pagada vía Mercado Pago. Transacción: {$payment['id']}");
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error procesando webhook MP: " . $e->getMessage());
            }
        }
        return response()->json(['status' => 'ok']);
    }

            //CRUD BASE
    public function store(Request $request) {
        $orden = new Order();
        $orden->session_id = $request->session_id;
        $orden->total_amount = $request->has('total_amount') ? $request->total_amount : 0.00;
        $orden->status = $request->has('status') ? $request->status : 'PENDING';
        $orden->save();
        return $orden;
    }

    public function update(Request $request, $id) {
        $orden = Order::findOrFail($id);
        $orden->session_id = $request->session_id;
        if ($request->has('total_amount')) { $orden->total_amount = $request->total_amount; }
        if ($request->has('status')) { $orden->status = $request->status; }
        $orden->save();
        return $orden;
    }

    public function destroy($id) {
        $orden = Order::findOrFail($id);
        $orden->delete();
        return ["mensaje" => "Orden eliminada con exito", "id_borrado" => $id];
    }
}