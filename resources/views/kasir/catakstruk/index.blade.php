<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice</title>

    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 25px;
            padding: 0;
            background: #fff;
        }

        .header-table {
            width: 100%;
            background: #ff6500;
            color: #fff;
            border-collapse: collapse;
        }

        .header-table,
        .header-table th,
        .header-table td {
            border: 0px;
        }

        .info-section table,
        .info-section table th,
        .info-section table td {
            border: 0px;
        }

        #tabel-payment,
        #tabel-payment th,
        #tabel-payment td {
            border: 0px;
        }

        .header-left img {
            width: 200px;
        }

        .header-left {
            float: left;
            width: 70%;
        }

        .title {
            float: right;
            width: 30%;
            text-align: right;
        }

        .info-section {
            padding: 20px 10px;
        }

        .info-row {
            display: flex;
            width: 60%;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .table-header {
            background: #ff6500;
            color: #fff;
            padding: 10px;
            font-weight: bold;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        .footer {
            background: #ff6500;
            color: #fff;
            text-align: center;
            padding: 15px;
            font-size: 18px;
            margin-top: 40px;
            font-weight: bold;
        }
    </style>

</head>

<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="width:70%">
                <img src="{{ public_path('logo-bengkel.png') }}" style="width:150px; margin-bottom: 5px"> <br>
                <strong>Aero Automotive</strong><br>
                Service Center<br>
                Jl. Tun Abdul Razak, Paccinongan,Kec. Somba Opu <br> ,Kabupaten Gowa, Sulawesi Selatan 90233
            </td>
            <td style="width:30%; text-align:right; font-size:32px; font-weight:bold;">
                INVOICE
            </td>
        </tr>
    </table>

    <!-- CUSTOMER INFO -->
    <div class="info-section">

        {{-- Data Customer --}}
        <table style="width: 100%; border-collapse: collapse;">
            @if (!empty($data['customer']))
                <tr>
                    <td style="width: 150px; font-weight: bold;">Nama</td>
                    <td>: {{ $data['customer']['nama'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Alamat</td>
                    <td>: {{ $data['customer']['alamat'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Telpon</td>
                    <td>: {{ $data['customer']['nomor'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">No. Polisi</td>
                    <td>: {{ $data['customer']['plat'] ?? '-' }}</td>
                </tr>
            @endif

            <tr>
                <td style="font-weight: bold; padding-top:10px;">Tanggal</td>
                <td style="padding-top:10px;">: {{ $data['tanggal'] ?? '-' }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold;">No. Bukti</td>
                <td>: {{ $data['no_bukti'] ?? '-' }}</td>
            </tr>
        </table>

    </div>

    <!-- == DATA SPAREPART == -->
    @if (!empty($data['items']))
        <div class="table-header">Data Sparepart</div>

        <table>
            <tr>
                <th>Qty</th>
                <th>Harga/Pcs</th>
                <th>Nama Barang</th>
                <th>Total</th>
            </tr>

            @foreach ($data['items'] as $item)
                <tr>
                    <td>{{ $item['qty'] }}</td>
                    <td>Rp{{ number_format($item['harga'], 0, ',', '.') }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td>Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif


    <!-- == DATA JASA == -->
    @if (!empty($data['jasa']))
        <div class="table-header">Data Jasa</div>

        <table>
            <tr>
                <th>Nama Jasa</th>
                <th>QTY</th>
                <th>Harga</th>
                <th>Total</th>
            </tr>

            @foreach ($data['jasa'] as $j)
                <tr>
                    <td>{{ $j['nama'] }}</td>
                    <td>{{ $j['qty'] }}</td>
                    <td>Rp{{ number_format($j['harga'], 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($j['subtotal'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @php
        $totalProduk = collect($data['items'])->sum('subtotal') ?? 0;
        $totalJasa = $data['totalJasa'] ?? 0;

        $discount = $data['discount'] ?? null;

        // Hitung jasa setelah diskon
        $totalJasaAfterDiscount = $totalJasa;

        if ($discount) {
            if (str_contains($discount, '%')) {
                // Diskon persen
                $angka = floatval(str_replace('%', '', $discount));
                $potongan = ($totalJasa * $angka) / 100;
                $totalJasaAfterDiscount = $totalJasa - $potongan;
            } elseif (is_numeric($discount)) {
                // Diskon nominal
                $totalJasaAfterDiscount = max(0, $totalJasa - floatval($discount));
            }
        }

        // Grand total akhir
        $finalGrandTotal = $totalProduk + $totalJasaAfterDiscount;
    @endphp

    <table style="margin-top: 30px; width: 45%; float: right; border-collapse: collapse;">
        <tr>
            <td>Total Produk</td>
            <td>Rp{{ number_format($totalProduk, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td>Total Jasa</td>
            <td>Rp{{ number_format($totalJasa, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td>Diskon Jasa</td>
            <td>
                @php
                    if (!$discount) {
                        echo '-';
                    } else {
                        if (str_contains($discount, '%')) {
                            echo $discount;
                        } elseif (is_numeric($discount)) {
                            echo 'Rp' . number_format($discount, 0, ',', '.');
                        } else {
                            echo $discount;
                        }
                    }
                @endphp
            </td>
        </tr>

        <tr>
            <td><strong>Grand Total</strong></td>
            <td><strong>Rp{{ number_format($finalGrandTotal, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <!-- PAYMENT METHOD -->
    <table id="tabel-payment" style="margin-top: 25px; width: 60%; border-collapse: collapse;">
        <tr>
            <td style="padding: 6px 10px; font-weight: bold; width: 35%;">Pembayaran</td>
            <td style="padding: 6px 10px;">{{ $data['pembayaran'] ?? '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 6px 10px; font-weight: bold;">Bank</td>
            <td style="padding: 6px 10px;">Bank Central Asia (BCA)</td>
        </tr>
        <tr>
            <td style="padding: 6px 10px; font-weight: bold;">No Rekening</td>
            <td style="padding: 6px 10px;">768 • 739 • 6381</td>
        </tr>
        <tr>
            <td style="padding: 6px 10px; font-weight: bold;">Atas Nama</td>
            <td style="padding: 6px 10px;">MOH. ALIF</td>
        </tr>
    </table>


    <!-- FOOTER -->
    <div class="footer">Kami Senang Melayani Anda</div>

</body>

</html>
