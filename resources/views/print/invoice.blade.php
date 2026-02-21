<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .footer { margin-top: 50px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>{{ $invoice->shop->name ?? 'AGROSYSTEMS' }}</h1>
        <p>{{ $invoice->shop->address ?? 'Main Terminal, Block A' }}</p>
        <p>GSTIN: {{ $invoice->shop->gstin ?? 'UNREGISTERED' }}</p>
    </div>

    <div class="details">
        <p><strong>Invoice No:</strong> {{ $invoice->invoice_no }}</p>
        <p><strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y') }}</p>
        <p><strong>Farmer:</strong> {{ $invoice->farmer->name }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->rate }}</td>
                <td>{{ $item->total_amount }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">Subtotal</th>
                <td>{{ $invoice->sub_total }}</td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: right;">Tax</th>
                <td>{{ $invoice->tax_amount }}</td>
            </tr>
            <tr>
                <th colspan="3" style="text-align: right;">Grand Total</th>
                <td>{{ $invoice->total_amount }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Term & Conditions: Goods once sold will not be taken back.</p>
        <br><br>
        <p style="text-align: right;">Authorized Signatory</p>
    </div>
</body>
</html>
