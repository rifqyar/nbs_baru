@extends('layouts.app')

@section('title')
Perencanaan Kegiatan Delivery
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
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.new_request.delivery.delivery_luar.index')}}">Delivery Ke Luar</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
                <h3><b>Request Delivery - SP2 ke LUAR DEPO</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Consignee : </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="EMKL" id="EMKL" class="form-control w-100"></select>
                                    <input type="hidden" name="ID_EMKL" id="ID_EMKL" class="form-control" value="">
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Penumpukan Oleh : </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="NM_AGEN" id="NM_AGEN" class="form-control w-100"></select>
                                    <input type="hidden" id="KD_AGEN" name="KD_AGEN" class="form-control" value="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Alamat : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="alamat" id="ALAMAT" class="form-control" value="" readonly>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Keterangan : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="keterangan" class="form-control" value="">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Npwp : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="npwp" id="NPWP" class="form-control" value="" readonly>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No RO : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="NO_RO" name="NO_RO" class="form-control" value="">
                                </div>
                            </div>

                        </div>
                    </form>
                    <div class="row text-center mb-3">
                        <div class="col">
                            <button onclick="save()" class="btn btn-info">Simpan</button>
                        </div>
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
        $('#EMKL').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.pbm") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.kd_pbm,
                            text: arr.nm_pbm + ' | ' + arr.almt_pbm + ' | ' + arr.no_npwp_pbm,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#EMKL').on('select2:select', function(e) {
            var data = e.params.data;
            $("#EMKL").val(data.nm_pbm);
            $("#ID_EMKL").val(data.kd_pbm);
            $("#ALAMAT").val(data.almt_pbm);
            $("#NPWP").val(data.no_npwp_pbm);
            $("#AC_EMKL").val(data.no_account_pbm);
        });

        $('#NM_AGEN').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.pbm") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.kd_pbm,
                            text: arr.nm_pbm + ' | ' + arr.almt_pbm,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_AGEN').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NM_AGEN").val(data.nm_pbm);
            $("#KD_AGEN").val(data.kd_pbm);
        });
    });


    ///==================================================///


    function save() {

        var formData = $('#dataCont').serialize();

        $.ajax({
            url: '{!! route("uster.new_request.delivery.delivery_luar.savedata") !!}', // Ganti dengan URL yang sesuai
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
            success: function(data) {
                // Sembunyikan pesan SweetAlert setelah permintaan berhasil
                Swal.close();
                // Tampilkan pesan sukses menggunakan SweetAlert
                if (data.status['code'] == 200) {
                    sAlert('Berhasil!', data.status['msg'], 'success');
                    window.location.href = "{{route('uster.new_request.delivery.delivery_luar.edit')}}" + "?no_req=" + data.status['no_req'];
                } else {
                    sAlert('Gagal!', data.status['msg'], 'danger');
                }
                // Lakukan tindakan tambahan sesuai kebutuhan, misalnya memperbarui tampilan
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