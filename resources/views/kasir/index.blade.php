<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') . ' | ' . $module }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('logo_favicon.png') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/sweet-alert/sweetalert2.min.css') }}">
</head>

<body>

    <form id="form-kasir">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar kiri -->
                <div class="col-md-2 sidebar p-3 d-flex flex-column min-vh-100">
                    <div>
                        <div class="d-flex justify-content-center"><img src="{{ asset('logo.png') }}" class="img-fluid"
                                alt=""></div>
                        <div class="text-center mb-3">
                            <h6 class="fw-bold text-uppercase">mmmaeroautomotive</h6>
                            <small>Jl. Tun Abdul Razak</small>
                        </div>

                        <div class="text-center mb-3">
                            <h4 class="bg-custom text-white p-2 rounded fs-2">{{ $nomor_urut }}</h4>
                            <p class="fw-bold">KASIR<br>{{ auth()->user()->nama }}</p>
                        </div>

                        <h6 class="fw-bold">PRODUK TERPILIH</h6>
                        <input type="hidden" name="uuid_jasa[]" id="uuid-jasa-hidden">

                        <!-- Scroll area produk -->
                        <div class="produk-terpilih overflow-auto" style="max-height: 350px;">
                            <!-- Produk terpilih akan muncul di sini -->
                        </div>
                    </div>

                    <!-- Tombol tetap di bawah -->
                    <div class="d-grid gap-2 mt-auto">
                        <button type="button" onclick="window.location.href = '{{ route('logout') }}'"
                            class="btn btn-outline-danger btn-sm">📤 Keluar</button>
                        <button type="button" id="open-stock" class="btn btn-outline-secondary btn-sm">📦 Stok
                            Barang</button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-7 p-3 d-flex flex-column min-vh-100">
                    <!-- Judul -->
                    <h5 class="bg-custom text-white text-center py-2 rounded">BARANG BELANJA</h5>
                    <!-- Input scan (bisa disembunyikan kalau mau) -->
                    {{-- <input type="text" id="scanInput" class="form-control" placeholder="Scan barcode disini"
                        autofocus style="opacity:0; position:absolute; left:-9999px;"> --}}
                    <!-- Area scroll untuk tabel + form -->
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <table class="table table-bordered table-striped table-sm mb-0" id="cartTable">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form bawah -->
                    <div class="row m-0">
                        <div class="col-md-12 mb-2">
                            <input type="text" id="searchPlat" class="form-control form-control-sm"
                                name="search_plat" placeholder="Cari Plat">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="nama"
                                placeholder="Nama Customer">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="alamat"
                                placeholder="Alamat">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="nomor"
                                placeholder="Nomor Telp">
                        </div>
                        <div class="col-md-3 mb-2">
                            <input type="text" class="form-control form-control-sm" name="plat"
                                placeholder="Plat">
                        </div>
                    </div>

                    <!-- Shortcut tombol selalu di bawah -->
                    <div class="shortcut-bar d-flex justify-content-between flex-wrap py-2 mt-auto">
                        <button type="button" id="btn-f1" class="btn btn-outline-primary btn-sm shortcut-btn">F1
                            Search</button>
                        <button type="button" id="btn-f2" class="btn btn-outline-success btn-sm shortcut-btn">F2
                            Tambah</button>
                        <button type="button" id="btn-f3" class="btn btn-outline-danger btn-sm shortcut-btn">F3
                            Hapus</button>
                        <button type="button" id="btn-f4" class="btn btn-outline-warning btn-sm shortcut-btn">F4
                            Edit Qty</button>
                        <button type="button" class="btn btn-outline-info btn-sm shortcut-btn">F5 Reload</button>
                        <button type="button" id="btn-f6" class="btn btn-outline-dark btn-sm shortcut-btn">F6 Cetak
                            Ulang</button>
                        <button type="button" id="btn-f7" class="btn btn-outline-secondary btn-sm shortcut-btn">F7
                            Discount</button>
                        <button type="button" id="btn-f8" class="btn btn-outline-success btn-sm shortcut-btn">F8
                            Simpan</button>
                        <button type="button" id="btn-f9" class="btn btn-outline-danger btn-sm shortcut-btn">F9
                            Batal</button>
                        <button type="button" id="btn-f10" class="btn btn-outline-info btn-sm shortcut-btn">F10
                            Fullscreen</button>
                    </div>
                </div>

                <!-- Sidebar kanan -->
                <div class="col-md-3 p-3 d-grid align-content-between">
                    <div class="bg-custom total-box mb-3">
                        TOTAL BELANJA <br>
                        <span class="fs-1" id="grandTotal">Rp 0</span> <br>
                        <small id="item">0 item</small>
                    </div>

                    <div class="d-grid">

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="check-jasa">
                                <label class="form-check-label" for="check-jasa">
                                    Pilih Jasa Service
                                </label>
                            </div>

                            <div id="list-jasa" class="d-none list-jasa-scroll">
                                <!-- List jasa akan dimasukkan di sini -->
                            </div>
                        </div>

                        <!-- Hidden input untuk menyimpan metode pembayaran (default: Tunai) -->
                        <input type="hidden" name="pembayaran" id="pembayaran" value="Tunai">

                        <!-- Tombol pilihan pembayaran -->
                        <button type="button" class="btn btn-primary w-100 mb-2 pay-btn" data-metode="Tunai">💵
                            PEMBAYARAN TUNAI</button>
                        <button type="button" class="btn btn-outline-dark w-100 pay-btn"
                            data-metode="Transfer Bank">🏦
                            Transfer Bank</button>

                        <div class="mt-5">
                            <button type="button" class="btn btn-outline-primary w-100 mb-2">💾 Simpan Transaksi
                                (F8)</button>
                            <button type="button" class="btn btn-outline-danger w-100 mb-2">❌ Batal Transaksi
                                (F9)</button>
                            <button type="button" id="btn-closing" class="btn btn-outline-success w-100">📂 Closing
                                Kasir</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Produk Manual</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode-produk" class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" id="kode-produk"
                            placeholder="Masukkan kode produk">
                    </div>
                    <div class="mb-3">
                        <label for="qty-produk" class="form-label">Qty</label>
                        <input type="number" class="form-control" id="qty-produk" value="1" min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btn-save-produk">Tambah</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="stockModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="stockModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="stockModalLabel">List Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <div class="mb-2">
                                <input type="text" id="searchInput" class="form-control"
                                    placeholder="Cari produk (kode/nama barang)...">
                            </div>

                            <table class="table table-bordered table-striped table-sm mb-0" id="produkTable">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Total Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="SearchModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="SearchModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="SearchModalLabel">List Produk</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <div class="mb-2">
                                <input type="text" id="searchInputModal" class="form-control"
                                    placeholder="Cari produk (kode/nama barang)...">
                            </div>

                            <table class="table table-bordered table-striped table-sm mb-0" id="produkTableModal">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="CetakUlangModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="CetakUlangModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="CetakUlangModalLabel">List Transaksi</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="flex-grow-1 overflow-auto">
                        <div class="table-wrapper mb-2">
                            <table class="table table-bordered table-striped table-sm mb-0" id="cetakUlangTableModal">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th>No Bukti</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/vendors/js/bootstrap.min.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/qz-tray.js"></script> --}}
    {{-- <script src="http://localhost:8182/qz-tray.js"></script> --}}
    {{-- <script src="{{ asset('qz-tray.js') }}"></script> --}}
    <script src="{{ asset('assets/sweet-alert/sweetalert2.min.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.5/qz-tray.js"></script> --}}
    <script>
        // Nonaktifkan certificate & signature (mode unsigned)
        // qz.security.setCertificatePromise(() => Promise.resolve(null));
        // qz.security.setSignaturePromise(null); // bukan function kosong, tapi null

        // (async () => {
        //     try {
        //         if (!qz.websocket.isActive()) {
        //             await qz.websocket.connect();
        //             console.log("✅ Connected to QZ Tray:", await qz.api.getVersion());
        //         }

        //         // Tes printer default
        //         const printer = await qz.printers.getDefault();
        //         console.log("🖨️ Printer default:", printer);

        //         // Tes print teks sederhana
        //         const config = qz.configs.create(printer);
        //         const data = ['Hello QZ Tray!'];
        //         await qz.print(config, data);

        //         console.log("✅ Tes print berhasil!");
        //     } catch (err) {
        //         console.error("❌ Error saat tes print:", err);
        //     }
        // })();



        // async function testPrint() {
        //     console.log("🧠 Menjalankan tes print...");
        //     try {
        //         if (!qz.websocket.isActive()) {
        //             await qz.websocket.connect();
        //         }

        //         const printerName = "POS-80-2"; // Ganti sesuai nama printer di QZ Tray
        //         const config = qz.configs.create(printerName);

        //         // ✅ Gunakan array data sederhana dengan format string biasa
        //         const data = [
        //             "\x1B\x40", // Reset printer
        //             "==============================\n",
        //             "     TES PRINT QZ TRAY OK     \n",
        //             "==============================\n",
        //             "Tanggal: " + new Date().toLocaleString() + "\n",
        //             "Terimakasih!\n\n\n\n\n\n\n",
        //             "\x1D\x56\x00" // Cut kertas
        //         ];

        //         await qz.print(config, data);
        //         console.log("✅ Struk uji berhasil dikirim ke printer");

        //     } catch (err) {
        //         console.error("❌ Error saat tes print:", err);
        //     }
        // }
    </script>

    <script>
        // qz.security.setCertificatePromise(() => Promise.resolve());
        // qz.security.setSignaturePromise(toSign => Promise.resolve());

        // document.addEventListener("DOMContentLoaded", async () => {
        //     console.log("🚀 Script QZ dimulai");
        //     try {
        //         await qz.websocket.connect();
        //         console.log("✅ connect() selesai");
        //         const version = await qz.api.getVersion();
        //         console.log("✅ Terhubung ke QZ Tray versi:", version);
        //     } catch (err) {
        //         console.error("❌ Gagal konek ke QZ Tray:", err);
        //     }
        // });

        document.addEventListener("DOMContentLoaded", () => {
            const scanInput = document.getElementById("scanInput");
            const modalEl = document.getElementById("exampleModal");
            const kodeProdukInput = document.getElementById("kode-produk");
            const btnSaveProduk = document.getElementById("btn-save-produk");

            const openStock = document.getElementById("open-stock");
            const modalStock = document.getElementById("stockModal");
            const search = document.getElementById("searchInput");

            const modalSearch = document.getElementById("SearchModal");
            const searchSearch = document.getElementById("searchInputModal");

            const modalCetakUlang = document.getElementById("CetakUlangModal");

            // variabel penting
            const cartTable = document.getElementById("cartTable");
            const grandTotalEl = document.getElementById("grandTotal");
            const itemTotalEl = document.getElementById("item");

            let checkJasa = document.getElementById("check-jasa");
            let selectJasa = document.getElementById("select-jasa");
            const listJasa = document.getElementById("list-jasa");

            let selectedJasa = []; // array untuk menyimpan jasa yang dipilih
            let totalJasa = 0;
            let totalJasaFinal = 0;
            let jasaDiscount = "0"; // menyimpan text discount (misal "10%" atau "5000")

            let form = document.getElementById("form-kasir");
            let formDataCancel = new FormData(form);

            // ----------------
            // F10: toggle fullscreen
            // ----------------
            const btnFullscreen = document.getElementById("btn-f10");
            if (btnFullscreen) {
                btnFullscreen.addEventListener("click", toggleFullscreen);
            }
            document.addEventListener("keydown", (e) => {
                if (e.key === "F10") {
                    e.preventDefault();
                    toggleFullscreen();
                }
            });

            // ----------------
            // F2: toggle modal
            // ----------------
            document.addEventListener("keydown", (e) => {
                if (e.key === "F2") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (modalEl.classList.contains("show")) {
                        modal.hide();
                    } else {
                        modal.show();
                    }
                }
            });

            openStock.addEventListener("click", (e) => {
                e.preventDefault();
                const modal = bootstrap.Modal.getOrCreateInstance(modalStock);
                if (modalStock.classList.contains("show")) {
                    modal.hide();
                } else {
                    // panggil pertama kali
                    loadStok();
                    modal.show();
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "F1") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalSearch);
                    if (modalSearch.classList.contains("show")) {
                        modal.hide();
                    } else {
                        loadProduk();
                        modal.show();
                    }
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "F6") {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(modalCetakUlang);
                    if (modalCetakUlang.classList.contains("show")) {
                        modal.hide();
                    } else {
                        loadPenjualanHariIni();
                        modal.show();
                    }
                }
            });

            // document.addEventListener("keydown", (e) => {
            //     if (e.key === "F11") {
            //         e.preventDefault();
            //         // const modal = bootstrap.Modal.getOrCreateInstance(modalPaket);
            //         // if (modalPaket.classList.contains("show")) {
            //         //     modal.hide();
            //         // } else {
            //         loadPaket();
            //         // modal.show();
            //         // }
            //     }
            // });

            // ----------------
            // Fokus otomatis
            // ----------------

            // optional: flag tambahan kalau mau
            let __swalOpen = false;

            function keepFocus(e) {
                // 1) Kalau ada modal bootstrap terbuka → jangan pindah fokus
                if (typeof modalEl !== 'undefined' && modalEl?.classList?.contains("show")) return;
                if (typeof modalStock !== 'undefined' && modalStock?.classList?.contains("show")) return;
                if (typeof modalSearch !== 'undefined' && modalSearch?.classList?.contains("show")) return;

                // 2) Kalau SweetAlert2 sedang tampil → jangan pindah fokus
                //    cek dua cara supaya robust di berbagai versi Swal
                if ((typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) ||
                    document.body.classList.contains('swal2-shown') ||
                    __swalOpen === true) {
                    return;
                }

                // 3) Kalau user sedang mengetik di input/select/textarea/button/contenteditable → jangan ganggu
                const ae = document.activeElement;
                if (ae &&
                    (ae.tagName === 'INPUT' || ae.tagName === 'TEXTAREA' || ae.tagName === 'SELECT' ||
                        ae.tagName === 'BUTTON' || ae.isContentEditable)) {
                    return;
                }

                // 4) Tidak ada yang perlu diprioritaskan → kembalikan fokus ke scanInput
                if (typeof scanInput !== 'undefined' && scanInput) {
                    scanInput.focus()
                }
            }

            // pakai click saja (tidak keydown supaya tidak bentrok shortcut)
            document.addEventListener("click", keepFocus);

            // panggil sekali di awal
            keepFocus();

            // Modal utama
            if (typeof modalEl !== 'undefined' && modalEl) {
                modalEl.addEventListener("shown.bs.modal", () => {
                    if (typeof kodeProdukInput !== 'undefined' && kodeProdukInput) kodeProdukInput.focus();
                });
                modalEl.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            // Modal stok
            if (typeof modalStock !== 'undefined' && modalStock) {
                modalStock.addEventListener("shown.bs.modal", () => {
                    if (typeof search !== 'undefined' && search) search.focus();
                });
                modalStock.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            if (typeof modalSearch !== 'undefined' && modalSearch) {
                modalSearch.addEventListener("shown.bs.modal", () => {
                    if (typeof searchSearch !== 'undefined' && searchSearch) searchSearch.focus();
                });
                modalSearch.addEventListener("hidden.bs.modal", () => {
                    if (typeof scanInput !== 'undefined' && scanInput) scanInput.focus();
                });
            }

            // ----------------
            // Tombol save produk manual
            // ----------------
            if (btnSaveProduk) {
                btnSaveProduk.addEventListener("click", () => {
                    const kode = kodeProdukInput.value.trim();
                    const qty = parseInt(document.getElementById("qty-produk").value) || 1;
                    if (!kode) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Masukkan kode produk terlebih dahulu!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
                        return;
                    }
                    tambahProduk(kode, qty);

                    // reset dan tutup modal
                    kodeProdukInput.value = "";
                    document.getElementById("qty-produk").value = "1";
                    bootstrap.Modal.getInstance(modalEl).hide();
                });
            }

            let scanBuffer = "";
            let scanTimeout;

            document.addEventListener("keypress", function(e) {
                // Abaikan input manual
                if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
                    return;
                }

                if (e.key === "Enter") {
                    e.preventDefault();
                    const kode = scanBuffer.trim();

                    if (kode !== "") {
                        tambahProduk(kode);
                    }
                    scanBuffer = "";
                    return;
                }

                scanBuffer += e.key;

                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => {
                    scanBuffer = "";
                }, 500);
            });

            // ----------------
            // fungsi toggle fullscreen
            // ----------------
            function toggleFullscreen() {
                if (document.fullscreenElement) {
                    document.exitFullscreen?.();
                    document.webkitExitFullscreen?.();
                    document.msExitFullscreen?.();
                } else {
                    document.documentElement.requestFullscreen?.();
                    document.documentElement.webkitRequestFullscreen?.(); // Safari
                    document.documentElement.msRequestFullscreen?.(); // IE/Edge lama
                }
            }

            // toggle tampil/hidden select jasa
            checkJasa.addEventListener("change", function() {
                if (this.checked) {
                    listJasa.classList.remove("d-none");
                    loadJasa();
                } else {
                    listJasa.classList.add("d-none");
                    listJasa.innerHTML = "";
                    selectedJasa = [];
                    jasaDiscount = "0";
                    updateTotal();
                }
            });

            // load jasa dari backend
            function loadJasa() {
                fetch("/kasir/get-jasa")
                    .then(res => res.json())
                    .then(data => {
                        listJasa.innerHTML = "";
                        selectedJasa = []; // reset

                        data.forEach(jasa => {
                            const div = document.createElement("div");
                            div.classList.add("d-flex", "align-items-center", "justify-content-between",
                                "mb-2");

                            div.innerHTML = `
                                <span class="me-2" style="min-width:100px; width: 150px;">${jasa.nama}</span>
                                <div class="">
                                    <button type="button"
                                    class="btn btn-sm btn-success me-2 add-jasa"
                                    data-uuid="${jasa.uuid}"
                                    data-harga="${jasa.harga}">
                                    +
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-danger me-2 remove-jasa"
                                        data-uuid="${jasa.uuid}"
                                        data-harga="${jasa.harga}">
                                        -
                                    </button>
                                    <div class="btn btn-sm btn-info jumlah-jasa" data-uuid="${jasa.uuid}">
                                        = 0
                                    </div>
                                </div>
                            `;

                            listJasa.appendChild(div);
                        });

                        // tombol tambah jasa
                        document.querySelectorAll(".add-jasa").forEach(btn => {
                            btn.addEventListener("click", function() {
                                const uuid = this.dataset.uuid;
                                const harga = parseInt(this.dataset.harga);

                                selectedJasa.push({
                                    uuid,
                                    harga
                                });

                                updateJumlah(uuid); // update tampilan jumlah
                                updateTotal();
                            });
                        });

                        // tombol kurang jasa
                        document.querySelectorAll(".remove-jasa").forEach(btn => {
                            btn.addEventListener("click", function() {
                                const uuid = this.dataset.uuid;

                                // hapus hanya 1 kemunculan pertama dari UUID yang sama
                                const index = selectedJasa.findIndex(j => j.uuid === uuid);
                                if (index !== -1) {
                                    selectedJasa.splice(index, 1);
                                }

                                updateJumlah(uuid); // update tampilan jumlah
                                updateTotal();
                            });
                        });
                    })
                    .catch(err => console.error("❌ Error load jasa:", err));
            }

            function updateJumlah(uuid) {
                const jumlahEl = listJasa.querySelector(`.jumlah-jasa[data-uuid="${uuid}"]`);
                const jumlah = selectedJasa.filter(j => j.uuid === uuid).length;
                if (jumlahEl) {
                    jumlahEl.textContent = `= ${jumlah}`;
                }
            }

            function updateTotal() {
                const total = selectedJasa.reduce((sum, j) => sum + j.harga, 0);
                totalJasa = total;

                // jika ada discount sebelumnya → hitung ulang
                if (jasaDiscount !== "0") {
                    totalJasaFinal = hitungPotongan(totalJasa, jasaDiscount);
                } else {
                    totalJasaFinal = totalJasa;
                }

                hitungTotal(); // ini pakai totalJasaFinal

                // hapus hidden input lama
                document.querySelectorAll("input[name='uuid_jasa[]']").forEach(el => el.remove());

                // buat hidden input baru
                const form = document.querySelector("form");
                selectedJasa.forEach(j => {
                    let input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "uuid_jasa[]";
                    input.value = j.uuid;
                    form.appendChild(input);
                });
            }

            const buttons = document.querySelectorAll(".pay-btn");
            const inputMetode = document.getElementById("pembayaran");

            buttons.forEach(btn => {
                btn.addEventListener("click", function() {
                    // set value hidden input
                    inputMetode.value = this.dataset.metode;

                    // reset semua tombol ke outline
                    buttons.forEach(b => {
                        b.classList.remove("btn-primary");
                        b.classList.add("btn-outline-dark");
                    });

                    // tombol terpilih jadi biru
                    this.classList.remove("btn-outline-dark");
                    this.classList.add("btn-primary");
                });
            });

            // ----------------
            // Pilih row dengan klik
            // ----------------
            cartTable.addEventListener("click", function(e) {
                let row = e.target.closest("tr");
                if (!row) return;

                // hapus selected dari semua
                cartTable.querySelectorAll("tr").forEach(r => r.classList.remove("selected"));

                // tandai baris aktif
                row.classList.add("selected");
            });

            // ----------------
            // Keyboard event
            // ----------------
            document.addEventListener("keydown", function(e) {
                let selectedRow = cartTable.querySelector("tr.selected");
                let rows = [...cartTable.querySelectorAll("tbody tr")];

                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    if (!selectedRow && rows.length) {
                        rows[0].classList.add("selected");
                    } else {
                        let idx = rows.indexOf(selectedRow);
                        if (idx < rows.length - 1) {
                            selectedRow.classList.remove("selected");
                            rows[idx + 1].classList.add("selected");
                        }
                    }
                }

                if (e.key === "ArrowUp") {
                    e.preventDefault();
                    if (selectedRow) {
                        let idx = rows.indexOf(selectedRow);
                        if (idx > 0) {
                            selectedRow.classList.remove("selected");
                            rows[idx - 1].classList.add("selected");
                        }
                    }
                }

                if (e.key === "F4") {
                    e.preventDefault();
                    if (!selectedRow) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Pilih dulu produk di tabel yang ingin di edit!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
                        return;
                    }
                    editQty(selectedRow);
                }

                if (e.key === "F3") {
                    e.preventDefault();
                    if (!selectedRow) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Pilih dulu produk di tabel yang ingin di hapus!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1000
                        });
                        return;
                    }
                    hapusRow(selectedRow);
                }

                if (e.key === "F7") {
                    e.preventDefault();
                    if (!totalJasa || totalJasa <= 0) {
                        Swal.fire({
                            title: "Warning!",
                            text: "⚠️ Belum ada jasa yang ingin didiscount!",
                            icon: "warning",
                            showConfirmButton: false,
                            timer: 1200
                        });
                        return;
                    }

                    editDiscountOnTotal();
                }
            });

            function hitungPotongan(total, discount) {
                let potongan = 0;

                if (discount.includes("%")) {
                    let persen = parseFloat(discount.replace("%", "")) || 0;
                    potongan = total * (persen / 100);
                } else {
                    potongan = parseFloat(discount.replace(/\D/g, "")) || 0;
                }

                if (potongan > total) potongan = total;

                return Math.round(total - potongan);
            }

            function editDiscountOnTotal() {
                Swal.fire({
                    title: "Tambah Discount",
                    input: "text",
                    inputValue: jasaDiscount,
                    inputLabel: "Isi nominal (5000) atau persen (10%)",
                    showCancelButton: true,
                    confirmButtonText: "Simpan",
                    cancelButtonText: "Batal",
                    preConfirm: (v) => v.trim() || "0"
                }).then(r => {
                    if (!r.isConfirmed) return;

                    jasaDiscount = r.value;
                    totalJasaFinal = hitungPotongan(totalJasa, jasaDiscount);

                    hitungTotal(); // refresh tampilan total final
                });
            }

            // === EDIT QTY (F4) ===
            // Hitung dan simpan qty + harga tier
            function simpanQty(row, newQty, prices = [], hargaDefault = 0) {
                newQty = parseInt(newQty, 10);
                if (isNaN(newQty) || newQty < 0) newQty = 0;

                const qtyCell = row.querySelector('.qty');
                const qtyInput = row.querySelector('.qty-input');
                const hargaCell = row.querySelector('.harga'); // TIDAK diubah
                const jumlahCell = row.querySelector('.jumlah');

                if (!qtyCell || !jumlahCell) {
                    console.warn('Row tidak lengkap:', row);
                    return;
                }

                // qty = 0 → hapus baris
                if (newQty === 0) {
                    hapusRow(row);
                    return;
                }

                // pastikan harga default terisi (fallback ambil dari DOM)
                let hargaSatuanDefault = Number(hargaDefault) || 0;
                if (!hargaSatuanDefault && hargaCell) {
                    hargaSatuanDefault = parseInt(hargaCell.innerText.replace(/[^\d]/g, ''), 10) || 0;
                }

                // normalisasi prices
                const ps = (prices || []).map(p => ({
                    qty: Number(p.qty) || 0,
                    harga_jual: Number(p.harga_jual * p.qty) || 0
                }));

                // hitung JUMLAH:
                // default: hargaDefault * qty
                // jika ada tier dengan qty PERSIS SAMA → jumlah = harga_jual (bundling)
                let jumlah = hargaSatuanDefault * newQty;

                // pastikan ps sudah diurutkan ascending berdasarkan qty
                const match = ps
                    .filter(p => newQty >= p.qty) // ambil semua harga yang berlaku untuk qty ini
                    .pop(); // ambil harga terakhir (paling besar qty yg masih memenuhi)

                if (match) {
                    jumlah = match.harga_jual;
                }

                // simpan ke DOM
                qtyCell.innerText = newQty;
                if (qtyInput) qtyInput.value = newQty;

                // JANGAN ubah hargaCell (biarkan tetap harga/unit default)
                jumlahCell.innerText = 'Rp ' + Math.round(jumlah).toLocaleString();

                hitungTotal();
            }

            // Dialog edit qty (F4)
            function editQty(row) {
                const current = parseInt(row.querySelector('.qty')?.innerText, 10) || 1;
                const hargaDefault = Number(row.dataset.hargaDefault) || 0;
                let prices = [];
                try {
                    prices = JSON.parse(row.dataset.prices || '[]');
                } catch (e) {}

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Ubah Qty',
                        input: 'number',
                        inputValue: current,
                        inputAttributes: {
                            min: 0,
                            step: 1
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        cancelButtonText: 'Batal',
                        preConfirm: (v) => {
                            const n = parseInt(v, 10);
                            if (isNaN(n)) {
                                Swal.showValidationMessage('Qty tidak valid');
                                return false;
                            }
                            return n;
                        }
                    }).then(r => {
                        if (!r.isConfirmed) return;
                        const newQty = r.value;
                        if (newQty <= 0) {
                            hapusRow(row);
                            return;
                        }
                        simpanQty(row, newQty, prices, hargaDefault);
                    });
                } else {
                    const v = prompt('Qty baru:', current);
                    if (v === null) return;
                    const n = parseInt(v, 10);
                    if (!(n > 0)) {
                        hapusRow(row);
                        return;
                    }
                    simpanQty(row, n, prices, hargaDefault);
                }
            }

            // ----------------
            // Tambah Produk
            // ----------------
            function tambahProduk(kode, qtyManual = 1) {
                fetch(`/kasir/produk-get?kode=${kode}`)
                    .then(r => r.json())
                    .then(r => {
                        if (!r || r.status !== 'success') throw new Error(r.message ||
                            'Produk tidak ditemukan!');
                        const data = r.data;
                        const prices = (r.prices || []).map(p => ({
                            qty: Number(p.qty) || 0,
                            harga_jual: Number(p.harga_jual) || 0
                        }));

                        let row = cartTable.querySelector(`tbody tr[data-kode='${kode}']`);
                        let qty;

                        if (row) {
                            // pastikan dataset ada (dipakai saat F4)
                            row.dataset.hargaDefault = Number(data.harga_jual_default) || 0;
                            row.dataset.prices = JSON.stringify(prices);

                            qty = (parseInt(row.querySelector('.qty')?.innerText, 10) || 0) + parseInt(
                                qtyManual, 10);
                            simpanQty(row, qty, prices, data.harga_jual_default);
                        } else {
                            qty = parseInt(qtyManual, 10) || 1;
                            row = document.createElement('tr');
                            row.setAttribute('data-kode', kode);
                            row.setAttribute('data-uuid', data.uuid);
                            row.dataset.hargaDefault = Number(data.harga_jual_default) || 0;
                            row.dataset.prices = JSON.stringify(prices);

                            row.innerHTML = `
                            <td>${data.nama_barang}
                                <input type="hidden" name="produk_uuid[]" value="${data.uuid}">
                                <input type="hidden" name="qty[]" value="${qty}" class="qty-input">
                            </td>
                            <td class="qty">${qty}</td>
                            <td>${data.satuan || 'PCS'}</td>
                            <td class="harga">Rp ${Math.round(data.harga_jual_default).toLocaleString()}</td>
                            <td class="jumlah">Rp 0</td>
                            `;
                            (cartTable.querySelector('tbody') || cartTable).appendChild(row);

                            simpanQty(row, qty, prices, data.harga_jual_default);
                        }

                        // ---- Tambah foto ke div produk-terpilih ----
                        let produkTerpilihDiv = document.querySelector(".produk-terpilih");
                        if (produkTerpilihDiv) {
                            let existing = produkTerpilihDiv.querySelector(`.produk-item[data-kode='${kode}']`);
                            if (!existing) {
                                let newBox = document.createElement("div");
                                newBox.classList.add("border", "p-2", "mb-2", "text-center", "produk-item");
                                newBox.setAttribute("data-kode", kode);
                                newBox.innerHTML = `
                                    <img src="${data.foto ? '/storage/' + data.foto : 'https://via.placeholder.com/100x80'}"
                                        class="img-fluid" alt="${data.nama_barang}">
                                    <p class="small mt-1">${data.nama_barang}</p>
                                `;
                                produkTerpilihDiv.prepend(newBox);
                            }
                        }

                        hitungTotal();
                    })
                    .catch(err => {
                        Swal.fire({
                            title: "Gagal!",
                            text: err.message,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    });
            }

            // === EDIT DISCOUNT (F7) ===
            function editDiscount(row) {
                const currentDiscount = "0"; // bisa "5000" atau "10%"

                Swal.fire({
                    title: "Tambah Discount",
                    input: "text",
                    inputValue: currentDiscount,
                    inputLabel: "Isi nominal (5000) atau persen (10%)",
                    showCancelButton: true,
                    confirmButtonText: "Simpan",
                    cancelButtonText: "Batal",
                    preConfirm: (v) => {
                        if (!v) return "0";
                        return v.trim();
                    }
                }).then(r => {
                    if (!r.isConfirmed) return;

                    let discount = r.value;

                    let potongan = 0;

                    if (discount.includes("%")) {
                        let persen = parseFloat(discount.replace("%", "")) || 0;
                        potongan = harga * (persen / 100);
                    } else {
                        potongan = parseFloat(discount) || 0;
                    }

                    if (potongan > harga) potongan = harga; // jaga-jaga biar ga minus

                    let jumlahAkhir = harga - potongan;

                    const jumlahCell = row.querySelector(".jumlah");
                    if (jumlahCell) {
                        jumlahCell.innerText = "Rp " + Math.round(jumlahAkhir).toLocaleString();
                    }

                    hitungTotal();
                });
            }

            // ----------------
            // Hapus Row
            // ----------------
            function hapusRow(row) {
                const kode = row.getAttribute("data-kode");

                // hapus row dari tabel
                row.remove();

                // hapus gambar produk juga
                let produkTerpilihDiv = document.querySelector(".produk-terpilih");
                if (produkTerpilihDiv) {
                    let imgBox = produkTerpilihDiv.querySelector(`.produk-item[data-kode='${kode}']`);
                    if (imgBox) {
                        imgBox.remove();
                    }
                }

                hitungTotal();
            }

            // ----------------
            // Hitung total
            // ----------------
            function hitungTotal() {
                let grandTotal = 0;
                let item = 0;

                // ambil semua row produk
                let rows = cartTable.querySelectorAll("tbody tr");

                rows.forEach(row => {
                    let qtyCell = row.querySelector(".qty");
                    let jumlahCell = row.querySelector(".jumlah");

                    if (!qtyCell || !jumlahCell) {
                        console.warn("⚠️ Row tidak lengkap, dilewati:", row);
                        return;
                    }

                    let qty = parseInt(qtyCell.innerText) || 0;
                    let jumlahText = jumlahCell.innerText.replace(/[^\d]/g, "");
                    let jumlah = parseInt(jumlahText) || 0;

                    grandTotal += jumlah;
                    item += qty;
                });

                // ==============================
                // 🔥 tambahkan total jasa SETELAH DISKON
                // ==============================
                if (typeof totalJasaFinal !== "undefined" && totalJasaFinal > 0) {
                    grandTotal += totalJasaFinal;
                } else if (totalJasa) {
                    // jika belum ada diskon
                    grandTotal += totalJasa;
                }
                // ==============================

                // tampilkan total
                let totalCell = document.querySelector("#grandTotal");
                if (totalCell) {
                    totalCell.innerText = "Rp " + grandTotal.toLocaleString("id-ID");
                    itemTotalEl.innerText = item + " item";
                }
            }

            document.addEventListener("keydown", function(event) {
                if (event.key === "F8") {
                    event.preventDefault();
                    event.stopPropagation();

                    if (!form) return;

                    // ambil grand total dari grandTotalEl
                    let grandTotal = parseInt(grandTotalEl.innerText.replace(/[^0-9]/g, "")) || 0;
                    if (grandTotal <= 0) {
                        Swal.fire({
                            title: "Belum ada transaksi!",
                            text: "Silakan tambahkan produk terlebih dahulu",
                            icon: "warning",
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    // modal input uang customer
                    Swal.fire({
                        title: 'Input Uang Customer',
                        html: `Grand Total: <b>Rp ${grandTotal.toLocaleString()}</b><br>
                                <input type="text" id="swal-uang-customer" class="swal2-input" placeholder="Masukkan nominal">`,
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Batal',
                        didOpen: () => {
                            const input = document.getElementById('swal-uang-customer');
                            // fokus ke input
                            input.focus();

                            // event listener format Rupiah saat mengetik
                            input.addEventListener('input', (e) => {
                                let value = e.target.value.replace(/[^0-9]/g,
                                    ""); // hapus semua selain angka
                                if (value === "") {
                                    e.target.value = "";
                                    return;
                                }
                                // format ke Rp
                                e.target.value = parseInt(value).toLocaleString(
                                    'id-ID');
                            });
                        },
                        preConfirm: () => {
                            const input = document.getElementById('swal-uang-customer');
                            let uangCustomer = parseInt(input.value.replace(/[^0-9]/g, ""));
                            if (isNaN(uangCustomer) || uangCustomer <= 0) {
                                Swal.showValidationMessage("Masukkan nominal yang valid!");
                            }
                            return uangCustomer;
                        }
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        let uangCustomer = result.value;
                        let kembalian = uangCustomer - grandTotal;

                        if (kembalian < 0) {
                            Swal.fire({
                                title: "Uang Kurang!",
                                html: `Grand Total: <b>Rp ${grandTotal.toLocaleString()}</b><br>` +
                                    `Uang Customer: <b>Rp ${uangCustomer.toLocaleString()}</b><br>` +
                                    `Kurang: <b>Rp ${Math.abs(kembalian).toLocaleString()}</b>`,
                                icon: "warning",
                                confirmButtonText: "OK"
                            });
                            return; // hentikan submit
                        }

                        // buat FormData dari form
                        let formData = new FormData(form);
                        const tbody = cartTable.querySelector("tbody");
                        tbody.querySelectorAll("tr").forEach(row => {
                            let uuid = row.getAttribute("data-uuid");
                            let qty = parseInt(row.querySelector(".qty")?.innerText) || 0;
                            let jumlah = row.querySelector(".jumlah")?.innerText || "0";
                            jumlah = jumlah.replace(/[^0-9]/g, "");
                            formData.append("uuid_produk[]", uuid);
                            formData.append("qty[]", qty);
                            formData.append("total_harga[]", jumlah);

                            formDataCancel.append("uuid_produk[]", uuid);
                            formDataCancel.append("qty[]", qty);
                            formDataCancel.append("total_harga[]", jumlah);
                        });

                        formData.append("discount", jasaDiscount);

                        // tambahkan info pembayaran
                        formData.append("uang_customer", uangCustomer);
                        formData.append("kembalian", kembalian);

                        // tampilkan loading
                        // Tampilkan kembalian dulu
                        Swal.fire({
                            title: `Kembalian: Rp ${kembalian.toLocaleString()}`,
                            icon: "success",
                            showConfirmButton: false,
                            timer: 3500, // tampil selama 2.5 detik
                            timerProgressBar: true,
                        }).then(() => {
                            // Setelah popup kembalian selesai, lanjut ke proses simpan
                            Swal.fire({
                                title: "Menyimpan Transaksi...",
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });

                            // kirim ke backend
                            fetch("/kasir/penjualan-store", {
                                    method: "POST",
                                    body: formData,
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector(
                                            'meta[name="csrf-token"]').getAttribute(
                                            "content")
                                    }
                                })
                                .then(res => res.json())
                                .then(res => {
                                    Swal.close();
                                    if (res.status === "success") {
                                        Swal.fire({
                                            title: "Transaksi Berhasil ✅",
                                            text: "Apakah Anda ingin mencetak struk?",
                                            icon: "success",
                                            showCancelButton: true,
                                            confirmButtonText: "Cetak Struk",
                                            cancelButtonText: "Tidak",
                                            reverseButtons: true
                                        }).then(result => {
                                            if (result.isConfirmed) {
                                                strukData = {
                                                    outlet_nama: "BENGKEL MOBIL MMM",
                                                    outlet_alamat: "Jl. Tun Abdul Razak",
                                                    outlet_telp: "082194581659 / 082190461884",
                                                    no_bukti: res
                                                        .data
                                                        .no_bukti,
                                                    tanggal: res
                                                        .data
                                                        .tanggal,
                                                    kasir: res
                                                        .data
                                                        .kasir,
                                                    pembayaran: res
                                                        .data
                                                        .pembayaran,
                                                    discount: res
                                                        .data
                                                        .discount,
                                                    items: res
                                                        .data
                                                        .items
                                                        .map(
                                                            i =>
                                                            ({
                                                                nama: i
                                                                    .nama,
                                                                qty: Number(
                                                                    i
                                                                    .qty
                                                                ),
                                                                harga: Number(
                                                                    i
                                                                    .harga
                                                                ),
                                                                subtotal: Number(
                                                                    i
                                                                    .subtotal
                                                                )
                                                            })
                                                        ),
                                                    jasa: res
                                                        .data
                                                        .jasa
                                                        .map(
                                                            i =>
                                                            ({
                                                                nama: i
                                                                    .nama,
                                                                qty: i
                                                                    .qty,
                                                                subtotal: Number(
                                                                    i
                                                                    .subtotal
                                                                ),
                                                                harga: Number(
                                                                    i
                                                                    .harga
                                                                ),
                                                            })
                                                        ),
                                                    totalJasa: Number(
                                                        totalJasa
                                                    ),
                                                    totalItem: Number(
                                                        res
                                                        .data
                                                        .totalItem
                                                    ),
                                                    grandTotal: Number(
                                                        res
                                                        .data
                                                        .grandTotal
                                                    ),
                                                    customer: res
                                                        .data
                                                        .customer,
                                                };
                                                cetakStruk(strukData);
                                            }
                                            resetKasir();
                                            loadJasa();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Gagal!",
                                            text: res.message,
                                            icon: "error",
                                            confirmButtonText: "OK"
                                        });
                                        resetKasir();
                                    }
                                })
                                .catch(err => {
                                    Swal.close();
                                    console.error("❌ Error simpan transaksi:", err);
                                    Swal.fire({
                                        title: "Error!",
                                        text: "Terjadi kesalahan server",
                                        icon: "error",
                                        confirmButtonText: "OK"
                                    });
                                });
                        });
                    });
                }
            });

            async function loadPaket() {
                try {
                    const res = await fetch('/kasir/get-paket');
                    const data = await res.json();

                    const tbody = document.querySelector("#paketTableModal tbody");
                    tbody.innerHTML = "";

                    if (data.status === false) {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-center">${data.message}</td></tr>`;
                        return;
                    }

                    if (!data.paket || data.paket.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-center">Belum ada Paket</td></tr>`;
                    } else {
                        data.paket.forEach(p => {
                            const tr = document.createElement("tr");
                            tr.innerHTML =
                                `<td class="text-primary fw-bold cursor-pointer">${p.nama_paket}</td>`;

                            tr.querySelector("td").addEventListener("click", async () => {
                                try {
                                    const detailRes = await fetch(
                                        `/kasir/detail-paket/${p.uuid}`);
                                    const res = await detailRes.json();

                                    if (res.status === "success") {
                                        const grandTotal = Number(res.data.grandTotal);
                                        const grandTotalJasa = grandTotal + totalJasa;

                                        const itemsHtml = res.data.items.map(i => `
                                        <tr>
                                            <td>${i.nama}</td>
                                            <td>1</td>
                                        </tr>
                                    `).join("");

                                        const detailHtml = `
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Nama</th>
                                                        <th>Qty</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${itemsHtml}
                                                    <tr>
                                                        <td class="text-end fw-bold">Grand Total</td>
                                                        <td>Rp ${grandTotal.toLocaleString()}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    `;

                                        // === Step 1: Detail paket ===
                                        Swal.fire({
                                            title: `Detail - ${res.data.nama_paket}`,
                                            html: detailHtml,
                                            width: 600,
                                            showCancelButton: true,
                                            confirmButtonText: "Lanjut",
                                            cancelButtonText: "Batal"
                                        }).then(result => {
                                            if (!result.isConfirmed) return;

                                            // === Step 2: Input uang customer ===
                                            Swal.fire({
                                                title: 'Input Uang Customer',
                                                html: `
                                                    Grand Total: <b>Rp ${grandTotalJasa.toLocaleString()}</b><br>
                                                    <input type="text" id="swal-uang-customer-paket" class="swal2-input" placeholder="Masukkan nominal">
                                                `,
                                                showCancelButton: true,
                                                confirmButtonText: 'Simpan',
                                                cancelButtonText: 'Batal',
                                                didOpen: () => {
                                                    const input =
                                                        document
                                                        .getElementById(
                                                            'swal-uang-customer-paket'
                                                        );
                                                    if (input) {
                                                        input.focus();
                                                        input
                                                            .addEventListener(
                                                                'input',
                                                                (e) => {
                                                                    let value =
                                                                        e
                                                                        .target
                                                                        .value
                                                                        .replace(
                                                                            /[^0-9]/g,
                                                                            ""
                                                                        );
                                                                    e.target
                                                                        .value =
                                                                        value ?
                                                                        new Intl
                                                                        .NumberFormat(
                                                                            'id-ID'
                                                                        )
                                                                        .format(
                                                                            value
                                                                        ) :
                                                                        "";
                                                                });
                                                    }
                                                },
                                                preConfirm: () => {
                                                    const input =
                                                        document
                                                        .getElementById(
                                                            'swal-uang-customer-paket'
                                                        );
                                                    if (!input)
                                                        return false;

                                                    let rawValue = input
                                                        .value.replace(
                                                            /[^0-9]/g,
                                                            "");
                                                    let uangCustomer =
                                                        parseInt(
                                                            rawValue);

                                                    if (isNaN(
                                                            uangCustomer
                                                        ) ||
                                                        uangCustomer <=
                                                        0) {
                                                        Swal.showValidationMessage(
                                                            "Masukkan nominal yang valid!"
                                                        );
                                                        return false;
                                                    }
                                                    return uangCustomer;
                                                }
                                            }).then(result2 => {
                                                if (!result2.isConfirmed)
                                                    return;

                                                let uangCustomer = result2
                                                    .value;
                                                let kembalian =
                                                    uangCustomer -
                                                    grandTotalJasa;

                                                if (kembalian < 0) {
                                                    Swal.fire({
                                                        title: "Uang Kurang!",
                                                        html: `
                                            Grand Total: <b>Rp ${grandTotalJasa.toLocaleString()}</b><br>
                                            Uang Customer: <b>Rp ${uangCustomer.toLocaleString()}</b><br>
                                            Kurang: <b>Rp ${Math.abs(kembalian).toLocaleString()}</b>
                                        `,
                                                        icon: "warning",
                                                        confirmButtonText: "OK"
                                                    });
                                                    return;
                                                }

                                                // === Simpan transaksi ===
                                                let formData =
                                                    new FormData(form);
                                                formData.append(
                                                    "uuid_paket", p.uuid
                                                );
                                                formData.append(
                                                    "total_harga",
                                                    grandTotal
                                                );
                                                formData.append(
                                                    "uang_customer",
                                                    uangCustomer);
                                                formData.append("kembalian",
                                                    kembalian);

                                                Swal.fire({
                                                    title: `Kembalian: Rp ${kembalian.toLocaleString()}`,
                                                    text: "Menyimpan Transaksi...",
                                                    allowOutsideClick: false,
                                                    didOpen: () =>
                                                        Swal
                                                        .showLoading()
                                                });

                                                fetch("/kasir/penjualan-store", {
                                                        method: "POST",
                                                        body: formData,
                                                        headers: {
                                                            "X-CSRF-TOKEN": document
                                                                .querySelector(
                                                                    'meta[name="csrf-token"]'
                                                                )
                                                                .getAttribute(
                                                                    "content"
                                                                )
                                                        }
                                                    })
                                                    .then(res => res.json())
                                                    .then(res => {
                                                        Swal.close();
                                                        if (res
                                                            .status ===
                                                            "success") {
                                                            Swal.fire({
                                                                title: "Transaksi Berhasil ✅",
                                                                text: "Apakah Anda ingin mencetak struk?",
                                                                icon: "success",
                                                                showCancelButton: true,
                                                                confirmButtonText: "Cetak Struk",
                                                                cancelButtonText: "Tidak",
                                                                reverseButtons: true
                                                            }).then(
                                                                result => {
                                                                    if (result
                                                                        .isConfirmed
                                                                    ) {
                                                                        const
                                                                            strukData = {
                                                                                outlet_nama: "BENGKEL MOBIL MMM",
                                                                                outlet_alamat: "Jl. Tun Abdul Razak",
                                                                                outlet_telp: "082194581659 / 082190461884",
                                                                                no_bukti: res
                                                                                    .data
                                                                                    .no_bukti,
                                                                                tanggal: res
                                                                                    .data
                                                                                    .tanggal,
                                                                                kasir: res
                                                                                    .data
                                                                                    .kasir,
                                                                                pembayaran: res
                                                                                    .data
                                                                                    .pembayaran,
                                                                                discount: res
                                                                                    .data
                                                                                    .discount,
                                                                                items: res
                                                                                    .data
                                                                                    .items
                                                                                    .map(
                                                                                        i =>
                                                                                        ({
                                                                                            nama: i
                                                                                                .nama,
                                                                                            qty: Number(
                                                                                                i
                                                                                                .qty
                                                                                            ),
                                                                                            harga: Number(
                                                                                                i
                                                                                                .harga
                                                                                            ),
                                                                                            subtotal: Number(
                                                                                                i
                                                                                                .subtotal
                                                                                            )
                                                                                        })
                                                                                    ),
                                                                                jasa: res
                                                                                    .data
                                                                                    .jasa
                                                                                    .map(
                                                                                        i =>
                                                                                        ({
                                                                                            nama: i
                                                                                                .nama,
                                                                                            qty: i
                                                                                                .qty,
                                                                                            subtotal: Number(
                                                                                                i
                                                                                                .subtotal
                                                                                            ),
                                                                                            harga: Number(
                                                                                                i
                                                                                                .harga
                                                                                            ),
                                                                                        })
                                                                                    ),
                                                                                totalJasa: Number(
                                                                                    totalJasa
                                                                                ),
                                                                                totalItem: Number(
                                                                                    res
                                                                                    .data
                                                                                    .totalItem
                                                                                ),
                                                                                grandTotal: Number(
                                                                                    res
                                                                                    .data
                                                                                    .grandTotal
                                                                                ),
                                                                                customer: res
                                                                                    .data
                                                                                    .customer,
                                                                            };
                                                                        cetakStruk
                                                                            (
                                                                                strukData
                                                                            );
                                                                    }
                                                                    const
                                                                        modal =
                                                                        bootstrap
                                                                        .Modal
                                                                        .getOrCreateInstance(
                                                                            modalPaket
                                                                        );
                                                                    modal
                                                                        .hide();
                                                                    resetKasir
                                                                        ();
                                                                    loadJasa
                                                                        ();
                                                                });
                                                        } else {
                                                            Swal.fire({
                                                                title: "Gagal!",
                                                                text: res
                                                                    .message,
                                                                icon: "error",
                                                                confirmButtonText: "OK"
                                                            });
                                                            resetKasir
                                                                ();
                                                        }
                                                    })
                                                    .catch(err => {
                                                        Swal.close();
                                                        console.error(
                                                            "❌ Error simpan transaksi:",
                                                            err);
                                                        Swal.fire({
                                                            title: "Error!",
                                                            text: "Terjadi kesalahan server",
                                                            icon: "error",
                                                            confirmButtonText: "OK"
                                                        });
                                                    });
                                            });
                                        });
                                    }
                                } catch (err) {
                                    console.error("❌ Gagal ambil detail paket:", err);
                                }
                            });

                            tbody.appendChild(tr);
                        });
                    }
                } catch (err) {
                    console.error("Gagal ambil paket hemat:", err);
                }
            }

            document.addEventListener("keydown", (e) => {
                if (e.key === "F11") {
                    e.preventDefault();

                    // pakai opsi focus:false biar SweetAlert bisa ambil fokus
                    const modal = bootstrap.Modal.getOrCreateInstance(modalPaket, {
                        backdrop: 'static',
                        keyboard: false,
                        focus: false
                    });

                    if (modalPaket.classList.contains("show")) {
                        modal.hide();
                    } else {
                        loadPaket();
                        modal.show();
                    }
                }
            });

            function cetakStruk(data) {
                let jsonString = encodeURIComponent(JSON.stringify(data));
                window.open(`/kasir/print-struk?data=${jsonString}`, "_blank");
            }

            // ------------------
            // Fungsi Reset Kasir
            // ------------------
            function resetKasir() {
                // kosongkan hanya tbody (isi tabel)
                const tbody = cartTable.querySelector("tbody");
                if (tbody) {
                    tbody.innerHTML = "";
                }

                // reset total & item
                grandTotalEl.innerText = "Rp 0";
                itemTotalEl.innerText = "0 item";

                // kosongkan box produk terpilih
                const produkTerpilihDiv = document.querySelector(
                    ".produk-terpilih");
                if (produkTerpilihDiv) {
                    produkTerpilihDiv.innerHTML = "";
                }


                // reset scan input
                // scanInput.value = "";
                // scanInput.focus();

                // ✅ reset jasa
                selectedJasa = []; // kosongkan array jasa
                totalJasa = 0; // reset total jasa
                jasaDiscount = "0";
                totalJasaFinal = 0;
                const listJasa = document.getElementById("list-jasa");
                if (listJasa) {
                    listJasa.innerHTML = ""; // kosongkan tampilan jasa
                    listJasa.classList.add("d-none"); // sembunyikan kembali
                }

                // hapus semua hidden input uuid_jasa[]
                document.querySelectorAll("input[name='uuid_jasa[]']").forEach(el => el.remove());

                // kalau ada checkbox jasa, reset centangnya
                const checkJasa = document.getElementById("check-jasa");
                if (checkJasa) {
                    checkJasa.checked = false;
                }

                // 🔹 Reset seluruh input di form kasir
                const form = document.getElementById("form-kasir");
                if (form) {
                    form.reset(); // ini akan reset semua input, select, checkbox, dll
                }

                keepFocus();
            }

            async function loadStok() {
                try {
                    const res = await fetch('/kasir/get-stock');
                    const data = await res.json();

                    const tbody = document.querySelector("#produkTable tbody");
                    tbody.innerHTML = "";

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="text-center">Belum ada produk</td></tr>`;
                    } else {
                        data.forEach(p => {
                            // buat elemen row
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${p.kode}</td>
                                <td>${p.nama_barang}</td>
                                <td>${p.total_stok} ${p.satuan}</td>
                            `;

                            tbody.appendChild(tr);
                        });

                        // row no-data
                        const noDataRow = document.createElement("tr");
                        noDataRow.id = "no-data";
                        noDataRow.classList.add("d-none");
                        noDataRow.innerHTML =
                            `<td colspan="3" class="text-center">Tidak ada produk ditemukan</td>`;
                        tbody.appendChild(noDataRow);
                    }
                } catch (err) {
                    console.error("Gagal ambil stok:", err);
                }
            }

            async function loadProduk() {
                try {
                    const res = await fetch('/kasir/get-stock');
                    const data = await res.json();

                    const tbody = document.querySelector("#produkTableModal tbody");
                    tbody.innerHTML = "";

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="2" class="text-center">Belum ada produk</td></tr>`;
                    } else {
                        data.forEach(p => {
                            // buat elemen row
                            const tr = document.createElement("tr");
                            tr.innerHTML = `
                                <td>${p.kode}</td>
                                <td>${p.nama_barang}</td>
                            `;

                            // event klik row
                            tr.addEventListener("click", () => {
                                let qty = 1; // default klik = tambah 1
                                tambahProduk(p.kode, qty);

                                // highlight row
                                tr.classList.add("tr-highlight");
                                setTimeout(() => {
                                    tr.classList.remove("tr-highlight");
                                }, 800);

                                // tutup modal setelah klik
                                const modalEl = document.getElementById(
                                    "modalProduk"); // ganti ID sesuai modal kamu
                                if (modalEl) {
                                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                }
                            });

                            tbody.appendChild(tr);
                        });

                        // row no-data
                        const noDataRow = document.createElement("tr");
                        noDataRow.id = "no-data-modal";
                        noDataRow.classList.add("d-none");
                        noDataRow.innerHTML =
                            `<td colspan="2" class="text-center">Tidak ada produk ditemukan</td>`;
                        tbody.appendChild(noDataRow);
                    }
                } catch (err) {
                    console.error("Gagal ambil stok:", err);
                }
            }

            async function loadPenjualanHariIni() {
                try {
                    const res = await fetch('/kasir/get-penjualan');
                    const data = await res.json();

                    const tbody = document.querySelector("#cetakUlangTableModal tbody");
                    tbody.innerHTML = "";

                    if (data.status === false) {
                        // Sudah closing atau error
                        const tr = document.createElement("tr");
                        tr.innerHTML = `<td colspan="1" class="text-center">${data.message}</td>`;
                        tbody.appendChild(tr);
                        return;
                    }

                    if (!data.penjualans || data.penjualans.length === 0) {
                        // Tidak ada penjualan
                        tbody.innerHTML = `
                            <tr><td colspan="1" class="text-center">Belum ada Riwayat Struk</td></tr>
                        `;
                    } else {
                        // Loop data penjualan
                        data.penjualans.forEach(p => {
                            const tr = document.createElement("tr");
                            tr.innerHTML =
                                `<td class="text-primary fw-bold cursor-pointer">${p.no_bukti}</td>`;

                            // klik nomor bukti untuk detail
                            tr.querySelector("td").addEventListener("click", async () => {
                                try {
                                    const detailRes = await fetch(
                                        `/kasir/get-penjualan-detail/${p.uuid}`);
                                    const res = await detailRes.json();

                                    if (res.status === "success") {

                                        // =============================
                                        // HITUNG TOTAL PRODUK, JASA, DISKON
                                        // =============================
                                        const items = res.data.items ?? [];
                                        const totalProduk = items.reduce((sum, i) => sum +
                                            Number(i.subtotal), 0);

                                        let totalJasa = Number(res.data.totalJasa ?? 0);
                                        let discount = res.data.discount;

                                        let totalJasaAfterDiscount = totalJasa;

                                        if (discount) {
                                            if (String(discount).includes("%")) {
                                                // Diskon persen
                                                let angka = parseFloat(discount.replace("%",
                                                    ""));
                                                let potongan = (totalJasa * angka) / 100;
                                                totalJasaAfterDiscount = totalJasa -
                                                    potongan;
                                            } else if (!isNaN(discount)) {
                                                // Diskon nominal
                                                totalJasaAfterDiscount = Math.max(0,
                                                    totalJasa - Number(discount));
                                            }
                                        }

                                        const finalGrandTotal = totalProduk +
                                            totalJasaAfterDiscount;

                                        // =============================
                                        // TAMPILKAN TABEL ITEMS
                                        // =============================
                                        const itemsHtml = items.map(i => `
                <tr>
                    <td>${i.nama}</td>
                    <td>${i.qty}</td>
                    <td>${Number(i.harga).toLocaleString()}</td>
                    <td>${Number(i.subtotal).toLocaleString()}</td>
                </tr>
            `).join("");

                                        // =============================
                                        // APA YANG DITAMPILKAN DI DISCOUNT
                                        // =============================
                                        let discountDisplay = "-";
                                        if (discount) {
                                            if (String(discount).includes("%")) {
                                                discountDisplay = discount;
                                            } else if (!isNaN(discount)) {
                                                discountDisplay = "Rp " + Number(discount)
                                                    .toLocaleString();
                                            } else {
                                                discountDisplay = discount;
                                            }
                                        }

                                        // =============================
                                        // HTML FINAL
                                        // =============================
                                        const detailHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHtml}

                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total Jasa</td>
                                <td>${totalJasa.toLocaleString()}</td>
                            </tr>

                            <tr>
                                <td colspan="3" class="text-end fw-bold">Discount (Jasa)</td>
                                <td>${discountDisplay}</td>
                            </tr>

                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                <td>${finalGrandTotal.toLocaleString()}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

                                        Swal.fire({
                                            title: `Detail Struk - ${res.data.no_bukti}`,
                                            html: detailHtml,
                                            width: 600,
                                            showCancelButton: true,
                                            confirmButtonText: "🖨 Cetak Struk",
                                            cancelButtonText: "Tutup"
                                        }).then(result => {
                                            if (result.isConfirmed) {
                                                const totalJasa = res.data
                                                    .totalJasa ?? 0;
                                                strukData = {
                                                    outlet_nama: "BENGKEL MOBIL MMM",
                                                    outlet_alamat: "Jl. Tun Abdul Razak",
                                                    outlet_telp: "082194581659 / 082190461884",
                                                    no_bukti: res
                                                        .data
                                                        .no_bukti,
                                                    tanggal: res
                                                        .data
                                                        .tanggal,
                                                    kasir: res
                                                        .data
                                                        .kasir,
                                                    pembayaran: res
                                                        .data
                                                        .pembayaran,
                                                    discount: res
                                                        .data
                                                        .discount,
                                                    items: res
                                                        .data
                                                        .items
                                                        .map(
                                                            i =>
                                                            ({
                                                                nama: i
                                                                    .nama,
                                                                qty: Number(
                                                                    i
                                                                    .qty
                                                                ),
                                                                harga: Number(
                                                                    i
                                                                    .harga
                                                                ),
                                                                subtotal: Number(
                                                                    i
                                                                    .subtotal
                                                                )
                                                            })
                                                        ),
                                                    jasa: res
                                                        .data
                                                        .jasa
                                                        .map(
                                                            i =>
                                                            ({
                                                                nama: i
                                                                    .nama,
                                                                qty: i
                                                                    .qty,
                                                                subtotal: Number(
                                                                    i
                                                                    .subtotal
                                                                ),
                                                                harga: Number(
                                                                    i
                                                                    .harga
                                                                ),
                                                            })
                                                        ),
                                                    totalJasa: Number(
                                                        totalJasa
                                                    ),
                                                    totalItem: Number(
                                                        res
                                                        .data
                                                        .totalItem
                                                    ),
                                                    grandTotal: Number(
                                                        res
                                                        .data
                                                        .grandTotal
                                                    ),
                                                    customer: res
                                                        .data
                                                        .customer,
                                                };

                                                cetakStruk(strukData);
                                            }
                                        });
                                    }
                                } catch (err) {
                                    console.error("❌ Gagal ambil detail penjualan:", err);
                                }
                            });

                            tbody.appendChild(tr);
                        });
                    }

                    // row no-data tersembunyi
                    const noDataRow = document.createElement("tr");
                    noDataRow.id = "no-data-modal";
                    noDataRow.classList.add("d-none");
                    noDataRow.innerHTML = `<td colspan="3" class="text-center">Tidak ada produk ditemukan</td>`;
                    tbody.appendChild(noDataRow);

                } catch (err) {
                    console.error("Gagal ambil riwayat struk:", err);
                }
            }

            // ---------------------------
            // Search filter
            // ---------------------------
            document.getElementById("searchInput").addEventListener("keyup", function() {
                let value = this.value.toLowerCase();
                let rows = document.querySelectorAll("#produkTable tbody tr:not(#no-data)");
                let noData = document.getElementById("no-data");

                let visibleCount = 0;

                rows.forEach(function(row) {
                    let kode = row.cells[0]?.textContent.toLowerCase() || "";
                    let nama = row.cells[1]?.textContent.toLowerCase() || "";

                    if (kode.includes(value) || nama.includes(value)) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });

                if (noData) {
                    if (visibleCount === 0) {
                        noData.classList.remove("d-none");
                    } else {
                        noData.classList.add("d-none");
                    }
                }
            });

            // ---------------------------
            // Search filter
            // ---------------------------
            document.getElementById("searchInputModal").addEventListener("keyup", function() {
                let value = this.value.toLowerCase();
                let rows = document.querySelectorAll("#produkTableModal tbody tr:not(#no-data-modal)");
                let noData = document.getElementById("no-data-modal");

                let visibleCount = 0;

                rows.forEach(function(row) {
                    let kode = row.cells[0]?.textContent.toLowerCase() || "";
                    let nama = row.cells[1]?.textContent.toLowerCase() || "";

                    if (kode.includes(value) || nama.includes(value)) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });

                if (noData) {
                    if (visibleCount === 0) {
                        noData.classList.remove("d-none");
                    } else {
                        noData.classList.add("d-none");
                    }
                }
            });

            document.addEventListener("keydown", function(event) {
                if (event.key === "F9") {
                    event.preventDefault();

                    const tbody = cartTable.querySelector("tbody");
                    const rows = tbody.querySelectorAll("tr");

                    if (rows.length === 0) {
                        return; // stop eksekusi
                    }

                    let formDataCancel = new FormData();

                    rows.forEach(row => {
                        let uuid = row.getAttribute("data-uuid");
                        let qty = parseInt(row.querySelector(".qty")?.innerText) || 0;
                        let jumlah = row.querySelector(".jumlah")?.innerText || "0";
                        jumlah = jumlah.replace(/[^0-9]/g, "");

                        formDataCancel.append("uuid_produk[]", uuid);
                        formDataCancel.append("qty[]", qty);
                        formDataCancel.append("total_harga[]", jumlah);
                    });

                    fetch("/kasir/cancel-pejualan", {
                            method: "POST",
                            body: formDataCancel,
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content")
                            }
                        })
                        .then(async res => {
                            if (!res.ok) {
                                throw new Error("HTTP status " + res.status);
                            }
                            return await res.json();
                        })
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    title: "Berhasil",
                                    text: res.message,
                                    icon: "success",
                                    confirmButtonText: "OK"
                                });
                                resetKasir();
                            } else {
                                Swal.fire({
                                    title: "Gagal",
                                    text: res.message || "Cancel penjualan gagal",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        })
                        .catch(err => {
                            console.error("Fetch error:", err); // debug di console
                            Swal.fire({
                                title: "Error!",
                                text: "Terjadi kesalahan server: " + err.message,
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        });

                }
            });

            document.getElementById("btn-closing").addEventListener("click", function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Input Uang Fisik',
                    input: 'text', // ganti dari number ke text
                    inputLabel: 'Masukkan jumlah uang fisik di kasir',
                    inputPlaceholder: 'Contoh: 1.500.000',
                    showCancelButton: true,
                    confirmButtonText: 'Simpan Closing',
                    cancelButtonText: 'Batal',
                    willOpen: () => {
                        window.__swalOpen = true;
                    },
                    didOpen: () => {
                        const input = Swal.getInput();
                        if (input) {
                            input.focus();
                            input.select();

                            // Format ke rupiah saat mengetik
                            input.addEventListener("input", () => {
                                let value = input.value.replace(/\D/g,
                                    ""); // hanya angka
                                if (value) {
                                    input.value = new Intl.NumberFormat("id-ID").format(
                                        value);
                                } else {
                                    input.value = "";
                                }
                            });
                        }
                    },
                    willClose: () => {
                        window.__swalOpen = false;
                    },
                    preConfirm: () => {
                        const input = Swal.getInput().value;
                        // Hapus titik/format → jadi angka murni
                        return input.replace(/\D/g, "");
                    }
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    if (result.isConfirmed) {
                        let data = {
                            total_fisik: result.value,
                            uuid_kasir_outlet: "{{ $kasir_login->uuid_user ?? '' }}"
                        };

                        fetch("{{ route('kasir.closing') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute("content")
                                },
                                body: JSON.stringify(data)
                            })
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === "success") {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Closing Berhasil',
                                        text: 'Data closing kasir sudah disimpan!',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        // window.open("/kasir/sumary-report/" + res.data
                                        //     .uuid, "_blank");
                                        Swal.fire({
                                            title: "Berhasil",
                                            text: res.message,
                                            icon: "success",
                                            showConfirmButton: false,
                                            timer: 1500,
                                            timerProgressBar: true
                                        });
                                        localStorage.setItem("closing_done", "1");
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', res.message ??
                                        'Gagal menyimpan closing.', 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'Terjadi kesalahan koneksi.', 'error');
                            });
                    }
                });
            });

            document.getElementById("searchPlat").addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault(); // cegah form submit
                    const plat = this.value.trim();

                    if (plat === "") {
                        alert("Masukkan plat kendaraan terlebih dahulu!");
                        return;
                    }

                    // Kirim request ke route Laravel
                    fetch(`/kasir/get-costumer-byPlat?plat=${encodeURIComponent(plat)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success" && data.data) {
                                // isi inputan form
                                document.querySelector("input[name='nama']").value = data.data.nama ||
                                    "";
                                document.querySelector("input[name='alamat']").value = data.data
                                    .alamat || "";
                                document.querySelector("input[name='nomor']").value = data.data.nomor ||
                                    "";
                                document.querySelector("input[name='plat']").value = data.data.plat ||
                                    "";
                            } else {
                                Swal.fire({
                                    title: "Warning!",
                                    text: "Data pelanggan tidak ditemukan!",
                                    icon: "warning",
                                    confirmButtonText: "OK"
                                });
                            }
                        })
                        .catch(error => {
                            console.error("Terjadi kesalahan:", error);
                            alert("Gagal mengambil data dari server!");
                        });
                }
            });

        });
    </script>

</body>

</html>
