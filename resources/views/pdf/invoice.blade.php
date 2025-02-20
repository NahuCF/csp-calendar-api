<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            font-size: 14px;
        }
        .logo {
            text-align: center;
        }
        .header {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            padding:  0.2rem;
        }
        .total {
            text-align: right;
        }
        p {
            margin: .3rem 0;
        }
        .totals-table {
            margin-left: auto;
            width: 500px;
        }
        .row-venue {
            display: flex;
            flex-direction: column;
        }
        .no-wrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="logo">
            <img src="data:image/png;base64,{{ $logo }}" alt="Community Sports Partners" height="80">
        </div>
        
        <h1>Order Invoice</h1>

        <div class="header">
            <p><strong>Invoice Number:</strong> {{ $invoiceNumber }}</p>
            <p><strong>Date:</strong> {{ $date }}</p>
        </div>

        <p><strong>To:</strong> {{ $clientName }}</p>
        <p><strong>RE:</strong> Venue Rental</p>

        <table>
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Time</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Use</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    <td>{{ $item['position'] }}</td>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['facility_name'] }}-{{ $item['resource_name'] }}</td>
                    <td class="no-wrap">{{ $item['times'] }}</td>
                    <td class="no-wrap">${{ number_format($item['total_to_pay'], 2) }}</td>
                    <td class="no-wrap">${{ number_format($item['discount_amount'], 2) }}</td>
                    <td>{{ $item['sport_name'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="total"><strong>Currency</strong></td>
                <td>{{ $currencyName }}</td>
            </tr>
            <tr>
                <td class="total"><strong>Price</strong></td>
                <td>${{ $dataTotals['total_price'] }}</td>
            </tr>
            <tr>
                <td class="total"><strong>Discount</strong></td>
                <td>${{ $dataTotals['total_discount'] }}</td>
            </tr>
            <tr>
                <td class="total"><strong>HST</strong></td>
                <td>${{ $dataTotals['total_tax'] }}</td> </tr>
            <tr>
                <td class="total"><strong>Final Amount</strong></td>
                <td>${{ $dataTotals['total_price_after_taxes'] }}</td>
            </tr>
            <tr>
                <td class="total"><strong>Amount Paid</strong></td>
                <td>${{ $dataTotals['amount_paid'] }}</td>
            </tr>
            <tr>
                <td class="total"><strong>Balance Amount</strong></td>
                <td>${{ $dataTotals['amount_due'] }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
