@extends('layouts.app')

@section('title')
Laporan Delivery per kapal
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
            <li class="breadcrumb-item active">Laporan Delivery per kapal</li>
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
                <h3><b>Laporan Delivery per kapal</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Kapal: </label>
                                </div>
                                <div class="col-md-4">
                                    <!-- <input class="form-control" type="text" name="NM_KAPAL" id="NM_KAPAL" /> -->
                                    <select name="NM_KAPAL_SELECT" id="NM_KAPAL_SELECT" class="form-control"></select>
                                    <input class="form-control" type="hidden" name="NM_KAPAL" id="NM_KAPAL" />
                                    <input class="form-control" type="hidden" name="NO_BOOKING" id="NO_BOOKING" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Voyage: </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="VOYAGE" id="VOYAGE" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Status: </label>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="STATUS">
                                        <option value=""> -- Pilih -- </option>
                                        <option value="MTY"> MTY </option>
                                        <option value="FCL"> FCL </option>
                                        <option value="LCL"> LCL </option>
                                        <option value="ALL"> ALL </option>
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
                                    <th>NO </th>
                                    <th>NO CONTAINER</th>
                                    <th>SIZE</th>
                                    <th>TYPE</th>
                                    <th>NO REQUEST</th>
                                    <th>STATUS</th>
                                    <th>VIA</th>
                                    <th>NOPOL</th>
                                    <th>TGL GATE OUT</th>
                                    <th>USER</th>
                                    <th>NO NOTA</th>
                                    <th>LUNAS</th>
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
        $('#NM_KAPAL_SELECT').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.report.deliveryperkapal.mastervessel") !!}',
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

        $('#NM_KAPAL_SELECT').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NM_KAPAL").val(data.nm_kapal);
            $("#VOYAGE").val(data.voyage_in);
        })

    });

    function genReport() {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }
        var nm_kapal = $("#NM_KAPAL").val();
        var voyage = $("#VOYAGE").val();
        var status_ = $("#STATUS").val();
        var csrfToken = '{{ csrf_token() }}';

        if ($("#NM_KAPAL").val() == '') {
            alert('Nama Kapal harus diisi');
        }

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.report.deliveryperkapal.datatable") !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    NM_KAPAL: nm_kapal,
                    VOYAGE: voyage,
                    STATUS: status_
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
                    data: 'type_',
                    name: 'type_'
                },
                {
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'via',
                    name: 'via'
                },
                {
                    data: 'nopol',
                    name: 'nopol'
                },
                {
                    data: 'tgl_in',
                    name: 'tgl_in'
                },
                {
                    data: 'username',
                    name: 'username'
                },
                {
                    data: 'no_nota',
                    name: 'no_nota'
                },
                {
                    data: 'lunas',
                    name: 'lunas'
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
        var url = '{!! route("uster.report.deliveryperkapal.generateexcel") !!}?NO_REQUEST=' + request + "&KEGIATAN=" + kegiatan;
        window.location.href = url;
    }
</script>
@endsection
