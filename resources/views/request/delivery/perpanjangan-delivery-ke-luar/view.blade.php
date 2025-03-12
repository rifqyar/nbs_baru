@extends('layouts.app')

@section('title')
Perencanaan Kegiatan Delivery
@endsection

@section('pages-css')
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">New Request</a></li>
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.new_request.delivery.perpanjangan_delivery_luar.index')}}">Perpanjangan Delivery Ke Luar</a></li>
            <li class="breadcrumb-item active">Perpanjangan</li>
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
                <h3><b>Perpanjangan Delivery - SP2</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <input type="hidden" name="total" id="total">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nomor Request Sebelumnya: </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="NO_REQ" id="NO_REQ" class="form-control" value="{{ $data['row_request']->no_request}}" readonly>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">E.M.K.L : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="EMKL" id="EMKL" class="form-control" value="{{ $data['row_request']->nama_emkl}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Tgl Request : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="tgl_dev" name="tgl_dev" class="form-control" value="{{ $data['row_request']->tgl_request}}">
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Keterangan : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="alamat" id="ALAMAT" class="form-control" value="{{ $data['row_request']->keterangan ?? ''}}" readonly>
                                    <input type="hidden" id="jumlah" value="{{$data['row_count']->jumlah}}" />
                                </div>
                            </div>
                        </div>

                        <div class="m-3 card p-3">
                            <div class="table-responsive">
                                <table class="datatables-service table " id="container-table">
                                    <thead>
                                        <tr>
                                            <th>No </th>
                                            <th>No Container</th>
                                            <th>Komoditi </th>
                                            <th>Via</th>
                                            <th>Hz</th>
                                            <th>Tgl Delivery</th>
                                            <th>Tgl Perpanjangan</th>
                                        </tr>
                                    </thead>

                                </table>
                            </div>
                            <div id="button_save" class="text-center my-3">

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pages-js')
<script>
    $(document).ready(function() {
        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: false,
            ajax: {
                url: '{!! route("uster.new_request.delivery.perpanjangan_delivery_luar.contlist") !!}',
                type: 'GET',
                data: function(d) {
                    d.noReq = $('#NO_REQ').val();
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
                    data: 'komoditi',
                    name: 'komoditi'
                },
                {
                    data: 'via',
                    name: 'via'
                },
                {
                    data: 'hz',
                    name: 'hz'
                },
                {
                    data: 'tgl_delivery',
                    name: 'tgl_delivery',
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                        id = 'TGL_PERP_' + meta.row;
                        return '<input type="date" value=' + data.tgl_delivery + ' id=' + id + ' name=' + id + ' />';
                    }
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                $(row).append('<input type="hidden" id="NO_CONT_' + rowIdx + '"  name="NO_CONT_' + rowIdx + '" class="hidden-input" value="' + data.no_container + '">');
                // $(row).append('<input type="text" id="TGL_PERP_' + rowIdx + '" name="TGL_PERP_' + rowIdx + '" class="hidden-input">');
            },
            lengthMenu: [10, 20, 50, 100, 200], // Set the default page lengths
            pageLength: 200, // Set the initial page length
            initComplete: function() {
                var table = $('#container-table').DataTable();
                var totalRows = table.rows().count();
                $('#button_save').html('<button type="button" onclick="simpanarea()" value="Save" class="btn btn-info">Simpan Perpanjangan SP2</button>');
                $('#total').val(totalRows);
            }
        });
    });


    ///==================================================///


    function sAlert(title, msg, type) {
        let timerInterval;
        Swal.fire({
            title: title,
            timer: 1500,
            text: msg,
            type: type,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    function simpanarea() {

        var formData = $('#dataCont').serialize();

        $.ajax({
            url: '{!! route("uster.new_request.delivery.perpanjangan_delivery_luar.updateperpanjangandelivery") !!}', // Ganti dengan URL yang sesuai
            type: 'POST', // Ganti dengan metode HTTP yang sesuai (GET/POST)
            data: formData,
            beforeSend: function() {
                // Tampilkan pesan SweetAlert sebelum permintaan dikirim
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                // Sembunyikan pesan SweetAlert setelah permintaan berhasil
                Swal.close();
                // Tampilkan pesan sukses menggunakan SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Data berhasil disimpan',
                });
                // Lakukan tindakan tambahan sesuai kebutuhan, misalnya memperbarui tampilan
                window.location.href = '{!! route("uster.new_request.delivery.perpanjangan_delivery_luar.index") !!}'
            },
            error: function(xhr, status, error) {
                // Sembunyikan pesan SweetAlert setelah permintaan gagal
                Swal.close();
                // Tampilkan pesan error menggunakan SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Ada masalah saat menyimpan data',
                });
                // Lakukan penanganan kesalahan tambahan sesuai kebutuhan
            }
        });
    }
</script>
@endsection
