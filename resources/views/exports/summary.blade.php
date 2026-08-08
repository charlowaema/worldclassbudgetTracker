<table>
    <tr>
        <td colspan="2" style="font-size: 16px; font-weight: bold;">Budget Tracker — Financial Report</td>
    </tr>
    <tr>
        <td>Period</td>
        <td>{{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</td>
    </tr>
    <tr>
        <td>Generated</td>
        <td>{{ $generatedAt }}</td>
    </tr>
    <tr><td></td><td></td></tr>
    <tr>
        <td style="font-weight: bold; background-color: #DCFCE7;">Total Income</td>
        <td style="font-weight: bold; background-color: #DCFCE7;">{{ number_format($income, 2) }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #FEE2E2;">Total Expense</td>
        <td style="font-weight: bold; background-color: #FEE2E2;">{{ number_format($expense, 2) }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #DBEAFE;">Net Savings</td>
        <td style="font-weight: bold; background-color: #DBEAFE;">{{ number_format($net, 2) }}</td>
    </tr>
    <tr><td></td><td></td></tr>
    <tr>
        <td>Savings Rate</td>
        <td>{{ $income > 0 ? round(($net / $income) * 100, 1) : 0 }}%</td>
    </tr>
</table>
