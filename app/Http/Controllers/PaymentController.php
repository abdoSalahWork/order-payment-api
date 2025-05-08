<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
        $this->middleware('auth:api');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Payment::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        $payments = $query->with('order')->paginate(10);

        return response()->json($payments);
    }

    public function process(ProcessPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::findOrFail($validated['order_id']);

        try {
            $result = $this->paymentService->processPayment($validated, $order);

            if (!$result['success']) {
                return response()->json([
                    'message' => $result['message'],
                    'error' => $result['error']
                ], 422);
            }

            return response()->json($result, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('order'));
    }
}
