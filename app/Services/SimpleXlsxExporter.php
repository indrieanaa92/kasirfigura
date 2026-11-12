<?php

namespace App\Services;

use Illuminate\Support\Str;

class SimpleXlsxExporter
{
    /**
     * Generate a valid .xlsx binary payload for the monthly sales report using only core PHP + zlib.
     * Used automatically as a zero-dependency fallback when maatwebsite/excel or ext-zip is unavailable.
     *
     * @param  array<string, mixed>  $report
     */
    public function buildMonthlyReportXlsx(array $report): string
    {
        $rows = $this->buildReportRows($report);
        $sheetXml = $this->buildWorksheetXml($rows);
        $sheetTitle = $this->escapeXml(Str::limit('Laporan ' . ($report['selectedMonth'] ?? ''), 31, ''));

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',

            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',

            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="' . $sheetTitle . '" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>',

            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',

            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="3">'
                . '<font><sz val="11"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="11"/><color rgb="FF78350F"/><name val="Calibri"/></font>'
                . '</fonts>'
                . '<fills count="4">'
                . '<fill><patternFill patternType="none"/></fill>'
                . '<fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FF78350F"/></patternFill></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/></patternFill></fill>'
                . '</fills>'
                . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="3">'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
                . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
                . '<xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
                . '</cellXfs>'
                . '</styleSheet>',

            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        return $this->createZipArchive($files);
    }

    /**
     * Build structured table rows covering revenue summary, top-selling frames, and monthly transactions.
     *
     * @param  array<string, mixed>  $report
     * @return list<array{style: int, cells: list<int|float|string>}>
     */
    private function buildReportRows(array $report): array
    {
        $rows = [];
        $monthLabel = (string) ($report['monthLabel'] ?? '');

        $rows[] = ['style' => 1, 'cells' => ['LAPORAN PENJUALAN INUL WIJAYA FRAME - ' . mb_strtoupper($monthLabel)]];
        $rows[] = ['style' => 0, 'cells' => []];

        $rows[] = ['style' => 2, 'cells' => ['Ringkasan Keuangan', 'Nilai']];
        $rows[] = ['style' => 0, 'cells' => ['Periode Laporan', $monthLabel . ' (' . $report['selectedMonth'] . ')']];
        $rows[] = ['style' => 0, 'cells' => ['Total Penghasilan Bulan Terpilih', $this->formatRupiah($report['filteredRevenue'])]];
        $rows[] = ['style' => 0, 'cells' => ['Pemasukan Hari Ini (' . ($report['todayLabel'] ?? '') . ')', $this->formatRupiah($report['dailyRevenue'])]];
        $rows[] = ['style' => 0, 'cells' => ['Pemasukan Minggu Ini (' . ($report['currentWeekLabel'] ?? '') . ')', $this->formatRupiah($report['weeklyRevenue'])]];
        $rows[] = ['style' => 0, 'cells' => ['Jumlah Transaksi Bulan Terpilih', (int) $report['filteredTransactionCount']]];
        $rows[] = ['style' => 0, 'cells' => ['Total Unit Figura Terjual', (int) $report['filteredUnitsSold']]];
        $rows[] = ['style' => 0, 'cells' => []];

        $rows[] = ['style' => 1, 'cells' => ['PEMASUKAN TIAP MINGGU - ' . mb_strtoupper($monthLabel)]];
        $rows[] = ['style' => 2, 'cells' => ['Minggu', 'Rentang Tanggal', 'Jumlah Transaksi', 'Total Pemasukan']];
        foreach (($report['weeklyBreakdown'] ?? []) as $week) {
            $rows[] = [
                'style' => 0,
                'cells' => [
                    (string) $week['label'],
                    (string) $week['range_label'],
                    (int) $week['transaction_count'],
                    $this->formatRupiah($week['total_revenue']),
                ],
            ];
        }
        $rows[] = ['style' => 0, 'cells' => []];

        $rows[] = ['style' => 1, 'cells' => ['PEMASUKAN SETIAP HARI - ' . mb_strtoupper($monthLabel)]];
        $rows[] = ['style' => 2, 'cells' => ['Tanggal', 'Jumlah Transaksi', 'Total Pemasukan']];
        if (! empty($report['dailyBreakdown']) && count($report['dailyBreakdown']) > 0) {
            foreach ($report['dailyBreakdown'] as $day) {
                $rows[] = [
                    'style' => 0,
                    'cells' => [
                        (string) $day['label'],
                        (int) $day['transaction_count'],
                        $this->formatRupiah($day['total_revenue']),
                    ],
                ];
            }
        } else {
            $rows[] = ['style' => 0, 'cells' => ['Belum ada pemasukan harian pada periode ini.']];
        }
        $rows[] = ['style' => 0, 'cells' => []];

        $rows[] = ['style' => 1, 'cells' => ['FIGURA PALING BANYAK TERJUAL BULAN INI']];
        $rows[] = ['style' => 2, 'cells' => ['Peringkat', 'Nama Figura', 'Ukuran', 'Jumlah Terjual (Unit)', 'Total Penjualan']];

        if (count($report['topProducts']) > 0) {
            foreach ($report['topProducts'] as $index => $product) {
                $rows[] = [
                    'style' => 0,
                    'cells' => [
                        $index + 1,
                        (string) $product->product_name,
                        (string) $product->product_size,
                        (int) $product->total_qty,
                        $this->formatRupiah($product->total_sales),
                    ],
                ];
            }
        } else {
            $rows[] = ['style' => 0, 'cells' => ['Belum ada penjualan figura pada periode ini.']];
        }

        $rows[] = ['style' => 0, 'cells' => []];
        $rows[] = ['style' => 1, 'cells' => ['DATA TRANSAKSI BULANAN']];
        $rows[] = ['style' => 2, 'cells' => ['No', 'No. Invoice', 'Tanggal & Waktu', 'Kasir', 'Nama Pembeli', 'Status', 'Metode Bayar', 'Rincian Figura Terjual', 'Total Pembayaran']];

        if (count($report['transactions']) > 0) {
            foreach ($report['transactions'] as $index => $trx) {
                $itemsText = $trx->details->map(function ($detail) {
                    $name = $detail->product?->name ?? 'Produk Dihapus';
                    $size = $detail->product?->size ?? '-';
                    return "{$name} ({$size}) x{$detail->quantity}";
                })->implode(', ');

                $rows[] = [
                    'style' => 0,
                    'cells' => [
                        $index + 1,
                        (string) $trx->invoice_number,
                        $trx->created_at->format('d/m/Y H:i'),
                        (string) ($trx->user?->name ?? '-'),
                        (string) ($trx->customer_name ?? 'Umum'),
                        (string) $trx->customer_type_label,
                        (string) $trx->payment_method_label,
                        $itemsText !== '' ? $itemsText : '-',
                        $this->formatRupiah($trx->total_amount),
                    ],
                ];
            }
        } else {
            $rows[] = ['style' => 0, 'cells' => ['Tidak ada transaksi pada bulan ini.']];
        }

        return $rows;
    }

    /**
     * Render XML for worksheet 1 including dynamic column widths.
     *
     * @param  list<array{style: int, cells: list<int|float|string>}>  $rows
     */
    private function buildWorksheetXml(array $rows): string
    {
        $colWidths = [];
        foreach ($rows as $row) {
            foreach ($row['cells'] as $colIndex => $value) {
                $len = mb_strlen((string) $value) + 4;
                $colWidths[$colIndex] = max($colWidths[$colIndex] ?? 12, min(55, $len));
            }
        }

        $colsXml = '<cols>';
        foreach ($colWidths as $colIndex => $width) {
            $colNum = $colIndex + 1;
            $colsXml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $sheetDataXml = '<sheetData>';
        foreach ($rows as $rowIndex => $row) {
            $rowNum = $rowIndex + 1;
            $style = $row['style'];
            $sheetDataXml .= '<row r="' . $rowNum . '">';

            foreach ($row['cells'] as $colIndex => $value) {
                $cellRef = $this->columnLetter($colIndex) . $rowNum;
                $styleAttr = $style > 0 ? ' s="' . $style . '"' : '';

                if (is_int($value) || is_float($value)) {
                    $sheetDataXml .= '<c r="' . $cellRef . '"' . $styleAttr . '><v>' . $value . '</v></c>';
                } else {
                    $sheetDataXml .= '<c r="' . $cellRef . '"' . $styleAttr . ' t="inlineStr"><is><t>'
                        . $this->escapeXml((string) $value)
                        . '</t></is></c>';
                }
            }

            $sheetDataXml .= '</row>';
        }
        $sheetDataXml .= '</sheetData>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . ($colWidths !== [] ? $colsXml : '')
            . $sheetDataXml
            . '</worksheet>';
    }

    /**
     * Build a minimal PKZIP archive in memory using deflate compression.
     *
     * @param  array<string, string>  $files
     */
    private function createZipArchive(array $files): string
    {
        $localData = '';
        $centralDir = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $content) {
            $crc = crc32($content) & 0xFFFFFFFF;
            $uncompressedSize = strlen($content);
            $compressed = gzdeflate($content, 6);
            $compressedSize = strlen($compressed);
            $nameLen = strlen($name);

            $localHeader = "\x50\x4b\x03\x04"
                . "\x14\x00"         // Version needed (2.0)
                . "\x00\x00"         // General purpose bit flag
                . "\x08\x00"         // Compression method (8 = deflate)
                . "\x00\x00\x00\x00" // Mod time & date
                . pack('V', $crc)
                . pack('V', $compressedSize)
                . pack('V', $uncompressedSize)
                . pack('v', $nameLen)
                . pack('v', 0)       // Extra field length
                . $name;

            $localData .= $localHeader . $compressed;

            $centralHeader = "\x50\x4b\x01\x02"
                . "\x14\x00"         // Version made by
                . "\x14\x00"         // Version needed
                . "\x00\x00"         // Bit flag
                . "\x08\x00"         // Compression method
                . "\x00\x00\x00\x00" // Mod time & date
                . pack('V', $crc)
                . pack('V', $compressedSize)
                . pack('V', $uncompressedSize)
                . pack('v', $nameLen)
                . pack('v', 0)       // Extra length
                . pack('v', 0)       // Comment length
                . pack('v', 0)       // Disk number
                . pack('v', 0)       // Internal attrs
                . pack('V', 0)       // External attrs
                . pack('V', $offset)
                . $name;

            $centralDir .= $centralHeader;
            $offset += strlen($localHeader) + $compressedSize;
            $count++;
        }

        $endOfCentralDir = "\x50\x4b\x05\x06"
            . "\x00\x00\x00\x00"
            . pack('v', $count)
            . pack('v', $count)
            . pack('V', strlen($centralDir))
            . pack('V', $offset)
            . "\x00\x00";

        return $localData . $centralDir . $endOfCentralDir;
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = intdiv($index - $mod - 1, 26);
        }

        return $letter;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function formatRupiah(mixed $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}

