<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Virtual Account - {{ $order->order_number }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            background: linear-gradient(180deg, #EAF2FF 0%, #F3F6FC 100%);
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .container {
            width: 100%;
            max-width: 440px;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid #DBEAFE;
            border-radius: 22px;
            padding: 26px 24px;
            box-shadow: 0 20px 45px rgba(27, 100, 242, 0.10), 0 4px 14px rgba(15, 23, 42, 0.04);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #1B64F2, #5B9DFA);
        }
        .badge-gateway {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            color: #1B64F2;
            margin-bottom: 14px;
            letter-spacing: 0.4px;
        }
        .badge-dot {
            width: 7px;
            height: 7px;
            background: #1B64F2;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(27, 100, 242, 0.55);
        }
        .header-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 4px;
        }
        .header-subtitle {
            font-size: 12.5px;
            color: #6B7280;
            margin-bottom: 20px;
        }
        .bill-box {
            background: #F8FAFC;
            border: 1px solid #E5EAF2;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 18px;
        }
        .bill-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            color: #6B7280;
            margin-bottom: 9px;
        }
        .bill-row:last-child {
            margin-bottom: 0;
            padding-top: 11px;
            border-top: 1px dashed #CBD5E1;
        }
        .bill-value {
            font-weight: 700;
            color: #111827;
        }
        .total-amount {
            font-size: 20px;
            font-weight: 900;
            color: #1B64F2;
        }
        .field-group {
            margin-bottom: 18px;
        }
        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
        }
        .va-input {
            width: 100%;
            background: #EFF6FF;
            border: 1.5px solid #93C5FD;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #1B64F2;
            font-family: monospace;
            outline: none;
            transition: all 0.2s;
        }
        .va-input:focus {
            background: #FFFFFF;
            border-color: #1B64F2;
            box-shadow: 0 0 0 4px rgba(27, 100, 242, 0.14);
        }
        .btn-pay {
            width: 100%;
            background: linear-gradient(135deg, #1B64F2 0%, #3B82F6 100%);
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 6px 18px rgba(27, 100, 242, 0.28);
        }
        .btn-pay:hover {
            background: linear-gradient(135deg, #1554D1 0%, #2563EB 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(27, 100, 242, 0.36);
        }
        .btn-pay:active {
            transform: translateY(1px);
        }
        .btn-pay:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }
        .success-box {
            text-align: center;
            padding: 14px 10px;
        }
        .success-icon {
            width: 68px;
            height: 68px;
            background: #EFF6FF;
            border: 2px solid #1B64F2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            color: #1B64F2;
            font-size: 32px;
            font-weight: 900;
            box-shadow: 0 8px 24px rgba(27, 100, 242, 0.22);
            animation: pulseSuccess 2s infinite;
        }
        @keyframes pulseSuccess {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .success-title {
            font-size: 19px;
            font-weight: 900;
            color: #111827;
            margin-bottom: 6px;
        }
        .success-desc {
            font-size: 13px;
            color: #4B5563;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        .countdown-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            border-radius: 999px;
            font-size: 12.5px;
            font-weight: 700;
            color: #1B64F2;
            margin-bottom: 12px;
        }
        .countdown-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #1B64F2;
            color: #FFFFFF;
            border-radius: 50%;
            font-weight: 900;
            font-size: 13px;
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .countdown-number.tick {
            transform: scale(1.35);
        }
        .progress-bar-wrap {
            width: 100%;
            height: 5px;
            background: #E2E8F0;
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #1B64F2, #5B9DFA);
            width: 100%;
            transition: width 1s linear;
        }
        .btn-return {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: #1B64F2;
            color: #FFFFFF;
            border: 1.5px solid #1B64F2;
            border-radius: 12px;
            padding: 13px;
            font-size: 13.5px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(27, 100, 242, 0.24);
        }
        .btn-return:hover {
            background: #1554D1;
            border-color: #1554D1;
        }
        .spinner {
            width: 18px;
            height: 18px;
            border: 2.5px solid rgba(255, 255, 255, 0.35);
            border-top-color: #FFFFFF;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .footer-note {
            text-align: center;
            margin-top: 18px;
            font-size: 11px;
            color: #6B7280;
        }
        .footer-note a {
            color: #1B64F2;
            text-decoration: none;
            font-weight: 700;
        }
        .footer-note a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="badge-gateway">
                <span class="badge-dot"></span>
                MIDTRANS SANDBOX GATEWAY
            </div>

            @if($order->status->value === 'paid' || $order->status->value === 'completed' || $order->status->value === 'shipped')
                <!-- Tampilan Sudah Lunas -->
                <div class="success-box">
                    <div class="success-icon">✓</div>
                    <h2 class="success-title">Pembayaran Berhasil!</h2>
                    <p class="success-desc">
                        Tagihan pesanan <strong>{{ $order->order_number }}</strong> telah lunas diverifikasi.
                    </p>

                    <div class="countdown-badge">
                        Otomatis kembali dalam
                        <span class="countdown-number" id="countdown-num-initial">5</span>
                        detik...
                    </div>
                    <div class="progress-bar-wrap">
                        <div id="progress-bar-initial" class="progress-bar-fill"></div>
                    </div>

                    <a href="intent://order/{{ $order->order_number }}#Intent;scheme=itselling;package=com.example.itselling_mobile;end" onclick="returnToApp(event)" class="btn-return">
                        <span>Buka Aplikasi IT Selling Sekarang ➔</span>
                    </a>
                </div>
            @else
                <!-- Form Pembayaran Virtual Account -->
                <div id="payment-form-section">
                    <h1 class="header-title">Pembayaran {{ $order->payment_channel ?? 'Virtual Account' }}</h1>
                    <p class="header-subtitle">Simulasi pembayaran instan untuk pengujian & demo aplikasi.</p>

                    <div class="bill-box">
                        <div class="bill-row">
                            <span>Nomor Invoice</span>
                            <span class="bill-value">{{ $order->order_number }}</span>
                        </div>
                        <div class="bill-row">
                            <span>Nama Pelanggan</span>
                            <span class="bill-value">{{ $order->recipient_name }}</span>
                        </div>
                        <div class="bill-row">
                            <span>Metode / Bank</span>
                            <span class="bill-value">{{ $order->payment_channel ?? 'BCA Virtual Account' }}</span>
                        </div>
                        <div class="bill-row">
                            <span>Total Tagihan</span>
                            <span class="total-amount">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form id="payment-form" onsubmit="handlePay(event)">
                        @if($order->payment_method === 'credit_card')
                            <div class="field-group">
                                <label class="field-label" for="card_number">Nomor Kartu Kredit (Midtrans Sandbox)</label>
                                <div class="input-wrapper">
                                    <input
                                        type="text"
                                        id="card_number"
                                        class="va-input"
                                        value="4811 •••• •••• 1114"
                                        readonly
                                    >
                                </div>
                            </div>
                        @else
                            <div class="field-group">
                                <label class="field-label" for="va_number">Nomor Virtual Account</label>
                                <div class="input-wrapper">
                                    <input
                                        type="text"
                                        id="va_number"
                                        name="va_number"
                                        class="va-input"
                                        value="{{ $order->va_number ?? '123458123456' }}"
                                        required
                                        placeholder="Contoh: 123458123456"
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        @endif

                        <button type="submit" id="btn-submit" class="btn-pay">
                            <span>BAYAR SEKARANG</span>
                        </button>
                    </form>
                </div>

                <!-- Tampilan Sukses (Awalnya Sembunyi) -->
                <div id="success-section" class="success-box" style="display: none;">
                    <div class="success-icon">✓</div>
                    <h2 class="success-title">Pembayaran Berhasil!</h2>
                    <p class="success-desc">
                        Tagihan pesanan <strong>{{ $order->order_number }}</strong> sebesar
                        <strong style="color: #1B64F2;">Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                        telah berhasil dilunasi.
                    </p>

                    <div class="countdown-badge">
                        Otomatis kembali dalam
                        <span class="countdown-number" id="countdown-num">5</span>
                        detik...
                    </div>
                    <div class="progress-bar-wrap">
                        <div id="progress-bar" class="progress-bar-fill"></div>
                    </div>

                    <a href="intent://order/{{ $order->order_number }}#Intent;scheme=itselling;package=com.example.itselling_mobile;end" onclick="returnToApp(event)" class="btn-return">
                        <span>Buka Aplikasi IT Selling Sekarang ➔</span>
                    </a>
                </div>
            @endif

            <div class="footer-note">
                Sistem Pembayaran Terintegrasi IT Selling •
                @if($order->payment_method === 'credit_card')
                    <span>Midtrans 3D-Secure Sandbox</span>
                @else
                    <a href="https://simulator.sandbox.midtrans.com/{{ strtolower($order->payment_channel ?? 'bca') }}/va/index" target="_blank">Web Simulator Midtrans</a>
                @endif
            </div>
        </div>
    </div>

    <script>
        const deepLinkUrl = "itselling://order/{{ $order->order_number }}";
        const intentUrl = "intent://order/{{ $order->order_number }}#Intent;scheme=itselling;package=com.example.itselling_mobile;end";

        function returnToApp(e) {
            if (e && e.preventDefault) e.preventDefault();
            
            // Coba buka aplikasi lewat Intent URI (paling handal di Chrome Android)
            try {
                window.location.href = intentUrl;
            } catch (err) {}

            setTimeout(() => {
                try {
                    window.location.href = deepLinkUrl;
                } catch (err) {}
            }, 250);
        }

        function runCountdown(numId, barId) {
            let secondsLeft = 5;
            const numEl = document.getElementById(numId);
            const barEl = document.getElementById(barId);

            if (barEl) barEl.style.width = '100%';

            // Efek getar halus saat sukses (didukung di Chrome Android)
            if (window.navigator && window.navigator.vibrate) {
                try { window.navigator.vibrate([80, 40, 80]); } catch (e) {}
            }

            const timer = setInterval(() => {
                secondsLeft--;
                if (numEl) {
                    numEl.innerText = secondsLeft;
                    numEl.classList.add('tick');
                    setTimeout(() => numEl.classList.remove('tick'), 200);
                }
                if (barEl) {
                    barEl.style.width = Math.max(0, (secondsLeft / 5) * 100) + '%';
                }

                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    returnToApp();
                }
            }, 1000);
        }

        @if($order->status->value === 'paid' || $order->status->value === 'completed' || $order->status->value === 'shipped')
            window.addEventListener('DOMContentLoaded', () => {
                runCountdown('countdown-num-initial', 'progress-bar-initial');
            });
        @endif

        async function handlePay(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit');
            const vaInput = document.getElementById('va_number');
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<div class="spinner"></div><span>Memproses Pembayaran...</span>';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch("{{ route('payment.web.pay', $order) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        va_number: vaInput ? vaInput.value.trim() : null
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    document.getElementById('payment-form-section').style.display = 'none';
                    document.getElementById('success-section').style.display = 'block';
                    runCountdown('countdown-num', 'progress-bar');
                } else {
                    alert(data.message || 'Gagal memproses pembayaran.');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (err) {
                alert('Terjadi kesalahan koneksi ke server.');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
    </script>
</body>
</html>
