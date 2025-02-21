@extends('layouts.app')

@section('title')
    History Container
@endsection


@section('pages-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Monitoring</a></li>
                <li class="breadcrumb-item active">History Container</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p>
                </h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <h3><b>History Container</b></h3>


                    <div class="p-3">

                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Container : </label>
                            </div>
                            <div class="col-md-6">
                                <select name="NO_CONT" id="NO_CONT" class="form-control" style="width: 100%"></select>
                                <div class="invalid-feedback">Harap Masukan Nomor Container</div>
                                <input type="hidden" id="NO_CONT2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Vessel : </label>
                            </div>
                            <div class="col-md-3">
                                <input id="VESSEL" name="VESSEL" type="text" class="form-control" readonly />
                            </div>

                            <div class="col-md-3">
                                <input name="VOYAGE" id="VOYAGE" type="text" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">NO BOOKING : </label>
                            </div>
                            <div class="col-md-2">
                                <input id="NO_BOOKING" type="text" class="form-control" readonly />
                            </div>
                            <div class="col-md-1 py-2">
                                <label for="tb-fname">BP ID : </label>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" readonly name="NO_DOC" id="NO_DOC">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">SIZE/TYPE/STATUS</label>
                            </div>
                            <div class="col-md-2">
                                <input id="SIZE" type="text" class="form-control" readonly />
                            </div>
                            <div class="col-md-2">
                                <input id="TYPE" type="text" class="form-control" readonly />
                            </div>
                            <div class="col-md-2">
                                <input id="STATUS" type="text" class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Location</label>
                            </div>
                            <div class="col-md-4">
                                <input id="LOCATION" name="LOCATION" type="text" class="form-control" readonly />
                            </div>

                        </div>

                    </div>

                    <div class="history">
                        <div class="row justify-content-center">
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('HandilingtSection')"
                                    data-section="HandilingtSection">Handling</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('placementSection')"
                                    data-section="placementSection">Placement</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('strippingSection')"
                                    data-section="strippingSection">Stripping</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('stuffingSection')"
                                    data-section="stuffingSection">Stuffing</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('receivingSection')"
                                    data-section="receivingSection">Receiving</button>
                            </div>
                            <div class="col-auto mb-2">
                                <button type="button" class="btn btn-info" onclick="showSection('deliverySection')"
                                    data-section="deliverySection">Delivery</button>
                            </div>
                        </div>
                        <style>
                            .table-bordered {
                                border: 1px solid #dee2e6;
                            }
                        </style>
                        <div class="section-container p-3" style="border: 1px solid; margin-top:20px;border-radius:10px;">

                            <div class="table-responsive" id="HandilingtSection">
                                <h3>Handiling</h3>
                                <table class="display nowrap table data-table" cellspacing="0" width="100%"
                                    id="handlingTable">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>HANDLING</th>
                                            <th>NO REQ</th>
                                            <th>NAMA YARD</th>
                                            <th>NAMA USER</th>
                                            <th>TANGGAL</th>
                                        </tr>
                                    </thead>

                                </table>
                            </div>

                            <div class="table-responsive" id="placementSection" style="display: none;">
                                <h3>Placement</h3>
                                <table id="placementTable" class="display nowrap table data-table" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>TGL PLACEMENT</td>
                                            <th>NO_REQ_REC</td>
                                            <th>VIA</td>
                                            <th>NAMA YARD</td>
                                            <th>BLOK</td>
                                            <th>SLOT/ROW/TIER</td>
                                            <th>OPER NAME</td>
                                            <th>KETERANGAN</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Isi tabel akan diisi secara dinamis oleh DataTable -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Section for Stripping -->
                            <div class="table-responsive" id="strippingSection" style="display: none;">
                                <h3>Stripping</h3>
                                <table id="strippingTable" class="display nowrap table data-table" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>NO REQ</td>
                                            <th>PROFORMA</td>
                                            <th>FAKTUR</td>
                                            <th>LUNAS</td>
                                            <th>TGL REQUEST</td>
                                            <th>CONS</td>
                                            <th>DO</td>
                                            <th>BL</td>
                                            <th>PERP KE</td>
                                            <th>TIPE STRIP</td>
                                            <th>APPROVE</td>
                                            <th>APPROVE END</td>
                                            <th>START PNKN</td>
                                            <th>ACTIVE TO</td>
                                            <th>TGL REALISASI</td>
                                            <th>USER REAL</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Isi tabel akan diisi secara dinamis oleh DataTable -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Section for Stuffing -->
                            <div class="table-responsive" id="stuffingSection" style="display: none;">
                                <h3>Stuffing</h3>
                                <table id="stuffingTable" class="display nowrap table data-table" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>NO REQUEST</td>
                                            <th>PROFORMA</td>
                                            <th>FAKTUR</td>
                                            <th>ST NOTA</td>
                                            <th>LUNAS</td>
                                            <th>TGL REQ</td>
                                            <th>NO DO</td>
                                            <th>TGL APPROVE</td>
                                            <th>ACTIVE FROM</td>
                                            <th>ACTIVE TO</td>
                                            <th>STUF DR</td>
                                            <th>CONSIGNEE</td>
                                            <th>KAPAL</td>
                                            <th>TGL REALISASI</td>
                                            <th>USER REAL</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Section for Receiving -->
                            <div class="table-responsive" id="receivingSection" style="display: none;">
                                <h3>Receiving</h3>
                                <table id="receivingTable" class="display nowrap table data-table" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>NO REQUEST</td>
                                            <th>PROFORMA</td>
                                            <th>FAKTUR</td>
                                            <th>LUNAS</td>
                                            <th>TGL REQUEST</td>
                                            <th>CONS</td>
                                            <th>RECEIVING DARI</td>
                                            <th>AKTIF</td>
                                            <th>DO</td>
                                            <th>BL</td>
                                            <th>STAT_REQ</td>
                                            <th>PERALIHAN</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Isi tabel akan diisi secara dinamis oleh DataTable -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Section for Delivery -->
                            <div class="table-responsive" id="deliverySection" style="display: none;">
                                <h3>Delivery</h3>
                                <table id="deliveryTable" class="display nowrap table data-table" cellspacing="0"
                                    width="100%">
                                    <thead>
                                        <tr bgcolor="#1E88E5">
                                            <th>NO REQUEST</td>
                                            <th>PROFORMA</td>
                                            <th>FAKTUR</td>
                                            <th>LUNAS</td>
                                            <th>TGL REQUEST</td>
                                            <th>START PNKN</td>
                                            <th>ACTIVE TO</td>
                                            <th>PERP KE</td>
                                            <th>KOMODITI</td>
                                            <th>AKTIF</td>
                                            <th>HZ</td>
                                            <th>PERALIHAN</td>
                                            <th>DELIVERY KE</td>
                                            <th>JENIS REPO</td>
                                            <th>NAMA EMKL</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Isi tabel akan diisi secara dinamis oleh DataTable -->
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>


                    <!-- Section for Placement -->

                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@section('pages-js')
    <script>
        function showSection(sectionId) {
            // Sembunyikan semua section terlebih dahulu
            document.querySelectorAll('.section-container > div').forEach(function(section) {
                section.style.display = 'none';
            });

            // Tampilkan section yang sesuai dengan id yang diberikan
            document.getElementById(sectionId).style.display = 'block';

            // Menghapus kelas 'btn-primary' dari semua tombol
            document.querySelectorAll('.btn').forEach(function(btn) {
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-info');
            });

            // Menambahkan kelas 'btn-primary' ke tombol yang sesuai
            document.querySelector('button[data-section="' + sectionId + '"]').classList.remove('btn-info');
            document.querySelector('button[data-section="' + sectionId + '"]').classList.add('btn-warning');
        }

        function resetForm() {
            // Mengatur ulang nilai input menjadi kosong atau nilai awalnya
            $("#VESSEL").val('');
            $("#VOYAGE").val('');
            $("#NO_BOOKING").val('');
            $("#NO_DOC").val('');
            $("#SIZE").val('');
            $("#TYPE").val('');
            $("#STATUS").val('');
            $("#LOCATION").val('');
        }

        $(document).ready(function() {
            // Existing Select2 initialization
            $('#NO_CONT').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    url: '{!! route('uster.monitoring.listContainer') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.no_container + item.counter,
                                text: ' [ ' + item.counter + ' ] ' + item.no_container +
                                    ' ' + item.ie +
                                    ' ' + item.nm_kapal,
                                ...item
                            }))
                        };
                    }
                }
            });

            // Get the 'container' parameter from the URL
            const urlParams = new URLSearchParams(window.location.search);
            const containerParam = urlParams.get('container');

            if (containerParam) {
                // Remove any leading or trailing spaces
                const containerNumber = containerParam.trim();

                // Now make an AJAX call to get the container data
                $.ajax({
                    url: '{!! route('uster.monitoring.listContainer') !!}',
                    dataType: 'json',
                    data: {
                        q: containerNumber
                    },
                    success: function(response) {
                        // Process the data
                        const dataArray = Array.isArray(response) ? response : [response];
                        const processedData = dataArray.map((item) => ({
                            id: item.no_container + item.counter,
                            text: ' [ ' + item.counter + ' ] ' + item.no_container + ' ' +
                                item.ie + ' ' + item.nm_kapal,
                            ...item
                        }));

                        if (processedData.length > 0) {
                            // Find the matching container
                            var matchedItem = null;
                            for (var i = 0; i < processedData.length; i++) {
                                if (processedData[i].no_container === containerNumber) {
                                    matchedItem = processedData[i];
                                    break;
                                }
                            }

                            if (matchedItem) {
                                // Create a new option and append it to the select2 input
                                var newOption = new Option(matchedItem.text, matchedItem.id, true,
                                    true);
                                $('#NO_CONT').append(newOption).trigger('change');

                                // Trigger the 'select2:select' event with the data
                                $('#NO_CONT').trigger({
                                    type: 'select2:select',
                                    params: {
                                        data: matchedItem
                                    }
                                });
                            } else {
                                // No matching container found
                                Swal.fire('Container not found');
                            }
                        } else {
                            // No data found, show an error message
                            Swal.fire('Container not found');
                        }
                    },
                    error: function() {
                        // Handle error
                        Swal.fire('Error fetching container data');
                    }
                });
            }
        });



        $('#NO_CONT').on('select2:select', function(e) {

            resetForm();
            Swal.fire({
                title: 'Mendapatkan Data Container...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            var data = e.params.data;
            $("#SIZE").val(data.size_);
            $("#TYPE").val(data.type_);

            var booking = data.no_booking;
            var booking_ = data.no_booking;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var ajaxCount = 4; // Menyimpan jumlah permintaan Ajax yang sedang berjalan

            function checkAjaxComplete() {
                ajaxCount--; // Mengurangi jumlah permintaan Ajax yang sedang berjalan
                if (ajaxCount === 0) {
                    Swal.close(); // Menutup SweetAlert setelah semua permintaan Ajax selesai
                }
            }

            $.post("{{ route('uster.monitoring.getStatusContainer') }}", {
                no_cont: data.no_container,
                no_booking: data.no_booking
            }, function(datf) {
                $("#STATUS").val(datf);
                checkAjaxComplete(); // Memeriksa apakah semua permintaan Ajax selesai
            });

            $.post("{{ route('uster.monitoring.getLocation') }}", {
                no_cont: data.no_container
            }, function(datz) {
                $("#LOCATION").val(datz);
                checkAjaxComplete(); // Memeriksa apakah semua permintaan Ajax selesai
            });

            if (data.no_booking == "VESSEL_NOTHING") {
                var booking = '';
                $("#VESSEL").val('VESSEL_NOTHING');
            }


            if (booking == '') {
                $.post('{{ route('uster.monitoring.getBooking') }}', {
                    no_cont: data.no_container
                }, function(datea) {
                    checkAjaxComplete();
                    if (datea != '') {
                        booking = datea;
                        $.get("{{ route('uster.monitoring.ContainerVessel') }}?" +
                            data.no_container +
                            "&no_book=" + booking,
                            function(datax) {
                                checkAjaxComplete();
                                $("#VESSEL").val(datax.nm_kapal);
                                $("#VOYAGE").val(datax.voyage_in + "-" + datax.voyage_out);
                                $("#NO_BOOKING").val(datax.no_booking);
                                $("#BP_ID").val(datax.bp_id);
                            });
                    } else {
                        checkAjaxComplete();
                        booking = '';
                        $("#VESSEL").val('VESSEL_NOTHING');
                        $("#VOYAGE").val('');
                        $("#NO_BOOKING").val('');
                        $("#BP_ID").val('');
                    }
                });
            } else {
                checkAjaxComplete();
                $.get("{{ route('uster.monitoring.ContainerVessel') }}?" + data
                    .no_container +
                    "&no_book=" + booking,
                    function(datax) {
                        checkAjaxComplete();
                        $("#VESSEL").val(datax.nm_kapal);
                        $("#VOYAGE").val(datax.voyage_in + "-" + datax.voyage_out);
                        $("#BP_ID").val(datax.bp_id);
                    });

                $("#NO_BOOKING").val(data.no_booking);
            }


            var baseRoute = "{{ route('uster.monitoring.getDetail') }}"; // Assuming this is your base route


            var columnsHandling = [{
                    data: 'kegiatan',
                    name: 'kegiatan'
                },
                {
                    data: function(row) {
                        // Pilih nilai no_req_ yang tidak null
                        var noReqValues = [
                            row.no_req_del,
                            row.no_req_rbm,
                            row.no_req_rec,
                            row.no_req_rel,
                            row.no_req_stf,
                            row.no_req_str
                        ];
                        // Temukan nilai yang tidak null
                        var selectedNoReq = noReqValues.find(function(value) {
                            return value !== null;
                        });
                        // Kembalikan nilai yang ditemukan atau string kosong jika tidak ada
                        return selectedNoReq ? selectedNoReq : '';
                    },
                    name: 'no_req_combined' // Nama kolom baru
                },
                {
                    data: 'nama_yard',
                    name: 'nama_yard'
                },
                {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
                },
                {
                    data: 'tgl_update',
                    name: 'tgl_update'
                },

            ];

            var columnsPlacement = [{
                    data: 'tgl_update',
                    name: 'tgl_update'
                },
                {
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'via',
                    name: 'via'
                },
                {
                    data: 'nama_yard',
                    name: 'nama_yard'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: function(row) {
                        // Gabungkan nilai SLOT_, ROW_, dan TIER_ menjadi satu string
                        var combinedValue = '';
                        if (row.slot_ !== null) {
                            combinedValue += row.slot_ + ' / ';
                        }
                        if (row.row_ !== null) {
                            combinedValue += row.row_ + ' / ';
                        }
                        if (row.tier_ !== null) {
                            combinedValue += row.tier_;
                        }
                        return combinedValue;
                    },
                    name: 'slot_row_tier_combined' // Nama kolom baru
                },
                {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
                },
                {
                    data: 'keterangan',
                    name: 'keterangan'
                },
            ];

            var columnsDelivery = [{
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'no_nota',
                    name: 'no_nota'
                },
                {
                    data: 'no_faktur',
                    name: 'no_faktur'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
                },
                {
                    data: 'tgl_request',
                    name: 'tgl_request'
                },
                {
                    data: 'start_stack',
                    name: 'start_stack'
                },
                {
                    data: 'tgl_delivery',
                    name: 'tgl_delivery'
                },
                {
                    data: 'perp_ke',
                    name: 'perp_ke'
                },
                {
                    data: 'komoditi',
                    name: 'komoditi'
                },
                {
                    data: 'aktif',
                    name: 'aktif'
                },
                {
                    data: 'hz',
                    name: 'hz'
                },
                {
                    data: 'peralihan',
                    name: 'peralihan'
                },
                {
                    data: 'delivery_ke',
                    name: 'delivery_ke'
                },
                {
                    data: 'jn_repo',
                    name: 'jn_repo'
                },
                {
                    data: 'nama_emkl',
                    name: 'nama_emkl'
                },
            ];

            var columnsReceiving = [{
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'no_nota',
                    name: 'no_nota'
                },
                {
                    data: 'no_faktur',
                    name: 'no_faktur'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
                }, {
                    data: 'tgl_request',
                    name: 'tgl_request'
                },
                {
                    data: 'consignee',
                    name: 'consignee'
                },
                {
                    data: 'receiving_dari',
                    name: 'receiving_dari'
                },
                {
                    data: 'aktif',
                    name: 'aktif'
                },
                {
                    data: 'no_do',
                    name: 'no_do'
                },
                {
                    data: 'no_bl',
                    name: 'no_bl'
                },
                {
                    data: 'status_req',
                    name: 'status_req'
                },
                {
                    data: 'peralihan',
                    name: 'peralihan'
                },
            ];

            var columnsStuffing = [{
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'no_nota',
                    name: 'no_nota'
                },
                {
                    data: 'no_faktur',
                    name: 'no_faktur'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
                },
                {
                    data: 'tgl_request',
                    name: 'tgl_request'
                },
                {
                    data: 'no_do',
                    name: 'no_do'
                },
                {
                    data: 'tgl_approve',
                    name: 'tgl_approve'
                },
                {
                    data: 'start_stack',
                    name: 'start_stack'
                },
                {
                    data: 'start_perp_pnkn',
                    name: 'start_perp_pnkn'
                },
                {
                    data: 'asal_cont',
                    name: 'asal_cont'
                },
                {
                    data: 'nm_pbm',
                    name: 'nm_pbm'
                },
                {
                    data: 'nm_kapal',
                    name: 'nm_kapal'
                },
                {
                    data: 'tgl_realisasi',
                    name: 'tgl_realisasi'
                },
                {
                    data: 'nama_lkp',
                    name: 'nama_lkp'
                },
            ];

            var columnsStripping = [{
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'no_nota',
                    name: 'no_nota'
                },
                {
                    data: 'no_faktur',
                    name: 'no_faktur'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
                },
                {
                    data: 'tgl_request',
                    name: 'tgl_request'
                },
                {
                    data: 'nama_consignee',
                    name: 'nama_consignee'
                },
                {
                    data: 'no_do',
                    name: 'no_do'
                },
                {
                    data: 'no_bl',
                    name: 'no_bl'
                },
                {
                    data: 'perp_ke',
                    name: 'perp_ke'
                },
                {
                    data: 'type_stripping',
                    name: 'type_stripping'
                },
                {
                    data: 'tgl_approve',
                    name: 'tgl_approve'
                },
                {
                    data: 'tgl_app_selesai',
                    name: 'tgl_app_selesai'
                },
                {
                    data: function(row) {

                        if (row.status_req == 'PERP') {
                            return row.status_req
                        } else {
                            return row.tgl_mulai
                        }
                    },
                    name: 'start_pnkn' // Nama kolom baru
                },
                {
                    data: function(row) {

                        if (row.status_req == 'PERP') {
                            return row.end_stack_pnkn
                        } else {
                            return row.tgl_selesai
                        }
                    },
                    name: 'active_to' // Nama kolom baru
                },
                {
                    data: 'tgl_realisasi',
                    name: 'tgl_realisasi'
                },
                {
                    data: 'nama_lkp',
                    name: 'nama_lkp'
                },
            ];


            performAjaxRequest(baseRoute + "?ACT=handling&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#handlingTable', columnsHandling);

            performAjaxRequest(baseRoute + "?ACT=placement&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#placementTable', columnsPlacement);

            performAjaxRequest(baseRoute + "?ACT=delivery&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#deliveryTable', columnsDelivery);

            performAjaxRequest(baseRoute + "?ACT=receiving&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#receivingTable', columnsReceiving);

            performAjaxRequest(baseRoute + "?ACT=stuffing&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#stuffingTable', columnsStuffing);

            performAjaxRequest(baseRoute + "?ACT=stripping&NO_CONT=" + data.no_container + "&COUNTER=" + data
                .counter + "&NO_BOOK=" + data.no_booking, '#strippingTable', columnsStripping);


            return false;
        });

        $('#EMKL').on('select2:clear', function(e) {
            $form[0].reset()
        })

        function performAjaxRequest(url, tableId, columns) {
            // Hapus DataTable yang ada jika sudah ada sebelumnya
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            $.get(url, function(data) {
                // Buat DataTable baru untuk data yang diterima
                var dataTable = $(tableId).DataTable({
                    searching: false,
                    lengthChange: false,
                    pageLength: 20,
                    data: data,
                    columns: columns // Menggunakan struktur kolom yang ditentukan
                });
            });
        }
    </script>
@endsection
