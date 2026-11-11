<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Services\SalesReportService;
use App\Services\SimpleXlsxExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function __construct(
        private readonly SalesReportService $reportService,
        private readonly SimpleXlsxExporter $fallbackXlsxExporter
    ) {}

    /**
     * Display the Admin financial dashboard with weekly/monthly widgets and monthly filter.
     */
    public function dashboard(Request $request): View
    {
        $report = $this->reportService->getMonthlyReport($request->query('month'));

        return view('admin.dashboard', $report);
    }

    /**
     * Export monthly financial report (transactions, total revenue, top-selling frames) to Excel (.xlsx).
     */
    public function exportExcel(Request $request): BinaryFileResponse|StreamedResponse
    {
        $report = $this->reportService->getMonthlyReport($request->query('month'));
        $filename = 'laporan-penjualan-figura-' . $report['selectedMonth'] . '.xlsx';

        if (class_exists(Excel::class) && class_exists(\ZipArchive::class)) {
            return Excel::download(new MonthlyReportExport($report), $filename);
        }

        $binary = $this->fallbackXlsxExporter->buildMonthlyReportXlsx($report);

        return response()->streamDownload(
            static function () use ($binary): void {
                echo $binary;
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}
