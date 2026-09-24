<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $customer->name }} - Statement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 24px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { text-align: center; color: #4b5563; margin-bottom: 4px; }
        .date-range { text-align: center; color: #6b7280; font-size: 10px; margin-bottom: 20px; }

        .summary { width: 100%; border: 1px solid #e5e7eb; border-collapse: collapse; margin-bottom: 16px; }
        .summary td { padding: 12px 16px; border-right: 1px solid #e5e7eb; vertical-align: top; width: 25%; }
        .summary td:last-child { border-right: none; }
        .summary .label { color: #6b7280; font-size: 9px; text-transform: uppercase; margin-bottom: 4px; }
        .summary .value { font-size: 15px; font-weight: bold; }

        .entry-count { color: #6b7280; font-size: 10px; margin-bottom: 8px; }

        table.ledger { width: 100%; border-collapse: collapse; border: 1px solid #1f2937; }
        table.ledger th { border: 1px solid #1f2937; background: #f3f4f6; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; color: #374151; }
        table.ledger th.num, table.ledger td.num { text-align: right; }
        table.ledger td { border: 1px solid #d1d5db; padding: 6px 8px; }

        .month-header td { background: #f9fafb; font-weight: bold; border: 1px solid #1f2937; padding: 6px 8px; }
        .month-header .opening { font-weight: normal; color: #6b7280; text-align: right; }

        .month-total td { background: #f9fafb; font-weight: bold; border: 1px solid #d1d5db; }

        .debit-cell { background: #fef2f2; color: #b91c1c; }
        .credit-cell { background: #f0fdf4; color: #15803d; }
        .balance-dr { color: #b91c1c; font-weight: bold; }
        .balance-cr { color: #15803d; font-weight: bold; }

        .footer { margin-top: 24px; text-align: center; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    <div class="title">{{ $customer->name }} — Statement</div>
    @if ($customer->phone)
        <div class="subtitle">Phone Number: {{ $customer->phone }}</div>
    @endif
    <div class="date-range">({{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }})</div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Opening Balance</div>
                <div class="value">₹{{ number_format($openingBalance, 2) }}</div>
            </td>
            <td>
                <div class="label">Total Debit (-)</div>
                <div class="value">₹{{ number_format($totalDebit, 2) }}</div>
            </td>
            <td>
                <div class="label">Total Credit (+)</div>
                <div class="value">₹{{ number_format($totalCredit, 2) }}</div>
            </td>
            <td>
                <div class="label">Net Balance</div>
                <div class="value {{ $due > 0 ? 'balance-dr' : 'balance-cr' }}">
                    ₹{{ number_format(abs($due), 2) }} {{ $due > 0 ? 'Dr' : 'Cr' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="entry-count">No. of Entries: {{ $entryCount }}</div>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width:12%">Date</th>
                <th style="width:38%">Details</th>
                <th class="num" style="width:16%">Debit (-)</th>
                <th class="num" style="width:16%">Credit (+)</th>
                <th class="num" style="width:18%">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($months as $monthLabel => $monthEntries)
                <tr class="month-header">
                    <td colspan="4">{{ $monthLabel }}</td>
                    <td class="opening">
                        @if ($loop->first)
                            (Opening Balance: ₹{{ number_format($openingBalance, 2) }})
                        @endif
                    </td>
                </tr>
                @foreach ($monthEntries as $entry)
                    <tr>
                        <td>{{ $entry['date']->format('d M') }}</td>
                        <td>{{ $entry['label'] }}</td>
                        <td class="num debit-cell">{{ $entry['type'] === 'billed' ? number_format($entry['amount'], 2) : '' }}</td>
                        <td class="num credit-cell">{{ $entry['type'] === 'received' ? number_format($entry['amount'], 2) : '' }}</td>
                        <td class="num {{ $entry['balance_after'] >= 0 ? 'balance-dr' : 'balance-cr' }}">
                            {{ number_format(abs($entry['balance_after']), 2) }} {{ $entry['balance_after'] >= 0 ? 'Dr' : 'Cr' }}
                        </td>
                    </tr>
                @endforeach
                <tr class="month-total">
                    <td colspan="2">{{ $monthLabel }} Total</td>
                    <td class="num">{{ number_format($monthEntries->where('type', 'billed')->sum('amount'), 2) }}</td>
                    <td class="num">{{ number_format($monthEntries->where('type', 'received')->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#9ca3af;padding:20px;">No billing history yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Generated on {{ now()->format('d M Y, h:i A') }}</div>
</body>
</html>
