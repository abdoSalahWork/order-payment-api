<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Order::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->with('user')->paginate(10);

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $totalAmount = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $totalAmount,
            'items' => $request->items,
            'status' => 'pending',
        ]);

        return response()->json($order, 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load('user', 'payments'));
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        if ($request->has('items')) {
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });
            $order->total_amount = $totalAmount;
            $order->items = $request->items;
        }

        if ($request->has('status')) {
            $order->status = $request->status;
        }

        $order->save();

        return response()->json($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        if (!$order->canBeDeleted()) {
            return response()->json([
                'message' => 'Cannot delete order with associated payments'
            ], 422);
        }

        $order->delete();

        return response()->json(null, 204);
    }
}
