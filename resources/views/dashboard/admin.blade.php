@extends('layouts.layout')
<style>
    .table-responsive {
        max-height: calc(5 * 40px);
        /* 5 baris x tinggi baris ~40px */
        overflow-y: auto;
    }

    /* Optional: tetap sticky untuk header */
    .table-responsive thead {
        background: #f8f9fa;
        /* warna header */
        z-index: 1;
    }
</style>
@section('content')
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">{{ $module }}</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <!-- [Payment Records] start -->
                <div class="col-12">
                    <div class="card stretch stretch-full">
                        <div class="row">
                            <div class="card-header">
                                <h5 class="card-title">Data Penjualan</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn">
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                                data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                                data-bs-toggle="expand"> </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card-header">
                                    <h5 class="card-title">Target Profit Penjualan Harian</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    {{-- <div id="payment-records-chart"></div> --}}
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kassa</th>
                                                    <th>Modal</th>
                                                    <th>Penjualan</th>
                                                    <th>Jasa</th>
                                                    <th>Tunai</th>
                                                    <th>Non Tunai</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Target Profit</th>
                                                    <th>Persentase Target</th>
                                                    <th>Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="target-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card-header">
                                    <h5 class="card-title">Target Profit Penjualan Bulanan</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    {{-- <div id="payment-records-chart"></div> --}}
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Kassa</th>
                                                    <th>Modal</th>
                                                    <th>Penjualan</th>
                                                    <th>Jasa</th>
                                                    <th>Tunai</th>
                                                    <th>Non Tunai</th>
                                                    <th>Sub Total</th>
                                                    <th>Total</th>
                                                    <th>Target Profit</th>
                                                    <th>Persentase Target</th>
                                                    <th>Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="target-bulanan-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Produk Paling Laku</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan-terlaku" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <th>Total Terjual</th>
                                                </tr>
                                            </thead>
                                            <tbody id="terlaku-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card-header">
                                    <h5 class="card-title">Produk Untung Banyak</h5>
                                </div>
                                <div class="card-body custom-card-action p-0">
                                    <div class="table-responsive p-3">
                                        <table id="tabel-penjualan-untung" class="table table-bordered table-striped">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <th>Total Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody id="untung-body">
                                                <!-- Data akan diisi lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                        <div class="card-footer">
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Produk</div>
                                        <h6 class="fw-bold text-dark">{{ $produk }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Costumer</div>
                                        <h6 class="fw-bold text-dark">{{ $costumer }}</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Laba Bersih Bulan
                                            {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F') }}
                                        </div>
                                        <h6 class="fw-bold text-dark">
                                            {{ 'Rp ' . number_format($laba_bersih, 0, ',', '.') }}
                                        </h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-dark" role="progressbar" style="width: 100%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Payment Records] end -->

                <!--! BEGIN: [Upcoming Schedule] !-->
                {{-- <div class="col-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Informasi PO Dari Outlet</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                            data-bs-toggle="refresh"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                            data-bs-toggle="expand"> </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!--! BEGIN: [Events] !-->
                            @forelse ($data as $po)
                                <div class="p-3 border border-dashed rounded-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div
                                                class="wd-50 ht-50 bg-soft-warning text-warning lh-1 d-flex align-items-center justify-content-center flex-column rounded-2 schedule-date">
                                                <span
                                                    class="fs-18 fw-bold mb-1 d-block">{{ \Carbon\Carbon::createFromFormat('d-m-Y', $po->tanggal_transaksi)->format('d') }}</span>
                                                <span
                                                    class="fs-10 fw-semibold text-uppercase d-block">{{ \Carbon\Carbon::createFromFormat('d-m-Y', $po->tanggal_transaksi)->format('M') }}</span>
                                            </div>
                                            <div class="text-dark">
                                                <a href="javascript:void(0);" class="fw-bold mb-2 text-truncate-1-line">
                                                    {{ $po->no_po }} - {{ $po->keterangan }}
                                                </a>
                                                <span class="fs-11 fw-normal text-muted text-truncate-1-line">
                                                    Total Qty: {{ $po->total_qty }} | Total: Rp
                                                    {{ number_format($po->total_harga, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="img-group lh-0 ms-3 justify-content-start d-none d-sm-flex">
                                            <a href="{{ route('admin.po-vw-outlet') }}"
                                                class="btn btn-primary btn-sm">
                                                Detail PO
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center">
                                    <i class="feather-slash fs-1"></i>
                                    <p class="mt-3 text-muted">Tidak ada data PO dari outlet.</p>
                                </div>
                            @endforelse
                            <!--! END: [Events] !-->
                        </div>
                    </div>
                </div> --}}
                <!--! END: [Upcoming Schedule] !-->
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            function loadPenjualanTarget() {
                $.get("/admin/get-penjualan-kasir-harian", function(res) {
                    const tbody = $("#target-body");
                    tbody.empty();

                    // Jika tidak ada data
                    if (!res.data || res.data.length === 0) {
                        tbody.append(`
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data</td>
                </tr>
            `);
                        return;
                    }

                    // Loop per tanggal
                    res.data.forEach(group => {
                        const kasirList = group.kasir || [];

                        kasirList.forEach((item, index) => {
                            let row = "<tr>";

                            // Kolom tanggal hanya di baris pertama
                            if (index === 0) {
                                row +=
                                    `<td rowspan="${kasirList.length}">${group.tanggal || "-"}</td>`;
                            }

                            // Data per kasir
                            row += `
                    <td>${item.nama || "-"}</td>
                    <td>${Number(item.modal || 0).toLocaleString()}</td>
                    <td>${Number(item.penjualan || 0).toLocaleString()}</td>
                    <td>${Number(item.jasa || 0).toLocaleString()}</td>
                    <td>${Number(item.tunai || 0).toLocaleString()}</td>
                    <td>${Number(item.non_tunai || 0).toLocaleString()}</td>
                    <td>${Number(item.sub_total || 0).toLocaleString()}</td>
                `;

                            // Kolom total, target profit, persentase (hanya 1x di tanggal yang sama)
                            if (index === 0) {
                                row += `
                        <td rowspan="${kasirList.length}">${Number(item.total || 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${Number(item.target_profit || 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${Number(item.persentase || 0)}%</td>
                    `;
                            }

                            // Profit selalu di kolom paling akhir (setiap kasir punya profit sendiri)
                            row += `
                    <td>${Number(item.profit || 0).toLocaleString()}</td>
                `;

                            row += "</tr>";
                            tbody.append(row);
                        });
                    });
                }).fail(function() {
                    $("#target-body").html(`
            <tr>
                <td colspan="12" class="text-center text-danger">Gagal memuat data</td>
            </tr>
        `);
                });
            }

            loadPenjualanTarget(); // Panggil fungsi untuk load penjualan harian

            function loadPenjualanTargetBulanan() {
                $.get("/admin/get-penjualan-kasir-bulanan", function(res) {
                    const tbody = $("#target-bulanan-body");
                    tbody.empty();

                    // Jika tidak ada data
                    if (!res.data || res.data.length === 0) {
                        tbody.append(`
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data</td>
                </tr>
            `);
                        return;
                    }

                    // Loop setiap grup per bulan
                    res.data.forEach(group => {
                        const kasirList = group.kasir || [];

                        kasirList.forEach((item, index) => {
                            let row = "<tr>";

                            // Kolom bulan hanya di baris pertama
                            if (index === 0) {
                                row +=
                                    `<td rowspan="${kasirList.length}">${group.bulan || "-"}</td>`;
                            }

                            // Data utama per kasir
                            row += `
                    <td>${item.nama || "-"}</td>
                    <td>${Number(item.modal || 0).toLocaleString()}</td>
                    <td>${Number(item.penjualan || 0).toLocaleString()}</td>
                    <td>${Number(item.jasa || 0).toLocaleString()}</td>
                    <td>${Number(item.tunai || 0).toLocaleString()}</td>
                    <td>${Number(item.non_tunai || 0).toLocaleString()}</td>
                    <td>${Number(item.sub_total || 0).toLocaleString()}</td>
                `;

                            // Kolom total, target, dan persentase hanya di baris pertama
                            if (index === 0) {
                                row += `
                        <td rowspan="${kasirList.length}">${Number(item.total || 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${Number(item.target_profit || 0).toLocaleString()}</td>
                        <td rowspan="${kasirList.length}">${Number(item.persentase || 0)}%</td>
                    `;
                            }

                            // Profit di akhir setiap baris
                            row += `
                    <td>${Number(item.profit || 0).toLocaleString()}</td>
                `;

                            row += "</tr>";
                            tbody.append(row);
                        });
                    });
                }).fail(function() {
                    $("#target-bulanan-body").html(`
            <tr>
                <td colspan="12" class="text-center text-danger">Gagal memuat data</td>
            </tr>
        `);
                });
            }


            loadPenjualanTargetBulanan(); // Panggil fungsi untuk load penjualan harian

            //     function loadPenjualanTerlaku() {
            //         $.get("/admin/get-penjualan-terlaku")
            //             .done(function(res) {
            //                 // Kosongkan tabel
            //                 $('#terlaku-body').empty();
            //                 $('#untung-body').empty();

            //                 // Cek jika tidak ada data
            //                 if (!res || (!res.top_laku && !res.top_untung)) {
            //                     $('#terlaku-body').append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                     $('#untung-body').append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                     return;
            //                 }

            //                 // Produk paling laku
            //                 if (res.top_laku && res.top_laku.length > 0) {
            //                     res.top_laku.forEach(item => {
            //                         $('#terlaku-body').append(`
        //             <tr>
        //                 <td>${item.nama_barang ?? '-'}</td>
        //                 <td class="text-center">${item.total_terjual ?? 0}</td>
        //             </tr>
        //         `);
            //                     });
            //                 } else {
            //                     $('#terlaku-body').append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 }

            //                 // Produk dengan untung terbanyak
            //                 if (res.top_untung && res.top_untung.length > 0) {
            //                     res.top_untung.forEach(item => {
            //                         $('#untung-body').append(`
        //             <tr>
        //                 <td>${item.nama_barang ?? '-'}</td>
        //                 <td class="text-end">Rp ${Number(item.total_profit || 0).toLocaleString('id-ID')}</td>
        //             </tr>
        //         `);
            //                     });
            //                 } else {
            //                     $('#untung-body').append(`
        //         <tr>
        //             <td colspan="2" class="text-center">Tidak ada data</td>
        //         </tr>
        //     `);
            //                 }
            //             })
            //             .fail(function(err) {
            //                 console.error("Gagal memuat data:", err);
            //                 $('#terlaku-body, #untung-body').html(`
        //     <tr>
        //         <td colspan="2" class="text-center text-danger">Gagal memuat data</td>
        //     </tr>
        // `);
            //             });
            //     }

            //     loadPenjualanTerlaku();
        });
    </script>
@endpush
