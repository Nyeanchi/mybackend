<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsExport implements FromCollection, WithHeadings
{
    protected $payments;

    public function __construct(Collection $payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments->map(function ($payment) {
            return [
                'ID' => $payment->id,
                'Tenant' => $payment->tenant?->full_name ?? 'N/A',
                'Property' => $payment->property?->name ?? 'N/A',
                'Amount' => number_format($payment->amount, 2),
                'Status' => ucfirst($payment->status),
                'Due Date' => $payment->due_date?->format('Y-m-d') ?? 'N/A',
                'Paid Date' => $payment->paid_date?->format('Y-m-d') ?? 'N/A',
                'Transaction Ref' => $payment->transaction_reference ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Tenant', 'Property', 'Amount', 'Status', 'Due Date', 'Paid Date', 'Transaction Ref'
        ];
    }
}
