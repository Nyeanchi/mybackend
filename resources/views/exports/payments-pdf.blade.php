<!DOCTYPE html>
<html>

<head>
    <title>Payments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Payments Report for {{ $user->first_name }} {{ $user->last_name }} ({{ $user->role }})</h1>
    <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tenant</th>
                <th>Property</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Paid Date</th>
                <th>Transaction Ref</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->tenant?->full_name ?? 'N/A' }}</td>
                    <!-- Assuming full_name accessor in User model -->
                    <td>{{ $payment->property?->name ?? 'N/A' }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                    <td>{{ $payment->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ $payment->paid_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ $payment->transaction_reference ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
