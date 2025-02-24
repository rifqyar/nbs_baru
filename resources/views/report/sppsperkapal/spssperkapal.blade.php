@extends('layouts.app')

@section('title')
Laporan SPPS per Kapal
@endsection

@section('pages-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Report</a></li>
            <li class="breadcrumb-item active">Laporan SPPS per Kapal</li>
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
                <h3><b>Laporan SPPS per Kapal</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Kapal: </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="NM_KAPAL" id="NM_KAPAL" class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Type: </label>
                                </div>
                                <div class="col-md-4">
                                    <select id="kegiatan" class="form-control">
                                        <option value="stripping"> Stripping </option>
                                        <option value="stuffing"> Stuffing </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Voyage: </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="VOYAGE" id="VOYAGE" readonly />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No UKK / NO Booking: </label>
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input class="form-control" type="text" name="NO_UKK" id="NO_UKK" readonly />
                                        </div>
                                        /
                                        <div class="col-md-4">
                                            <input class="form-control" type="text" name="NO_BOOKING" id="NO_BOOKING" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <button onclick="genReport()" class="btn btn-danger mr-2"><i class="fas fa-file-alt pr-2"></i> Generate Report</button>
                    <button onclick="toExcel()" class="btn excel-button"><i class="fas fa-file-excel pr-2"></i> Generate Excel</button>
                </div>
                <div class="card p-2">
                    <div class="table-responsive">
                        <table class="datatables-service table " id="container-table">
                            <thead>
                                <tr>
                                    <th>No </th>
                                    <th>No Container</th>
                                    <th>Size</th>
                                    <th>No Request</th>
                                    <th>No Request Delivery/Receiving</th>
                                    <th>Tgl Request</th>
                                    <th>komoditi</th>
                                    <th>User</th>
                                    <th>Consignee</th>
                                    <th>Tgl Awal</th>
                                    <th>Tgl Akhir</th>
                                    <th>Lunas</th>
                                    <th>Tgl Gate</th>
                                    <th>Tgl Realisasi</th>
                                    <th>User</th>
                                    <th>Via</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                    <div id="button_save" class="text-center my-3">

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('pages-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {

        $('#NM_KAPAL').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.report.spps.mastervessel") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.voyage_in,
                            text: arr.voyage_in + ' | ' + arr.nm_kapal,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_KAPAL').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NM_KAPAL").val(data.nm_kapal);
            $("#ID_EMKL").val(data.kd_pbm);
            $("#NO_UKK").val(data.no_ukk);
            $("#VOYAGE").val(data.voyage_in);
            $("#NO_BOOKING").val(data.no_booking);
        })
    });

    function genReport() {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }
        var csrfToken = '{{ csrf_token() }}';
        var ukk = $("#NO_UKK").val();
        var keg = $("#kegiatan").val();
        var nm_kapal = $("#NM_KAPAL").val();
        var voyage = $("#VOYAGE").val();
        var no_booking = $("#NO_BOOKING").val();

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.report.spps.datatable") !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    NO_UKK: ukk,
                    NM_KAPAL: nm_kapal,
                    VOYAGE: voyage,
                    KEGIATAN: keg,
                    NO_BOOKING: no_booking
                }
            },
            columns: [{
                    data: 'DT_RowIndex', // Use the special 'DT_RowIndex' property provided by DataTables
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        // Render the sequence number
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
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        if (isset(data['no_request_delivery'])) {
                            return data['no_request_delivery'];
                        } else if (isset(data['no_request_receiving'])) {
                            return data['no_request_receiving'];
                        }
                    }
                },
                {
                    data: 'tgl_request',
                    name: 'tgl_request'
                },
                {
                    data: 'commodity',
                    name: 'commodity'
                },
                {
                    data: 'nm_request',
                    name: 'nm_request'
                },
                {
                    data: 'nm_pbm',
                    name: 'nm_pbm'
                },
                {
                    data: 'tgl_awal',
                    name: 'tgl_awal'
                },
                {
                    data: 'tgl_akhir',
                    name: 'tgl_akhir'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
                },
                {
                    data: 'tgl_gate',
                    name: 'tgl_gate'
                },
                {
                    data: 'tgl_realisasi',
                    name: 'tgl_realisasi'
                },
                {
                    data: 'nm_realisasi',
                    name: 'nm_realisasi'
                },
                {
                    data: 'status',
                    name: 'status'
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                // $(row).append('<input type="hidden" id="NO_CONT_' + rowIdx + '" class="hidden-input" value="' + data.no_container + '">');
            },
            lengthMenu: [10, 20, 50, 100], // Set the default page lengths
            pageLength: 10, // Set the initial page length
            initComplete: function() {
                // var table = $('#container-table').DataTable();
                // var totalRows = table.rows().count();
                // $('#button_save').html('<button type="button" onclick="save_tgl_delivery(' + totalRows + ')" value="Save" class="btn btn-info">Save</button>');
            }
        });
    }

    function isset(variable) {
        return variable !== null && variable !== undefined;
    }

    function toExcel() {
        var ukk = $("#NO_UKK").val();
        var keg = $("#kegiatan").val();
        var nm_kapal = $("#NM_KAPAL").val();
        var voyage = $("#VOYAGE").val();
        var no_booking = $("#NO_BOOKING").val();
        var url = '{!! route("uster.report.spps.generateexcel") !!}?ukk=' + ukk + '&keg=' + keg + '&nm_kapal=' + nm_kapal + '&voyage=' + voyage + '&no_booking=' + no_booking;        
        window.location.href = url;    
    }
</script>
@endsection