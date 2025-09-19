<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #555;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            font-size: 18px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .details label {
            font-weight: bold;
            width: 40%;
        }

        .details span {
            width: 60%;
            text-align: right;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .total {
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>Generated on {{ \Carbon\Carbon::now()->format('Y-m-d') }}</p>
            <p>Domotena</p>
        </div>

        <div class="section">
            <h2>Payment Details</h2>
            <div class="details">
                <label>Payment ID:</label>
                <span>{{ $payment->id }}</span>
            </div>
            <div class="details">
                <label>Transaction Reference:</label>
                <span>{{ $payment->transaction_reference ?? '-' }}</span>
            </div>
            <div class="details">
                <label>Payment Type:</label>
                <span>{{ ucfirst($payment->payment_type) }}</span>
            </div>
            <div class="details">
                <label>Status:</label>
                <span>{{ ucfirst($payment->status) }}</span>
            </div>
            <div class="details">
                <label>Due Date:</label>
                <span>{{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="details">
                <label>Paid At:</label>
                <span>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('Y-m-d') : '-' }}</span>
            </div>
            <div class="details">
                <label>Payment Method:</label>
                <span>{{ $payment->payment_method?->name ?? 'Unknown' }}</span>
            </div>
        </div>

        <div class="section">
            <h2>Tenant & Property</h2>
            <div class="details">
                <label>Tenant:</label>
                <span>{{ $payment->tenant?->full_name ?? 'Unknown' }}</span>
            </div>
            <div class="details">
                <label>Property:</label>
                <span>{{ $payment->property?->name ?? 'Unknown' }}</span>
            </div>
        </div>

        <div class="section">
            <h2>Financial Details</h2>
            <table class="table">
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
                <tr>
                    <td>Payment Amount</td>
                    <td>{{ number_format($payment->total_amount, 2) }} {{ $payment->currency }}</td>
                </tr>
                @if ($payment->late_fee)
                    <tr>
                        <td>Late Fee</td>
                        <td>{{ number_format($payment->late_fee, 2) }} {{ $payment->currency }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Total</td>
                    <td>{{ number_format($payment->total_amount + ($payment->late_fee ?? 0), 2) }}
                        {{ $payment->currency }}</td>
                </tr>
            </table>
        </div>

        @if ($payment->notes)
            <div class="section">
                <h2>Notes</h2>
                <p>{{ $payment->notes }}</p>
            </div>
        @endif
    </div>
</body>

</html>
