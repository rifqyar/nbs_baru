@extends('layouts.app')

@section('title')
Laporan Container By Request
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
            <li class="breadcrumb-item active">Laporan Container By Request</li>
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
                <h3><b>Laporan Container By Request</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No Request: </label>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="NO_REQUEST" name="NO_REQUEST" type="text"></select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Kegiatan: </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="KEGIATAN" id="KEGIATAN" readonly />
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
                                    <th>NO </th>
                                    <th>NO CONTAINER</th>
                                    <th>TGL AWAL</th>
                                    <th>TGL AKHIR</th>
                                    <th>SIZE</th>
                                    <th>TYPE</th>
                                    <th>STATUS</th>
                                    <th>HZ</th>
                                    <th>COMMODITY</th>
                                    <th>GROSS</th>
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

        $('#NO_REQUEST').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.report.container.request.nota") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_request,
                            text: arr.no_request + ' | ' + arr.kegiatan,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NO_REQUEST').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NO_REQUEST").val(data.no_request);
            $("#KEGIATAN").val(data.kegiatan);
        })
    });

    function genReport() {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }
        var csrfToken = '{{ csrf_token() }}';
        var nota = $("#NO_NOTA").val();
        var request = $("#NO_REQUEST").val();
        var kegiatan = $("#KEGIATAN").val();

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.report.container.request.datatable") !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    NO_NOTA: nota,
                    NO_REQUEST: request,
                    KEGIATAN: kegiatan
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
                    data: 'tgl_awal',
                    name: 'tgl_awal'
                },
                {
                    data: 'tgl_akhir',
                    name: 'tgl_akhir'
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
                    data: 'hz',
                    name: 'hz'
                },
                {
                    data: 'komoditi',
                    name: 'komoditi'
                },
                {
                    data: 'berat',
                    name: 'berat'
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
        var url = '{!! route("uster.report.container.request.generateexcel") !!}?NO_REQUEST=' + request + "&KEGIATAN=" + kegiatan;
        window.location.href = url;
    }

    function toPdf() {
        var tgl_awal_ = $("#tgl_awal").val();
        var tgl_akhir_ = $("#tgl_akhir").val();

        var url = '{!! route("uster.report.container.request.generatepdf") !!}?tgl_awal=' + tgl_awal_ + "&tgl_akhir=" + tgl_akhir_;

        window.location.href = url;
    }
</script>
@endsection