@extends('layouts.layout')
@section('content')
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10 text-capitalize">Accounting</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item text-capitalize">{{ $module }}</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items ">
                    <div class="d-flex d-md-none"><a class="page-header-right-close-toggle" href="/widgets/tables"><svg
                                stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                                stroke-linecap="round" stroke-linejoin="round" class="me-2" height="16" width="16"
                                xmlns="http://www.w3.org/2000/svg">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg><span>Back</span></a></div>
                </div>
                <div class="d-md-none d-flex align-items-center"><a class="page-header-right-open-toggle"
                        href="/widgets/tables"><svg stroke="currentColor" fill="none" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" class="fs-20" height="1em"
                            width="1em" xmlns="http://www.w3.org/2000/svg">
                            <line x1="21" y1="10" x2="7" y2="10"></line>
                            <line x1="21" y1="6" x2="3" y2="6"></line>
                            <line x1="21" y1="14" x2="3" y2="14"></line>
                            <line x1="21" y1="18" x2="7" y2="18"></line>
                        </svg></a></div>
            </div>
        </div>
        <div class="main-content">
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card stretch stretch-full widget-tasks-content  ">
                        <div class="card-header">
                            <h5 class="card-title">Tabel {{ $module }}</h5>
                            {{-- <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper"
                                style="width: 250px;">
                                <select name="uuid_user" id="filter-outlet" class="form-select form-select-sm">
                                    <option value="">Semua Outlet</option>
                                    @foreach ($outlet as $o)
                                        <option value="{{ $o->uuid_user }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" aria-label="Refresh" data-bs-original-title="Refresh">
                                        <span class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </span>
                                    </div>
                                    <div data-bs-toggle="tooltip" aria-label="Maximize/Minimize"
                                        data-bs-original-title="Maximize/Minimize"><span
                                            class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body custom-card-action p-0">
                            {{-- <button class="btn btn-success m-3" id="export-excel"><i
                                    class="fa-solid fa-file-excel me-2"></i> Export</button> --}}
                            <div class="table-responsive">
                                <table style="width: 100%" id="dataTables" class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-capitalize">No</th>
                                            <th class="text-capitalize">no. bukti</th>
                                            <th class="text-capitalize">Tanggal Transaksi</th>
                                            <th class="text-capitalize">Pembayaran</th>
                                            <th class="text-capitalize">created by</th>
                                            <th class="text-capitalize">jasa</th>
                                            <th class="text-capitalize">total penjualan</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(angka);
        }

        const initDatatable = () => {
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#dataTables')) {
                $('#dataTables').DataTable().clear().destroy();
            }

            $('#dataTables').DataTable({
                responsive: true,
                pageLength: 10,
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.get-lap-transaksi') }}",
                columns: [{
                        data: null,
                        class: 'mb-kolom-nomor align-content-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_bukti',
                        class: 'mb-kolom-text text-left align-content-center'
                    },
                    {
                        data: 'tanggal_transaksi',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row, meta) {
                            // Ubah created_at ke objek Date
                            let createdAt = new Date(row.created_at);

                            // Ambil jam, menit, detik
                            let jam = String(createdAt.getHours()).padStart(2, '0');
                            let menit = String(createdAt.getMinutes()).padStart(2, '0');
                            let detik = String(createdAt.getSeconds()).padStart(2, '0');

                            // Format HH:MM:SS
                            let waktu = jam + ':' + menit + ':' + detik;

                            return data + ' ' + waktu;
                        }
                    },
                    {
                        data: 'pembayaran',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'nama_user',
                        class: 'mb-kolom-tanggal text-left align-content-center'
                    },
                    {
                        data: 'total_jasa_setelah_diskon',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row, meta) {
                            return formatRupiah(data);
                        }
                    },
                    {
                        data: 'total_penjualan',
                        class: 'mb-kolom-tanggal text-left align-content-center',
                        render: function(data, type, row, meta) {
                            return formatRupiah(data);
                        }
                    }
                ],
            });
        };

        $(function() {
            // Reload datatable kalau filter berubah
            $('#filter-outlet').on('change', function() {
                $('#dataTables').DataTable().ajax.reload();
            });

            // Event tombol export (ambil nilai filter saat diklik)
            // $('#export-excel').on('click', function(e) {
            //     e.preventDefault();

            //     let outlet = $('#filter-outlet').val() ? $('#filter-outlet').val() : '';
            //     let url = '/admin/accounting/export-excel' + (outlet ? '/' + outlet : '');
            //     window.open(url, '_blank');
            // });

            initDatatable();
        });
    </script>
@endpush
