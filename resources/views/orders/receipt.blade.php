<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 78mm;
            margin: 0 auto;
            padding: 8px 4px;
            color: #000;
            background: #fff;
            font-size: 12px;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            padding: 3px 0;
            vertical-align: top;
        }
        .no-print {
            margin-bottom: 15px;
            padding: 10px;
            background: #f1f5f9;
            border-radius: 6px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- Print Button for manual reprint -->
    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 14px; background: #4f46e5; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            🖨️ Print Receipt
        </button>
    </div>

    <!-- Receipt Header (Dynamic Shop Settings) -->
    <div class="text-center">
        
        <h2 style="margin: 0; font-size: 16px; font-weight: 900; text-transform: uppercase;">{{ $shopSettings['shop_name'] ?? 'SILK & DENIM' }}</h2>
        <p style="margin: 2px 0 0 0; font-size: 10px;">{{ $shopSettings['shop_address'] ?? '123 Fashion Street, Colombo' }}</p>
        <p style="margin: 2px 0 0 0; font-size: 10px;">Tel: {{ $shopSettings['phone_number'] ?? '+94 11 234 5678' }}</p>
    </div>

    <div class="divider"></div>

    <!-- Order Metadata -->
    <div style="font-size: 10px; line-height: 1.4;">
        <div>INVOICE: <strong>{{ $order->invoice_number }}</strong></div>
        <div>DATE: {{ $order->created_at->format('Y-m-d H:i:s') }}</div>
        <div>CASHIER: {{ optional($order->cashier)->name ?? 'Cashier' }}</div>
        <div>PAYMENT: {{ strtoupper($order->payment_method) }}</div>
    </div>

    <div class="divider"></div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">ITEM</th>
                <th style="text-align: center;">QTY</th>
                <th style="text-align: right;">PRICE</th>
                <th style="text-align: right;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td colspan="4" style="font-weight: bold; padding-top: 4px;">
                        {{ $item->product_name }} <span style="font-weight: normal; font-size: 9px;">({{ $item->item_code }})</span>
                    </td>
                </tr>
                <tr style="border-bottom: 1px dotted #ccc;">
                    <td></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Totals -->
    @php($receiptSubtotal = (float) $order->items->sum('subtotal'))
    @php($receiptDiscount = (float) $order->discount_amount)
    <table style="font-size: 12px;">
        <tr>
            <td>SUB TOTAL:</td>
            <td class="text-right">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($receiptSubtotal, 2) }}</td>
        </tr>
        @if($receiptDiscount > 0)
            <tr>
                <td>DISCOUNT{{ !empty($order->discount_percent) ? ' (' . rtrim(rtrim(number_format($order->discount_percent, 2), '0'), '.') . '%)' : '' }}:</td>
                <td class="text-right">- {{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($receiptDiscount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="font-bold">GRAND TOTAL:</td>
            <td class="text-right font-bold" style="font-size: 14px;">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>PAID AMOUNT:</td>
            <td class="text-right">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($order->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="font-bold">CHANGE DUE:</td>
            <td class="text-right font-bold">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($order->change_amount, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Footer Note (Dynamic Receipt Footer) -->
    <div class="text-center" style="font-size: 10px; margin-top: 8px;">
        <p style="margin: 0; font-weight: bold;">THANK YOU FOR SHOPPING WITH US!</p>
        <p style="margin: 3px 0 0 0;">{{ $shopSettings['receipt_footer'] ?? 'Exchanges allowed within 7 days with bill.' }}</p>
    </div>

</body>
</html>
