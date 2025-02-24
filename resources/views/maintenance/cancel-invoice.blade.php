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
                <li class="breadcrumb-item">Operation</a></li>
                <li class="breadcrumb-item">Gate</li>
                <li class="breadcrumb-item"><a href="{{ route('uster.operation.gate.gate_out') }}">Gate Out</a>
                </li>
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
                    <h3><b>Cancel Invoice</b></h3>

                    <form action="javascript:void" id="requestGateIn" novalidate>
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NO NOTA : </label>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" id="NO_NOTA" name="NO_NOTA">
                                        <input class="form-control" id="NO_NOTA_" name="NO_NOTA_" type="hidden">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NO PERMINTAAN : </label>
                                </div>
                                <div class="col-md-4">

                                    <div class="row">

                                        <div class="col">
                                            <input class="form-control" id="NO_REQUEST" name="NO_REQUEST" type="text"
                                                value="" size="50" readonly="1">
                                        </div>

                                        <div class="col">
                                            <input class="form-control" id="KEGIATAN" name="KEGIATAN" type="text"
                                                value="" size="50" readonly="1">

                                        </div>


                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NAMA PELANGGAN : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="NM_PBM" name="NM_PBM" type="text" value=""
                                        size="50" readonly="1">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">ALAMAT : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="ALAMAT" name="ALAMAT" type="text" value=""
                                        size="100" readonly="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NO NPWP : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="NO_NPWP" name="NO_NPWP" type="text" value=""
                                        size="50" readonly="1">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">TOTAL TAGIHAN/PPN</label>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">

                                        <div class="col">
                                            <input class="form-control" id="TOTAL" name="TOTAL" type="text"
                                                value="" size="15" readonly="1">
                                        </div>

                                        <div class="col">
                                            <input class="form-control" id="PPN" name="PPN" type="text"
                                                readonly="1" value="" size="10">
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Jumlah Dibayar: </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="TOTAL_TAGIHAN" name="TOTAL_TAGIHAN" type="text"
                                        value="" size="35" readonly="1">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Status Lunas/Tanggal Lunas</label>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">

                                        <div class="col">
                                            <input class="form-control" id="LUNAS" name="LUNAS" type="text"
                                                readonly>

                                        </div>

                                        <div class="col">
                                            <input class="form-control" id="TGL_LUNAS" name="TGL_LUNAS" type="text"
                                                readonly>
                                        </div>

                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">STATUS LUNAS : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="TRANSFER" name="TRANSFER" type="text"
                                        value="" size="50" readonly="1">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Status Di Keuangan : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="CM" name="CM" type="text"
                                        value="" size="50" readonly="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">KETERANGAN : </label>
                                </div>
                                <div class="col">
                                    <input class="form-control" id="CM" name="CM" type="text"
                                        value="" size="50">
                                </div>


                            </div>
                        </div>
                        <button type="submit" class="btn btn-info" onclick="cancel()">Simpan Data</button>
                    </form>
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
        $(document).ready(function() {
            $('#NO_NOTA').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.maintenance.getNota') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.no_faktur,
                                text: item.no_faktur + '-' + item.emkl,
                                ...item
                            }))
                        };
                    }
                }
            });

            $('#NO_NOTA').on('select2:select', function(e) {
                var data = e.params.data;
                $("#CM").val(data.cm);
                $("#NO_NOTA").val(data.no_faktur);
                $("#NO_NOTA_").val(data.no_nota);
                $("#NO_REQUEST").val(data.no_request);
                $("#NM_PBM").val(data.emkl);
                $("#NO_NPWP").val(data.no_npwp_pbm);
                $("#ALAMAT").val(data.alamat);
                $("#NO_NPWP").val(data.npwp);
                $("#TOTAL_TAGIHAN").val(data.total_tagihan);
                $("#PPN").val(data.ppn);
                $("#TOTAL").val(data.total);
                $("#LUNAS").val(data.lunas);
                $("#TRANSFER").val(data.transfer);
                $("#TGL_LUNAS").val(data.tgl_lunas);
                $("#KEGIATAN").val(data.kegiatan);
                return false;
            })
            $('#NO_NOTA').on('select2:clear', function(e) {
                $form[0].reset()
            })
        });

        function cancel() {

            var NO_NOTA_ = $('#NO_NOTA_').val();
            var NO_FAKTUR = $('#NO_NOTA').val();
            var NO_REQUEST_ = $('#NO_REQUEST').val();
            var KEGIATAN = $('#KEGIATAN').val();
            var KETERANGAN = $('#id_KETERANGAN').val();
            var url = '{{ route('uster.maintenance.storeCancel') }}';

            Swal.fire({
                title: 'Konfirmasi',
                text: "Yakin untuk menyimpan ini? Pastikan Inputan Anda Sudah Benar",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Merubah Data...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post(url, {
                        NO_NOTA: NO_NOTA_,
                        TRX_NUMBER: NO_FAKTUR,
                        NO_REQUEST: NO_REQUEST_,
                        KEGIATAN: KEGIATAN,
                        KETERANGAN: KETERANGAN
                    }, function(data) {
                        if (data == 'sukses') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cancel Invoice Succeed, Lanjutkan Pembatalan Di Keuangan',
                                showConfirmButton: false,
                                timer: 1000
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: data,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'You pressed Cancel!',
                    });
                }
            });

        }
    </script>
@endsection
