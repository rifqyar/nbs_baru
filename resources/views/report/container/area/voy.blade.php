@extends('layouts.app')

@section('title')
Laporan Container By Area
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
            <li class="breadcrumb-item">Container</a></li>
            <li class="breadcrumb-item active">Laporan Container By Area</li>
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
                <h3><b>Laporan Container By Area</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Status Container: </label>
                                </div>
                                <div class="col-md-4">
                                    <select class='form-control' id="option_status" style="width:100%;">
                                        <option selected="selected" value="MTY"> MTY </option>
                                        <option value="FCL"> FCL </option>
                                        <option value="ALL"> ALL </option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Location: </label>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="option_locate" style="width:100%;">
                                        <option selected="selected" value="stripping"> Stripping Area </option>
                                        <option value="stuffing"> Stuffing Area </option>
                                        <option value="mty"> Empty Area </option>
                                        <option value="XX"> Uncategorized Area </option>
                                    </select>
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
                                    <th>No.Container</th>
                                    <th>lokasi</th>
                                    <th>Kegiatan</th>
                                    <th>Tgl Placement</th>
                                    <th>Username</th>
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


    });

    function genReport() {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }
        var csrfToken = '{{ csrf_token() }}';
        var option_status = $("#option_status").val();
        var option_locate = $("#option_locate").val();

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.report.container.area.datatable") !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    option_status: option_status,
                    option_locate: option_locate
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
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return data['no_container'] + '<br/>' + data['size_'] + '/' + data['type_'] + '/' + data['status_cont'];
                    }
                },                
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return data['slot_'] + '<br/>' + data['slot_'] + '/' + data['row_'] + '/' + data['tier_'];
                    }
                },
                {
                    data: 'kegiatan',
                    name: 'kegiatan'
                },
                {
                    data: 'tgl_placement',
                    name: 'tgl_placement'
                },
                {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
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
        var request = $("#NO_REQUEST").val();
        var kegiatan = $("#KEGIATAN").val();
        var url = '{!! route("uster.report.container.area.generateexcel") !!}?NO_REQUEST=' + request + "&KEGIATAN=" + kegiatan;
        window.location.href = url;
    }
</script>
@endsection