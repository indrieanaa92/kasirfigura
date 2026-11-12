<table>
    <thead>
        <tr>
            <th colspan="6">LAPORAN PENJUALAN FIGURA - {{ strtoupper($monthLabel) }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td><strong>Ringkasan Keuangan</strong></td>
            <td><strong>Nilai</strong></td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Periode Laporan</td>
            <td>{{ $monthLabel }} ({{ $selectedMonth }})</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Total Penghasilan Bulan Terpilih</td>
            <td>Rp {{ number_format($filteredRevenue, 0, ',', '.') }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Penghasilan 1 Minggu Terakhir</td>
            <td>Rp {{ number_format($weeklyRevenue, 0, ',', '.') }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Penghasilan 1 Bulan Terakhir (30 Hari)</td>
            <td>Rp {{ number_format($monthlyRollingRevenue, 0, ',', '.') }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Jumlah Transaksi Bulan Terpilih</td>
            <td>{{ $filteredTransactionCount }}</td>
            <td colspan="4"></td>
        </tr>
        <tr>
            <td>Total Unit Figura Terjual</td>
            <td>{{ $filteredUnitsSold }}</td>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="6"><strong>FIGURA PALING BANYAK TERJUAL BULAN INI</strong></td>
        </tr>
        <tr>
            <td><strong>Peringkat</strong></td>
            <td><strong>Nama Figura</strong></td>
            <td><strong>Ukuran</strong></td>
            <td><strong>Jumlah Terjual (Unit)</strong></td>
            <td><strong>Total Penjualan</strong></td>
            <td></td>
        </tr>
        @forelse ($topProducts as $index => $product)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->product_size }}</td>
                <td>{{ $product->total_qty }}</td>
                <td>Rp {{ number_format($product->total_sales, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="6">Belum ada penjualan figura pada periode ini.</td>
            </tr>
        @endforelse

        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="9"><strong>DATA TRANSAKSI BULANAN</strong></td>
        </tr>
        <tr>
            <td><strong>No</strong></td>
            <td><strong>No. Invoice</strong></td>
            <td><strong>Tanggal &amp; Waktu</strong></td>
            <td><strong>Kasir</strong></td>
            <td><strong>Nama Pembeli</strong></td>
            <td><strong>Status</strong></td>
            <td><strong>Metode Bayar</strong></td>
            <td><strong>Rincian Figura Terjual</strong></td>
            <td><strong>Total Pembayaran</strong></td>
        </tr>
        @forelse ($transactions as $index => $trx)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $trx->invoice_number }}</td>
                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->user?->name ?? '-' }}</td>
                <td>{{ $trx->customer_name ?? 'Umum' }}</td>
                <td>{{ $trx->customer_type_label }}</td>
                <td>{{ $trx->payment_method_label }}</td>
                <td>
                    @foreach ($trx->details as $detail)
                        {{ $detail->product?->name ?? 'Produk Dihapus' }} ({{ $detail->product?->size }}) x{{ $detail->quantity }}@if (! $loop->last), @endif
                    @endforeach
                </td>
                <td>Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9">Tidak ada transaksi pada bulan ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

