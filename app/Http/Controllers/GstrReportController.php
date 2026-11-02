<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Support\Gstr1Report;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GST return reporting — a GSTR-1 return is filed per GSTIN, and GST
 * registration in this app lives on the branch (not the company), so every
 * action here is scoped to exactly one GST-registered branch at a time,
 * unlike SalesReportController's "all branches by default" convention.
 */
class GstrReportController extends Controller
{
    public function index(Request $request): View
    {
        $branches = Branch::where('company_id', $request->user()->company_id)
            ->whereNotNull('gstin')
            ->orderBy('name')
            ->get();

        [$branch, $year, $month] = $this->resolveSelection($request, $branches);

        $data = $branch
            ? Gstr1Report::build($branch, $year, $month)
            : null;

        return view('gst-reports.gstr1', [
            'branches' => $branches,
            'selectedBranch' => $branch,
            'year' => $year,
            'month' => $month,
            'monthOptions' => $this->monthOptions(),
            'data' => $data,
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $branches = Branch::where('company_id', $request->user()->company_id)
            ->whereNotNull('gstin')
            ->orderBy('name')
            ->get();

        [$branch, $year, $month] = $this->resolveSelection($request, $branches);

        abort_if(! $branch, 404);

        $data = Gstr1Report::build($branch, $year, $month);
        $period = Carbon::createFromDate($year, $month, 1)->format('F Y');

        return response()->streamDownload(function () use ($data, $branch, $period) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['GSTR-1 — '.$branch->name.' (GSTIN: '.$branch->gstin.') — '.$period]);
            fputcsv($out, []);

            fputcsv($out, ['B2B — Business to Business (invoice-wise)']);
            fputcsv($out, ['GSTIN', 'Receiver Name', 'Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Rate (%)', 'Taxable Value', 'CGST', 'SGST', 'IGST']);
            foreach ($data['b2b'] as $row) {
                fputcsv($out, [
                    $row['gstin'], $row['customer_name'], $row['invoice_number'],
                    optional($row['invoice_date'])->format('d-m-Y'), number_format($row['invoice_value'], 2, '.', ''),
                    $row['place_of_supply'], $row['rate'], number_format($row['taxable_value'], 2, '.', ''),
                    number_format($row['cgst'], 2, '.', ''), number_format($row['sgst'], 2, '.', ''), number_format($row['igst'], 2, '.', ''),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['B2CL — Business to Consumer, Large (inter-state, invoice value over ₹2,50,000)']);
            fputcsv($out, ['Invoice Number', 'Invoice Date', 'Invoice Value', 'Place of Supply', 'Rate (%)', 'Taxable Value', 'IGST']);
            foreach ($data['b2cl'] as $row) {
                fputcsv($out, [
                    $row['invoice_number'], optional($row['invoice_date'])->format('d-m-Y'),
                    number_format($row['invoice_value'], 2, '.', ''), $row['place_of_supply'], $row['rate'],
                    number_format($row['taxable_value'], 2, '.', ''), number_format($row['igst'], 2, '.', ''),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['B2CS — Business to Consumer, Small (aggregated by place of supply and rate)']);
            fputcsv($out, ['Place of Supply', 'Rate (%)', 'Taxable Value', 'CGST', 'SGST', 'IGST']);
            foreach ($data['b2cs'] as $row) {
                fputcsv($out, [
                    $row['place_of_supply'], $row['rate'], number_format($row['taxable_value'], 2, '.', ''),
                    number_format($row['cgst'], 2, '.', ''), number_format($row['sgst'], 2, '.', ''), number_format($row['igst'], 2, '.', ''),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['HSN-wise Summary']);
            fputcsv($out, ['HSN Code', 'UQC', 'Rate (%)', 'Total Quantity', 'Taxable Value', 'CGST', 'SGST', 'IGST']);
            foreach ($data['hsn'] as $row) {
                fputcsv($out, [
                    $row['hsn_code'], $row['uqc'], $row['rate'], rtrim(rtrim(number_format($row['quantity'], 2), '0'), '.'),
                    number_format($row['taxable_value'], 2, '.', ''), number_format($row['cgst'], 2, '.', ''),
                    number_format($row['sgst'], 2, '.', ''), number_format($row['igst'], 2, '.', ''),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Summary']);
            fputcsv($out, ['Total Invoices', 'Total Taxable Value', 'Total CGST', 'Total SGST', 'Total IGST', 'Total Invoice Value']);
            fputcsv($out, [
                $data['summary']['invoice_count'], number_format($data['summary']['taxable_value'], 2, '.', ''),
                number_format($data['summary']['cgst'], 2, '.', ''), number_format($data['summary']['sgst'], 2, '.', ''),
                number_format($data['summary']['igst'], 2, '.', ''), number_format($data['summary']['invoice_value'], 2, '.', ''),
            ]);

            fclose($out);
        }, 'gstr1-'.$branch->gstin.'-'.$period.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array{0: ?Branch, 1: int, 2: int}
     */
    private function resolveSelection(Request $request, $branches): array
    {
        $branchId = $request->integer('branch_id');
        $branch = $branches->firstWhere('id', $branchId) ?? $branches->first();

        $year = $request->integer('year') ?: (int) now()->format('Y');
        $month = $request->integer('month') ?: (int) now()->format('m');

        if ($month < 1 || $month > 12) {
            $month = (int) now()->format('m');
        }

        return [$branch, $year, $month];
    }

    /**
     * Last 18 months (covers the usual GST annual-return lookback window),
     * newest first.
     */
    private function monthOptions(): array
    {
        $options = [];
        $cursor = now()->startOfMonth();

        for ($i = 0; $i < 18; $i++) {
            $options[] = [
                'year' => (int) $cursor->format('Y'),
                'month' => (int) $cursor->format('m'),
                'label' => $cursor->format('F Y'),
            ];
            $cursor->subMonth();
        }

        return $options;
    }
}
