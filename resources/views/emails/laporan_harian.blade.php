<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "Courier New", monospace;
            background-color: #f9f9f9;
            color: #222;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 650px;
            margin: auto;
        }

        h2,
        h3 {
            text-align: center;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 15px;
        }

        table th,
        table td {
            border-bottom: 1px solid #ccc;
            padding: 6px 8px;
            text-align: right;
        }

        table th:first-child,
        table td:first-child {
            text-align: left;
        }

        .total {
            font-weight: bold;
            border-top: 2px solid #000;
        }

        .footer {
            font-size: 13px;
            margin-top: 20px;
        }

        pre {
            font-family: "Courier New", monospace;
            white-space: pre-wrap;
            font-size: 13px;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>* LAPORAN HARIAN AKTIVITAS BENGKEL *</h2>
        <h3 style="text-transform: uppercase">mmmaeroautomotive</h3>
        <p style="text-align:center;">Jl. Tun Abdul Razak</p>

        <p><strong>TANGGAL :</strong> {{ $tanggal }}</p>
        <p><strong>Penjualan Hari Ini :</strong></p>

        @php
            $target = 1500000; // Target harian
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Kasir</th>
                    <th>Tunai</th>
                    <th>Non Tunai</th>
                    <th>Total</th>
                    <th>Profit</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTunai = 0;
                    $grandNonTunai = 0;
                    $grandTotal = 0;
                    $grandProfit = 0;
                @endphp

                @foreach ($data as $row)
                    <tr>
                        <td>{{ $row['kasir'] }}</td>
                        <td>Rp {{ number_format($row['tunai'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['non_tunai'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                        <td>{{ $row['persentase'] }}%</td>
                    </tr>

                    @php
                        $grandTunai += $row['tunai'];
                        $grandNonTunai += $row['non_tunai'];
                        $grandTotal += $row['total'];
                        $grandProfit += $row['profit'];
                    @endphp
                @endforeach

                <tr class="total">
                    <td>Total</td>
                    <td>Rp {{ number_format($grandTunai, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($grandNonTunai, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($grandProfit, 0, ',', '.') }}</td>
                    <td>
                        {{-- {{ $grandTotal > 0 ? round(($grandProfit / $grandTotal) * 100, 2) : 0 }}% --}}
                        {{ $target > 0 ? round(($grandProfit / $target) * 100, 2) : 0 }}%
                    </td>
                </tr>
            </tbody>
        </table>

        <hr style="margin: 25px 0;">

        <pre>
Penjualan yang di void :


Data yang dihapus hari ini :
----------------------------
 hpl2     | PRODUK |

PRODUK BARU YANG DI ENTRY HARI INI:
------------------------------------------------------------------------------
| JAM  | KODE BARANG   |      NAMA BARANG              | HARGA JUAL| CREATE BY
------------------------------------------------------------------------------


PIUTANG OVERDUE :
-------------------------------------------------------------------------------
| TANGGAL | NOBUKTI | JATUH TEMPO | OVER | CUSTOMER | JUMLAH | KETERANGAN
-------------------------------------------------------------------------------

TOTAL OVERDUE :


** end of report **
        </pre>

        <div class="footer" style="text-align:center;">
            <p>~ Laporan ini dikirim otomatis oleh sistem ~</p>
        </div>
    </div>
</body>

</html>
