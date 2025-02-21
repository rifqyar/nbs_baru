@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
@endsection


@section('pages-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item active">Stuffing Plan</li>
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
                                <label for="tb-fname">Periode Kegiatan : </label>
                            </div>
                            <div class="col-md-3">
                                <input id="TGL_AWAL" name="TGL_AWAL" type="date" class="form-control" />
                            </div>

                            <div class="col-md-3">
                                <input name="TGL_AKHIR" id="TGL_AKHIR" type="date" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Jenis Kegiatan : </label>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="kegiatan" id="kegiatan"
                                    style="margin:0px; padding-left:2px" onchange="showUser(this.value)">
                                    <option value=""> -- Pilih -- </option>
                                    <!-- <option value="receiving_tpk"> RECEIVING dari TPK </option>	 -->
                                    <option value="receiving_luar"> RECEIVING MTY</option>
                                    <option value="stripping_tpk"> STRIPPING </option>
                                    <!-- <option value="stripping_depo"> STRIPPING dari DEPO </option> -->
                                    <option value="stuffing_depo"> STUFFING</option>
                                    <!-- <option value="stuffing_tpk"> STUFFING dari TPK </option> -->
                                    <option value="delivery_tpk_mty"> REPO MUAT EMPTY</option>
                                    <option value="delivery_luar">DELIVERY ke LUAR / SP2</option>
                                    <!-- <option value="relokasi"> RELOKASI </option>	 -->
                                </select>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">SIZE/TYPE/STATUS</label>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="STATUS" id="STATUS"
                                    style="margin:0px; padding-left:2px"></select>
                            </div>

                        </div>


                    </div>

                    <div class="history">
                        <!-- Tombol untuk setiap proses -->
                        <button type="button" class="btn btn-info mx-2" onclick="generateButton()">Generate Report</button>
                        <button type="button" class="btn btn-info mx-2" onclick="generateExcel()"
                            data-section="HandilingtSection">Generate Excel</button>
                        <br>
                        <br>
                        <div class="table-responsive">
                            <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                                id="service-table">
                                <thead>
                                    <tr>

                                    </tr>
                                </thead>
                            </table>
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
        function showUser(kegiatan) {
            //alert(kegiatan);
            if (kegiatan == "receiving_tpk") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST RECEIVING&nbsp;&nbsp;</option><option value="gati">BORDER GATE IN&nbsp;&nbsp;</option><option value="plac">PLACEMENT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "receiving_luar") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST RECEIVING&nbsp;&nbsp;</option><option value="gati">GATE IN&nbsp;&nbsp;</option><option value="plac">PLACEMENT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "stripping_tpk") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="tgl_app">PLANNING REQ STRIPPING&nbsp;&nbsp;</option><option value="req">REQUEST STRIPPING / APPROVE&nbsp;&nbsp;</option><option value="gati">BORDER GATE IN&nbsp;&nbsp;</option><option value="plac">PLACEMENT&nbsp;&nbsp;</option><option value="real">REALISASI&nbsp;&nbsp;</option><option value="plac_mty">RELOKASI MTY&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "stripping_depo") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST STRIPPING&nbsp;&nbsp;</option><option value="tgl_app">APPROVE&nbsp;&nbsp;</option><option value="real">REALISASI&nbsp;&nbsp;</option><option value="plac_mty">PLACEMENT MTY&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "stuffing_tpk") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST STUFFING&nbsp;&nbsp;</option><option value="gati">BORDER GATE IN&nbsp;&nbsp;</option><option value="plac">PLACEMENT&nbsp;&nbsp;</option><option value="real">REALISASI&nbsp;&nbsp;</option><option value="req_del">REQUEST DELIVERY&nbsp;&nbsp;</option><option value="gato">GATE OUT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "stuffing_depo") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;<option value="req">REQUEST STUFFING&nbsp;&nbsp;</option><option value="real"> REALISASI&nbsp;&nbsp;</option><option value="req_del">REQUEST DELIVERY&nbsp;&nbsp;</option><option value="gato">GATE OUT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "delivery_tpk_fcl") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST DELIVERY&nbsp;&nbsp;</option><option value="gato">BORDER GATE OUT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "delivery_tpk_mty") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST DELIVERY&nbsp;&nbsp;</option><option value="gato">BORDER GATE OUT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "delivery_luar") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST DELIVERY&nbsp;&nbsp;</option><option value="gato">GATE OUT&nbsp;&nbsp;</option>'
                );

            } else if (kegiatan == "relokasi") {
                $('#STATUS').html(
                    '<option value=>&nbsp;&nbsp;</option><option value="req">REQUEST RELOKASI&nbsp;&nbsp;</option><option value="gato">GATE OUT&nbsp;&nbsp;</option><option value="gato">GATE OUT ASAL&nbsp;&nbsp;</option><option value="gati">GATE IN TUJUAN&nbsp;&nbsp;</option><option value="plac">PLACEMENT TUJUAN&nbsp;&nbsp;</option>'
                );

            }
        }

        function generateTable(headers) {
            // Find the table by its ID
            var table = document.getElementById("service-table");

            // Check if the table exists
            if (table) {
                // Clear existing <td> elements in the <thead>
                var thead = table.getElementsByTagName("thead")[0];
                thead.innerHTML = "";

                // Create a new row for the header
                var headerRow = thead.insertRow(0);

                // Loop through the headers array to create and append <td> elements
                for (var i = 0; i < headers.length; i++) {
                    var cell = headerRow.insertCell(i);
                    cell.innerHTML = headers[i];
                }
            }
        }

        function generateButton() {


            if ($.fn.DataTable.isDataTable('#service-table')) {
                $('#service-table').DataTable().destroy();
            }

            var kegiatan = $('#kegiatan').val();
            var status = $('#STATUS').val();

            if (kegiatan == 'receiving_tpk' || kegiatan == 'receiving_luar') {
                // Create an array of the header cell content
                var table = ["No", "No Container", "Size", "Type", "Status", "Tgl Request", "No Request", "EMKL",
                    "Peralihan", "Tgl GATE IN", "NoPoL", "Tgl Placement", "Lokasi"
                ];
                generateTable(table);
                var columns = [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_container',
                        name: 'no_container'
                    },
                    {
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'emkl',
                        name: 'emkl'
                    },
                    {
                        data: 'peralihan',
                        name: 'peralihan',
                        render: function(data, type, row, meta) {
                            return '';
                        }
                    },
                    {
                        data: 'tgl_in',
                        name: 'tgl_in'
                    },
                    {
                        data: 'nopol',
                        name: 'nopol'
                    },
                    {
                        data: 'tgl_placement',
                        name: 'tgl_placement'
                    },
                    {
                        data: 'block',
                        name: 'block',
                        render: function(data, type, row, meta) {
                            var block = row.nama_blok !== null ? row.nama_blok : '';
                            var row_ = row.slot_ !== null ? row.slot_ : '';
                            var slot = row.row_ !== null ? row.row_ : '';
                            var tier = row.tier_ !== null ? row.tier_ : '';
                            return block + ' , ' + row_ + ' , ' + slot + ' , ' + tier;
                        }
                    }
                ];
            } else if (kegiatan == 'stripping_tpk') {
                var headers = ["No", "No Container", "Size", "Type", "Status", "Tgl Request", "No Request", "EMKL"];

                // Append additional headers based on condition
                if (status != 'tgl_app') {
                    headers.push("TGL IN", "Tgl Placement", "Lokasi");
                }

                headers.push("Tgl Approve");

                if (status != 'tgl_app') {
                    headers.push("Tgl Realisasi", "Tgl Relokasi", "Lokasi");
                }

                console.log(headers);
                generateTable(headers);

                var columns = [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_container',
                        name: 'no_container'
                    },
                    {
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'emkl',
                        name: 'emkl'
                    },
                ];

                // Append additional headers based on condition
                if (status != 'tgl_app') {
                    columns.push({
                        data: 'tgl_in',
                        name: 'tgl_in'
                    }, {
                        data: 'tgl_placement',
                        name: 'tgl_placement'
                    }, {
                        data: 'block',
                        name: 'block',
                        render: function(data, type, row, meta) {
                            var block = row.nama_blok !== null ? row.nama_blok : '';
                            var row_ = row.slot_ !== null ? row.slot_ : '';
                            var slot = row.row_ !== null ? row.row_ : '';
                            var tier = row.tier_ !== null ? row.tier_ : '';
                            return block + ' , ' + row_ + ' , ' + slot + ' , ' + tier;
                        }
                    });
                }

                columns.push({
                    data: 'tgl_approve',
                    name: 'tgl_approve'
                }, );

                if (status != 'tgl_app') {
                    columns.push({
                        data: 'tgl_realisasi',
                        name: 'tgl_realisasi'
                    }, {
                        data: 'tgl_relokasi',
                        name: 'tgl_relokasi'
                    }, {
                        data: 'nama_blok_mty',
                        name: 'nama_blok_mty',
                        render: function(data, type, row, meta) {
                            var row_ = row.slot2 !== null ? row.slot2 : '';
                            var slot = row.row2 !== null ? row.row2 : '';
                            var tier = row.tier2 !== null ? row.tier2 : '';
                            return row_ + ' , ' + slot + ' , ' + tier;
                        }
                    });
                }

            } else if (kegiatan == 'stuffing_depo' || kegiatan == 'stuffing_tpk') {

                var headers = ["No", "No Container", "Size", "Type", "Status", "Tgl Request", "No Request", "EMKL"];


                // Add the remaining headers
                headers.push("Tgl Realisasi");

                generateTable(headers);
                var columns = [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_container',
                        name: 'no_container'
                    },
                    {
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'emkl',
                        name: 'emkl'
                    },
                ];



                columns.push({
                    data: 'tgl_realisasi',
                    name: 'tgl_realisasi'
                });


            } else if (kegiatan == 'delivery_tpk_mty' || kegiatan == 'delivery_luar') {
                // Define the headers
                var headers = ["No", "No Container", "Size", "Type", "Status", "Komoditi", "Berat", "Tgl Request",
                    "No Request", "Perp Dari", "EMKL", "Active"
                ];


                // Append additional header based on the condition
                if (kegiatan == 'delivery_tpk_mty') {
                    headers.push("Nm Kapal");
                }

                // Append remaining headers
                headers.push("Tgl Gate Out");

                generateTable(headers);
                var columns = [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_container',
                        name: 'no_container'
                    },
                    {
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'komoditi',
                        name: 'komoditi'
                    },
                    {
                        data: 'berat',
                        name: 'berat'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'emkl',
                        name: 'emkl',
                        render: function(data, type, row, meta) {
                            var kapal = (row && row.perp_dari && row.perp_dari.trim() !== '') ? row.perp_dari : ' ';
                            return kapal;
                        }
                    },
                    {
                        data: 'emkl',
                        name: 'emkl'
                    },
                    {
                        data: 'emkl',
                        name: 'emkl',
                        render: function(data, type, row, meta) {
                            var kapal = (row && row.tgl_delivery && row.tgl_delivery.trim() !== '') ? row
                                .tgl_delivery : ' ';
                            return kapal;
                        }
                    },
                ];

                // Append additional header based on the condition
                if (kegiatan == 'delivery_tpk_mty') {
                    columns.push({
                        data: 'nama_blok_mty',
                        name: 'nama_blok_mty',
                        render: function(data, type, row, meta) {
                            var kapal = row.vessel !== null ? row.vessel : '';
                            var voyage = row.voyage !== null ? row.voyage : '';
                            return kapal + ' , ' + voyage;
                        }
                    });
                }

                // Append remaining columns
                columns.push({
                    data: 'tgl_gate',
                    name: 'tgl_gate'
                });
            }

            console.log(columns);
            console.log(headers);
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.monitoring.listdata') !!}',
                    type: 'GET',
                    data: function(d) {
                        d.TGL_AWAL = $('#TGL_AWAL').val();
                        d.TGL_AKHIR = $('#TGL_AKHIR').val();
                        d.KEGIATAN = $('#kegiatan').val();
                        d.STATUS = $('#STATUS').val();
                    }
                },
                columns: columns,
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
                initComplete: function() {
                    console.log('initComplete');
                    // Initialize date range filter inputs
                    $('#from, #to').on('change', function() {
                        // Check if both 'from' and 'to' are filled before triggering the DataTable redraw
                        if ($('#TGL_AWAL').val() !== '' && $('#TGL_AKHIR').val() !== '') {
                            $('#service-table').DataTable().ajax
                                .reload();
                        }
                    });
                }
            });
        }

        function generateExcel() {
           
            var kegiatan = $('#kegiatan').val();
            var status = $('#STATUS').val();
            var tgl_awal_ = $("#TGL_AWAL").val();
            var tgl_akhir_ = $("#TGL_AKHIR").val();

            // Menggunakan fungsi route() untuk mendapatkan URL
            var url = "{{ route('uster.monitoring.toExcel') }}?KEGIATAN=" + kegiatan + "&STATUS=" +
                status + "&TGL_AWAL=" + tgl_awal_ + "&TGL_AKHIR=" + tgl_akhir_;

            window.open(url, "_blank");

        }
    </script>
@endsection
