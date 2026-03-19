<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PayTRService
{
    private string $merchantId;
    private string $merchantKey;
    private string $merchantSalt;
    private bool   $testMode;
    private string $iframeUrl;

    public function __construct()
    {
        $this->merchantId   = config('paytr.merchant_id');
        $this->merchantKey  = config('paytr.merchant_key');
        $this->merchantSalt = config('paytr.merchant_salt');
        $this->testMode     = config('paytr.test_mode');
        $this->iframeUrl    = config('paytr.iframe_url');
    }

    /**
     * iFrame token oluştur
     */
    public function getIframeToken(Order $order, string $userIp): array
    {
        $basketItems = [];
        foreach ($order->items as $item) {
            $basketItems[] = [
                $item->product_name,
                number_format($item->unit_price, 2, '.', ''),
                $item->quantity,
            ];
        }
        $basketEncoded = base64_encode(json_encode($basketItems));

        $userEmail     = $order->user?->email ?? 'musteri@example.com';
        $userPhone     = preg_replace('/[^0-9]/', '', $order->shipping_phone);
        $userName      = $order->shipping_name;
        $merchantOid   = $order->order_number;
        $paymentAmount = (int) round($order->total * 100); // Kuruş cinsinden

        $currency = match ($order->currency) {
            'USD'   => 'USD',
            'EUR'   => 'EUR',
            default => 'TL',
        };

        $hashStr = $this->merchantId .
            $userIp .
            $merchantOid .
            $userEmail .
            $paymentAmount .
            $basketEncoded .
            config('paytr.no_installment') .
            config('paytr.max_installment') .
            $currency .
            ($this->testMode ? '1' : '0');

        $paytrToken = base64_encode(
            hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true)
        );

        $postData = [
            'merchant_id'      => $this->merchantId,
            'user_ip'          => $userIp,
            'merchant_oid'     => $merchantOid,
            'email'            => $userEmail,
            'payment_amount'   => $paymentAmount,
            'paytr_token'      => $paytrToken,
            'user_basket'      => $basketEncoded,
            'debug_on'         => config('paytr.debug_on') ? '1' : '0',
            'no_installment'   => config('paytr.no_installment'),
            'max_installment'  => config('paytr.max_installment'),
            'user_name'        => $userName,
            'user_address'     => $order->shipping_address,
            'user_phone'       => $userPhone,
            'merchant_ok_url'  => route('payment.success') . '?merchant_oid=' . $merchantOid,
            'merchant_fail_url' => route('payment.fail') . '?merchant_oid=' . $merchantOid,
            'timeout_limit'    => '30',
            'currency'         => $currency,
            'test_mode'        => $this->testMode ? '1' : '0',
            'lang'             => config('paytr.lang'),
        ];

        $result = $this->sendRequest($postData);

        if ($result['status'] === 'success') {
            $order->update(['paytr_merchant_oid' => $merchantOid]);

            return [
                'success' => true,
                'token'   => $result['token'],
            ];
        }

        Log::error('PayTR Token Hatası', [
            'order'  => $order->order_number,
            'reason' => $result['reason'] ?? 'Bilinmeyen hata',
        ]);

        return [
            'success' => false,
            'message' => $result['reason'] ?? 'Ödeme başlatılamadı.',
        ];
    }

    /**
     * Webhook doğrulama
     */
    public function verifyWebhook(array $data): bool
    {
        $hash = base64_encode(
            hash_hmac(
                'sha256',
                $data['merchant_oid'] .
                    $this->merchantSalt .
                    $data['status'] .
                    $data['total_amount'],
                $this->merchantKey,
                true
            )
        );

        return $hash === $data['hash'];
    }

    /**
     * cURL ile PayTR API'ye istek gönder
     */
    private function sendRequest(array $postData): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->iframeUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('PayTR cURL Hatası: ' . curl_error($ch));
            return ['status' => 'failed', 'reason' => curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($result, true) ?? ['status' => 'failed', 'reason' => 'Geçersiz yanıt'];
    }

    public function getInstallments(string $binNumber, int $price): array
    {
        $binNumber = preg_replace('/[^0-9]/', '', $binNumber);

        $hashStr    = $this->merchantId . $binNumber . $price . $this->merchantSalt;
        $token      = base64_encode(hash_hmac('sha256', $hashStr, $this->merchantKey, true));

        $postData = [
            'merchant_id'  => $this->merchantId,
            'bin_number'   => $binNumber,
            'price'        => $price,
            'currency'     => config('paytr.currency'),
            'paytr_token'  => $token,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.paytr.com/odeme/api/bin-detail');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($result, true);

        if (!$data || $data['status'] !== 'success') {
            return [];
        }

        return $data['installment_details'] ?? [];
    }
}
