<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyReportExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(
        private readonly array $report
    ) {}

    /**
     * Render the Blade view containing summary revenue, top-selling frames, and transactions.
     */
    public function view(): View
    {
        return view('exports.monthly-report', $this->report);
    }

    /**
     * Set Excel worksheet title (max 31 characters).
     */
    public function title(): string
    {
        return Str::limit('Laporan ' . $this->report['selectedMonth'], 31, '');
    }

    /**
     * Apply clean warm walnut-gold styling to key header rows.
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '78350F'],
                ],
            ],
        ];
    }
}

