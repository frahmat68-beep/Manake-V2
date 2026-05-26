<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-logo {
            font-size: 28px;
            font-weight: 800;
            color: #d97706;
            letter-spacing: 2px;
            line-height: 1;
        }
        .header-title {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #4b5563;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .details-col {
            width: 50%;
            vertical-align: top;
        }
        .details-title {
            font-size: 11px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .details-value {
            font-size: 13px;
            color: #374151;
            font-weight: 500;
        }
        .details-value strong {
            color: #111827;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            vertical-align: middle;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .summary-col-label {
            width: 75%;
            text-align: right;
            padding: 6px 12px;
            color: #6b7280;
        }
        .summary-col-value {
            width: 25%;
            text-align: right;
            padding: 6px 12px;
            font-weight: 600;
            color: #111827;
        }
        .summary-total-label {
            width: 75%;
            text-align: right;
            padding: 12px;
            font-weight: 800;
            font-size: 14px;
            color: #d97706;
            border-top: 2px solid #e5e7eb;
        }
        .summary-total-value {
            width: 25%;
            text-align: right;
            padding: 12px;
            font-weight: 800;
            font-size: 16px;
            color: #d97706;
            border-top: 2px solid #e5e7eb;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .footer-note {
            background-color: #fafafa;
            border: 1px solid #f3f4f6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 40px;
            font-size: 11px;
            color: #6b7280;
        }
        .footer-note strong {
            color: #374151;
        }
    </style>
</head>
<body>

<div class="container">
    
    <!-- Top Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">MANAKE</td>
            <td class="header-title">INVOICE SEWA</td>
        </tr>
    </table>

    <hr style="border: none; border-top: 1px solid #e5e7eb; margin-bottom: 25px;">

    <!-- Details block -->
    <table class="details-table">
        <tr>
            <td class="details-col">
                <div class="details-title">Diterbitkan Kepada</div>
                <div class="details-value">
                    <strong>{{ $order->user->name }}</strong><br>
                    Email: {{ $order->user->email }}<br>
                    Telp: {{ $order->user->profile->phone ?? '-' }}<br>
                    No. Identitas: {{ $order->user->profile->identity_number ?? '-' }}
                </div>
            </td>
            <td class="details-col" style="padding-left: 20px;">
                <div class="details-title">Detail Reservasi</div>
                <div class="details-value">
                    No. Invoice: <strong>{{ $order->order_number }}</strong><br>
                    Tanggal Sewa: {{ $order->rental_start_date->format('d M Y') }} s/d {{ $order->rental_end_date->format('d M Y') }}<br>
                    Durasi Sewa: <strong>{{ $order->duration_days }} Hari</strong><br>
                    Status Bayar: <span class="badge badge-success">Lunas</span><br>
                    Waktu Lunas: {{ $order->paid_at ? $order->paid_at->format('d M Y H:i') . ' WIB' : '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Nama Alat</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 20%;">Tarif / Hari</th>
                <th class="text-right" style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->equipment_name }}</strong><br>
                        <span style="font-size: 11px; color: #9ca3af;">Reservasi: {{ $item->rental_start_date->format('d/m/Y') }} - {{ $item->rental_end_date->format('d/m/Y') }}</span>
                    </td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->item_subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Block -->
    <table class="summary-table">
        <tr>
            <td class="summary-col-label">Subtotal Sewa</td>
            <td class="summary-col-value">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-col-label">PPN (11%)</td>
            <td class="summary-col-value">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @if($order->additional_fee > 0)
            <tr>
                <td class="summary-col-label">Biaya Tambahan</td>
                <td class="summary-col-value">Rp {{ number_format($order->additional_fee, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="summary-total-label">Grand Total</td>
            <td class="summary-total-value">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Guidelines / Legal notes -->
    <div class="footer-note">
        <p style="margin-top: 0; font-weight: 700; color: #374151; font-size: 12px; margin-bottom: 8px;">Ketentuan Validasi Pengambilan:</p>
        <ol style="margin: 0; padding-left: 15px;">
            <li style="margin-bottom: 5px;">Tunjukkan lembar cetak invoice ini atau Kode Reservasi kepada petugas piket saat penyerahan alat media.</li>
            <li style="margin-bottom: 5px;">Harap membawa Kartu Identitas asli (KTP/KTM/SIM) yang datanya sesuai dengan profil akun terdaftar.</li>
            <li style="margin-bottom: 0;">Petugas berhak menolak serah terima jika data identitas fisik tidak cocok dengan data pemesan di invoice.</li>
        </ol>
        <p style="margin-top: 15px; margin-bottom: 0; text-align: center; font-style: italic; color: #9ca3af; font-size: 10px;">
            Terima kasih telah mempercayakan rental alat media Anda di Manake Production.
        </p>
    </div>

</div>

</body>
</html>
