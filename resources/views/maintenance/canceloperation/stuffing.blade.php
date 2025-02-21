@extends('layouts.app')

@section('title')
Batal Container SP2 - Stripping
@endsection

@section('pages-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Koreksi</a></li>
            <li class="breadcrumb-item active">Batal Container SP2 - Stripping</li>
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
                <h3><b>Batal Container SP2 - Stripping</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3 text-center">
                            <div class="row">
                                <div class="col-md-4 py-2">
                                    <label for="tb-fname">No Container: </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="NO_CONT" id="NO_CONT" class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 py-2">
                                    <label for="tb-fname">No Request: </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="NO_REQUEST" id="NO_REQUEST" class="form-control">
                                    </select>
                                </div>
                                <input type='hidden' id="NO_BOOKING" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card p-2">
                    <div class="table-responsive">
                        <table class="datatables-service table " id="container-table">
                            <thead>
                                <tr>
                                    <th>No </th>
                                    <th>Kegiatan</th>
                                    <th>No Request</th>
                                    <th>Eksekutor</th>
                                    <th>Time</th>
                                    <th>Action</th>
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

        $('#NO_CONT').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.maintenance.cancel_operation.stuffing.getnocontainer") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_container,
                            text: arr.no_container + ' | ' + arr.no_request,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NO_CONT').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NO_CONT").val(data.no_container);
            $("#NO_REQUEST").val(data.no_request);
            $("#NO_BOOKING").val(data.no_booking);
            load_table(data.no_request, data.no_container, data.no_booking);
        })

        $('#NO_REQUEST').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.maintenance.cancel_operation.stuffing.getrequest") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_container,
                            text: arr.no_container + ' | ' + arr.no_request,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NO_REQUEST').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NO_CONT").val(data.no_container);
            $("#NO_REQUEST").val(data.no_request);
            $("#NO_BOOKING").val(data.no_booking);
            load_table(data.no_request, data.no_container, data.no_booking);
        })
    });


    function load_table(noreq, nocont, nobook) {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }
        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.maintenance.cancel_operation.stuffing.datatable") !!}' + '?NO_CONT=' + nocont + '&NO_REQUEST=' + noreq + '&NO_BOOKING=' + nobook,
                type: 'GET',
                data: function(d) {
                    // d.noReq = $('#NO_REQ').val();
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
                    data: 'kegiatan',
                    name: 'kegiatan'
                },
                {
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'nama_lengkap',
                    name: 'nama_lengkap'
                },
                {
                    data: 'tgl_update',
                    name: 'tgl_update'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        noReq = data['no_request'];
                        return '<button value="Hapus" onclick="delete_operation(\'' + data['no_container'] + '\',' + data['no_request'] + '\',' + data['no_booking'] + '\',' + data['kegiatan'] + ')" class="btn btn-danger"> Hapus </button>';
                    }
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                $(row).append('<input type="hidden" id="NO_CONT_' + rowIdx + '" class="hidden-input" value="' + data.no_container + '">');
            },
            lengthMenu: [10, 20, 50, 100], // Set the default page lengths
            pageLength: 10, // Set the initial page length
            initComplete: function() {
                var table = $('#container-table').DataTable();
                var totalRows = table.rows().count();
                // $('#button_save').html('<button type="button" onclick="save_tgl_delivery(' + totalRows + ')" value="Save" class="btn btn-info">Save</button>');
            }
        });
    }


    ///==================================================///


    function delete_operation() {
        var no_cont_ = $("#NO_CONT").val();
        var no_req_ = $("#NO_REQ").val();
        var ket_ = $("#keterangan").val();
        var csrfToken = '{{ csrf_token() }}';

        $.ajax({
            url: '{!! route("uster.maintenance.cancel_operation.stuffing.deleteoperation") !!}',
            type: 'POST',
            data: {
                _token: csrfToken,
                NO_CONT: no_cont_,
                NO_REQ: no_req_,
                KETERANGAN: ket_
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(data) {
                Swal.close();
                if (data.status['code'] == 200) {
                    if (data.status['msg'] == 'Y') {
                        sAlert('Berhasil', 'Data Telah Dihapus', 'success');
                    } else if (data.status['msg'] == 'PL') {
                        sAlert('Gagal!', 'Maaf, Container Sudah Placement di TPK', 'danger');
                    } else if (data.status['msg'] == 'ST') {
                        sAlert('Gagal!', 'Maaf, Container Telah Berada di Siklus Stripping', 'danger');
                    } else if (data.status['msg'] == 'RC') {
                        sAlert('Gagal!', 'Maaf, Container Telah Berada di Siklus Receiving', 'danger');
                    } else {
                        sAlert('', data.status['msg'], 'warning');
                    }
                } else {
                    sAlert('Gagal!', data.status['msg'], 'danger');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
            },
            complete: function() {

            }
        });
    }
</script>
@endsection