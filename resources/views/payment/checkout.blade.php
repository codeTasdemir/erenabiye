<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme — Eren Abiye</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: sans-serif; }
        .container { max-width: 960px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        @media(max-width: 768px) { .container { grid-template-columns: 1fr; } }

        .card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        h1 { font-size: 22px; color: #1f2937; margin-bottom: 16px; }
        h2 { font-size: 15px; color: #6b7280; margin-bottom: 12px; text-transform: uppercase; letter-spacing: .5px; }

        .order-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .order-total { font-weight: bold; font-size: 18px; color: #1f2937; border-bottom: none; margin-top: 4px; }

        /* Taksit sorgulama */
        .bin-input-group { display: flex; gap: 8px; margin-bottom: 16px; }
        .bin-input-group input {
            flex: 1; padding: 10px 14px; border: 1px solid #d1d5db;
            border-radius: 8px; font-size: 15px; letter-spacing: 2px;
        }
        .bin-input-group button {
            padding: 10px 16px; background: #6366f1; color: white;
            border: none; border-radius: 8px; cursor: pointer; font-size: 14px;
        }
        .bin-input-group button:hover { background: #4f46e5; }

        .installment-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .installment-table th { background: #f9fafb; padding: 8px 12px; text-align: left; color: #6b7280; }
        .installment-table td { padding: 8px 12px; border-top: 1px solid #f3f4f6; }
        .installment-table tr:hover td { background: #f9fafb; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge-credit { background: #dbeafe; color: #1d4ed8; }
        .badge-debit  { background: #dcfce7; color: #15803d; }

        .bank-info { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; padding: 10px; background: #f9fafb; border-radius: 8px; }
        .bank-info span { font-size: 13px; color: #374151; }

        #installment-result { margin-top: 8px; }
        .loading { text-align: center; color: #6b7280; padding: 20px; font-size: 14px; }
        .error-msg { color: #dc2626; font-size: 13px; padding: 8px; background: #fef2f2; border-radius: 6px; }

        iframe { width: 100%; border: none; border-radius: 12px; }
        .full-width { grid-column: 1 / -1; }
    </style>
</head>
<body>
<div class="container">

    {{-- Sol: Sipariş Özeti --}}
    <div>
        <div class="card" style="margin-bottom: 20px;">
            <h2>Sipariş Özeti</h2>
            <p style="font-size:13px; color:#9ca3af; margin-bottom:12px;">
                Sipariş No: <strong style="color:#374151">{{ $order->order_number }}</strong>
            </p>

            @foreach($order->items as $item)
                <div class="order-row">
                    <span>
                        {{ $item->product_name }}
                        @if($item->variant_info)
                            <small style="color:#9ca3af"> ({{ $item->variant_info }})</small>
                        @endif
                        <small style="color:#6b7280"> x{{ $item->quantity }}</small>
                    </span>
                    <span>₺{{ number_format($item->total_price, 2, ',', '.') }}</span>
                </div>
            @endforeach

            @if($order->discount_amount > 0)
                <div class="order-row" style="color:#16a34a">
                    <span>İndirim</span>
                    <span>-₺{{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                </div>
            @endif

            <div class="order-row" style="color:#6b7280">
                <span>Kargo</span>
                <span>{{ $order->shipping_amount > 0 ? '₺'.number_format($order->shipping_amount, 2, ',', '.') : 'Ücretsiz' }}</span>
            </div>

            <div class="order-row order-total">
                <span>Toplam</span>
                <span>₺{{ number_format($order->total, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Taksit Sorgulama --}}
        <div class="card">
            <h2>🏦 Taksit Seçenekleri</h2>
            <p style="font-size:13px; color:#6b7280; margin-bottom:12px;">
                Kartınızın ilk 6 hanesini girerek taksit seçeneklerini öğrenin.
            </p>

            <div class="bin-input-group">
                <input
                    type="text"
                    id="bin-input"
                    maxlength="6"
                    placeholder="123456"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                />
                <button onclick="queryInstallments()">Sorgula</button>
            </div>

            <div id="installment-result"></div>
        </div>
    </div>

    {{-- Sağ: PayTR iFrame --}}
    <div class="card">
        <h2>💳 Güvenli Ödeme</h2>
        <p style="font-size:12px; color:#9ca3af; margin-bottom:16px;">
            🔒 256-bit SSL şifreleme ile korunmaktadır.
        </p>
        <iframe
            src="https://www.paytr.com/odeme/guvenli/{{ $iframeToken }}"
            id="paytriframe"
            scrolling="no">
        </iframe>
    </div>

</div>

<script>
    iFrameResize({}, '#paytriframe');

    async function queryInstallments() {
        const bin = document.getElementById('bin-input').value.trim();
        const resultDiv = document.getElementById('installment-result');

        if (bin.length !== 6) {
            resultDiv.innerHTML = '<p class="error-msg">Lütfen kartınızın ilk 6 hanesini girin.</p>';
            return;
        }

        resultDiv.innerHTML = '<p class="loading">⏳ Taksit seçenekleri yükleniyor...</p>';

        try {
            const response = await fetch('{{ route("payment.installments") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    bin_number: bin,
                    order_id: {{ $order->id }},
                }),
            });

            const data = await response.json();

            if (!data.success || !data.installments.length) {
                resultDiv.innerHTML = '<p class="error-msg">Bu kart için taksit bilgisi bulunamadı.</p>';
                return;
            }

            let badgeClass = data.card_type === 'credit' ? 'badge-credit' : 'badge-debit';
            let cardTypeLabel = data.card_type === 'credit' ? 'Kredi Kartı' : 'Banka Kartı';

            let html = `
                <div class="bank-info">
                    <span>🏦 <strong>${data.bank_name ?? 'Banka'}</strong></span>
                    <span class="badge ${badgeClass}">${cardTypeLabel}</span>
                </div>
                <table class="installment-table">
                    <thead>
                        <tr>
                            <th>Taksit</th>
                            <th>Aylık Tutar</th>
                            <th>Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            data.installments.forEach(inst => {
                const monthly = (inst.total_amount / inst.installment_count / 100).toFixed(2)
                    .replace('.', ',');
                const total = (inst.total_amount / 100).toFixed(2)
                    .replace('.', ',');

                const label = inst.installment_count === 1
                    ? 'Peşin'
                    : inst.installment_count + ' Taksit';

                html += `
                    <tr>
                        <td><strong>${label}</strong></td>
                        <td>₺${monthly}</td>
                        <td>₺${total}</td>
                    </tr>
                `;
            });

            html += '</tbody></table>';
            resultDiv.innerHTML = html;

        } catch (e) {
            resultDiv.innerHTML = '<p class="error-msg">Bir hata oluştu. Lütfen tekrar deneyin.</p>';
        }
    }

    document.getElementById('bin-input').addEventListener('input', function() {
        if (this.value.length === 6) queryInstallments();
    });
</script>
</body>
</html>