<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayTRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private PayTRService $payTR
    ) {}

    public function show(Order $order)
    {
        if ($order->payment_status !== 'pending') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Bu sipariş zaten işleme alınmış.');
        }

        $userIp = request()->ip();
        $result = $this->payTR->getIframeToken($order, $userIp);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        session(['current_order_id' => $order->id]);

        return redirect('https://www.paytr.com/odeme/guvenli/' . $result['token']);
    }

    /**
     * Ödeme sayfasını göster
     */
    public function success(Request $request)
    {
        $order = Order::where('order_number', $request->query('merchant_oid'))->first();

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Sipariş bulunamadı.');
        }

        if ($order->payment_status !== 'paid') {
            return view('payment.pending', ['order' => $order])
                ->with('warning', 'Ödemeniz işleniyor, lütfen bekleyiniz...');
        }

        return view('payment.success', ['order' => $order]);
    }



    /**
     * Ödeme başarısız — PayTR yönlendirmesi
     */
    public function fail(Request $request)
    {
        $order = Order::where('order_number', $request->query('merchant_oid'))->first();

        return view('payment.fail', ['order' => $order]);
    }

    /**
     * PayTR Webhook (bildirim URL)
     */
    public function webhook(Request $request)
    {
        $data = $request->all();

        Log::info('PayTR Webhook Geldi', $data);

        // Hash doğrulama
        if (!$this->payTR->verifyWebhook($data)) {
            Log::warning('PayTR Geçersiz Hash', $data);
            return response('FAILED', 400);
        }
        Log::info('Webhook çalışıyor!');

        $order = Order::where('order_number', $data['merchant_oid'])->first();

        if (!$order) {
            Log::error('PayTR: Sipariş bulunamadı', ['merchant_oid' => $data['merchant_oid']]);
            return response('FAILED', 404);
        }

        if ($data['status'] === 'success') {
            Log::info('Webhook çalışıyor - Ödeme başarılı!');

            $order->update([
                'payment_status' => 'paid',
                'status'         => 'confirmed',
            ]);

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->decrement('stock', $item->quantity);
                } else {
                    $item->product?->decrement('stock', $item->quantity);
                }
            }


            Log::info('PayTR Ödeme Başarılı', ['order' => $order->order_number]);
        } else {
            $order->update([
                'payment_status' => 'failed',
                'status'         => 'cancelled',
            ]);

            Log::warning('PayTR Ödeme Başarısız', ['order' => $order->order_number]);
        }

        return response('OK', 200);
    }

    public function installments(Request $request)
    {
        $request->validate([
            'bin_number' => 'required|digits:6',
            'order_id'   => 'required|exists:orders,id',
        ]);

        $order  = Order::findOrFail($request->order_id);
        $amount = (int) round($order->total * 100);

        $installments = $this->payTR->getInstallments(
            $request->bin_number,
            $amount
        );

        if (empty($installments)) {
            return response()->json([
                'success' => false,
                'message' => 'Taksit bilgisi alınamadı.',
            ]);
        }

        return response()->json([
            'success'      => true,
            'installments' => $installments,
            'card_type'    => $installments[0]['card_type'] ?? null,
            'bank_name'    => $installments[0]['bank_name'] ?? null,
        ]);
    }

    public function pending(Order $order)
    {
        return view('payment.pending', ['order' => $order]);
    }
}
