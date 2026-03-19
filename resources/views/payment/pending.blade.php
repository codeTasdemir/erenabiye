<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Ödeme Başarılı — Eren Abiye</title>
    <meta http-equiv="refresh" content="5;url=/">
</head>

<body style="font-family:sans-serif; text-align:center; padding:80px 20px; background:#f0fdf4;">
    <div
        style="max-width:500px; margin:0 auto; background:white; padding:40px; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.1);">

        <div class="text-center py-10">
            <h2 class="text-xl font-semibold text-yellow-600">Ödemeniz Kontrol Ediliyor</h2>
            <p class="mt-2 text-gray-600">Lütfen bekleyiniz, sayfayı yeniliyoruz...</p>

            @if ($order)
                <p class="mt-1 text-sm text-gray-500">Sipariş No: {{ $order->order_number }}</p>
            @endif
        </div>

        {{-- 3 saniyede bir sayfayı yenile webhook işlenince paid  --}}
        <meta http-equiv="refresh" content="3">
    </div>
</body>

</html>
