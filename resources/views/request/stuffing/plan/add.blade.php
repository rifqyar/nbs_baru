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
                <li class="breadcrumb-item"><a href="{{ route('uster.new_request.stuffing.stuffing_plan') }}">Stuffing
                        Plan</a>
                </li>
                <li class="breadcrumb-item active">Add</li>
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
                    <h3><b>Perencanaan Kegiatan Stuffing</b></h3>

                    <form action="javascript:void(0)" id="requestStuffingPlan" class="form-horizontal m-t-20" novalidate>
                        @csrf
                        <div class="p-3">

                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Pengguna Jasa</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No Request : </label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" name="no_req" id="no_req" class="form-control"
                                        placeholder="Auto Fill" readonly="readonly" />
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nama EMKL : </label>
                                </div>
                                <div class="col-md-10">

                                    <select name="EMKL" id="EMKL" class="form-control" style="width: 100%"></select>
                                    <input type="hidden" name="ID_EMKL" id="ID_EMKL" />
                                    <input type="hidden" name="ACC_EMKL" id="ACC_EMKL" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Penumpukan Oleh : </label>
                                </div>
                                <div class="col-md-10">

                                    <select name="PNKN_BY" id="PNKN_BY" class="form-control" style="width: 100%"></select>
                                    <input type="hidden" name="ID_PNKN_BY" id="ID_PNKN_BY" />
                                    <input type="hidden" name="ACC_PNKN" id="ACC_PNKN" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">International/Domestik</label>
                                </div>
                                <div class="col-md-10">
                                    <select id="DI" name="DI" class="form-control">
                                        <option value='D'>Domestik</option>
                                        <option value='I'>International</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Yard Stack : </label>
                                </div>
                                <div class="col-md-10">
                                    <select id="YARD_STACK" name="YARD_STACK" class="form-control">
                                        <option value='MTI'>MTI</option>
                                        <option value='TPK'>TPK</option>
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="p-3">
                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Kapal</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nama Kapal : </label>
                                </div>
                                <div class="col-md-4">
                                    <select name="NM_KAPAL" id="NM_KAPAL" class="form-control"
                                        style="width: 100%"></select>
                                    <input id="KD_KAPAL" name="KD_KAPAL" type="hidden" value="" />
                                    <input id="TGL_MUAT" name="TGL_MUAT" type="hidden" value="" />
                                    <input id="TGL_STACKING" name="TGL_STACKING" type="hidden" value="" />
                                    <input id="TGL_BERANGKAT" name="TGL_BERANGKAT" type="hidden" value="" />
                                    <input id="CALL_SIGN" name="CALL_SIGN" type="hidden" value="" />
                                    <input id="POD" name="POD" type="hidden" value="" />
                                    <input id="POL" name="POL" type="hidden" value="" />
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Voyage : </label>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col">
                                            <input type="text" class="form-control" id="VOYAGE_IN" name="VOYAGE_IN"
                                                value="" readonly>
                                        </div>
                                        <div class="col">
                                            <input type="text" class="form-control" id="VOYAGE_OUT" name="VOYAGE_OUT"
                                                value="" readonly>
                                            <input id="ETD" name="ETD" type="hidden" value=""
                                                size="40" />
                                            <input id="ETA" name="ETA" type="hidden" value=""
                                                size="40" />
                                            <input id="VOYAGE" name="VOYAGE" type="hidden" value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nama Agen : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="KD_AGEN" name="KD_AGEN" type="hidden" class="form-control" readonly />
                                    <input id="NM_AGEN" name="NM_AGEN" type="text" value=""
                                        class="form-control" readonly />
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Port Of Destination : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="KD_PELABUHAN_ASAL" name="KD_PELABUHAN_ASAL" type="hidden" value=""
                                        class="form-control" readonly />
                                    <input id="NM_PELABUHAN_ASAL" name="NM_PELABUHAN_ASAL" type="text" value=""
                                        title="Autocomplete" class="form-control" readonly />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No PKK : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="NO_UKK" name="NO_UKK"
                                        readonly="readonly" title="Autocomplete">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Final Discharge : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="KD_PELABUHAN_TUJUAN" name="KD_PELABUHAN_TUJUAN" type="hidden"
                                        value="" readonly />
                                    <input class="form-control" id="NM_PELABUHAN_TUJUAN" name="NM_PELABUHAN_TUJUAN"
                                        type="text" value="" title="Autocomplete" readonly />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No Booking : </label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="NO_BOOKING" name="NO_BOOKING"
                                        value="" readonly>
                                </div>
                            </div>


                        </div>



                        <div class="p-3">
                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Dokumen Pendukung</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No P.E.B : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="hidden" id="PENUMPUKAN" size="40" name="PENUMPUKAN"
                                        class="kdemkl " placeholder=" AUTOCOMPLETE" style="background-color:#FFFFCC;" />
                                    <input size="40" name="ID_PENUMPUKAN" id="ID_PENUMPUKAN" type="hidden" />
                                    <input size="40" name="ALMT_PENUMPUKAN" id="ALMT_PENUMPUKAN"
                                        type="hidden" /><input size="40" name="NPWP_PENUMPUKAN"
                                        id="NPWP_PENUMPUKAN" type="hidden" />
                                    <input id="id_NO_PEB" name="NO_PEB" type="text" class="form-control" />
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor Dokumen : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="NO_DOC" id="NO_DOC">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No. N.P.E : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="NO_NPE" type="text"
                                        value="">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor JPB : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="NO_JPB" id="NO_JPB">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor D.O : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="NO_DO" id="NO_DO">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">BPRP : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="BPRP" id="BPRP">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor B.L : </label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="NO_BL" id="NO_BL">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor SPPB : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="NO_SPPB" name="NO_SPPB">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Tanggal SPPB : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" id="TGL_SPPB" name="TGL_SPPB">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Keterangan : </label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="keterangan" id="KETERANGAN">
                                </div>
                            </div>

                        </div>
                        <button type="submit" class="btn btn-primary float-right">Tambah
                            Perencanaan Stuffing</a>
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
        var table, s_no_request, s_tgl_awal, s_tgl_akhir
        var cachePBM = {}
        var forms = document.querySelectorAll('form')
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                }, false)
            })

        $('#requestStuffingPlan').on('submit', function(e) {
            if (this.checkValidity()) {
                e.preventDefault();
                saveEditData()
                console.log('34');
            }
            $(this).addClass('was-validated');
            console.log('3sad4');
        });

        function saveEditData() {
            var form = $('#requestStuffingPlan').serialize();
            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda Ingin Menyimpan Stuffing Plan ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyimpan Data...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    event.preventDefault();
                    $.ajax({
                        url: '{{ route('uster.new_request.stuffing.stuffing_plan.storeStuffing') }}',
                        type: 'POST',
                        data: form,
                        dataType: 'json',
                        success: function(response) {
                            if (response.msg === 'OK') {
                                // Display a success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data Berhasil Di Simpan',
                                });
                                // Redirect to the route named 'sdsa'
                                window.location.href =
                                    '{{ route('uster.new_request.stuffing.stuffing_plan.view') }}' +
                                    '?no_req=' + response.no_req_s;
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Menambahkan Stuffing',
                                });

                            }
                        },
                        error: function(xhr, status, error) {
                            var msg = xhr.responseJSON?.message
                            // Handle any errors that occur during the AJAX request
                            Swal.fire({
                                icon: 'error',
                                title: 'An error occurred: ' + msg,
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


        function input_success(res) {
            if (res.status != 200) {
                input_error(res)
                return false
            }

            $.toast({
                heading: 'Berhasil!',
                text: res.message,
                position: 'top-right',
                icon: 'success',
                hideAfter: 5000,
            });
        }

        function input_error(err) {
            console.log(err)
            $.toast({
                heading: 'Gagal menyimpan data!',
                text: err.message,
                position: 'top-right',
                icon: 'error',
                hideAfter: 5000,
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#EMKL').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.getPmbByName') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.nm_pbm,
                                text: item.nm_pbm,
                                ...item
                            }))
                        };
                    }
                }
            });

            $('#EMKL').on('select2:select', function(e) {
                var data = e.params.data;
                $("#ID_EMKL").val(data.kd_pbm);
                $("#PENUMPUKAN").val(data.nm_pbm);
                $("#ID_PENUMPUKAN").val(data.kd_pbm);
                $("#ALMT_PENUMPUKAN").val(data.almt_pbm);
                $("#NPWP_PENUMPUKAN").val(data.no_npwp_pbm);
                $("#ACC_EMKL").val(data.no_account_pbm);
                return false;
            })
            $('#EMKL').on('select2:clear', function(e) {
                $form[0].reset()
            })
        });

        $(document).ready(function() {
            $('#NM_KAPAL').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.master_vessel_palapa') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.vessel,
                                text: item.vessel + ' ' + item.voyage_in + ' | ' + item
                                    .voyage_out,
                                ...item
                            }))
                        };
                    }
                }
            });

            $('#NM_KAPAL').on('select2:select', function(e) {
                var data = e.params.data;
                $("#KD_KAPAL").val(data.vessel_code);
                $("#VOYAGE_IN").val(data.voyage_in);
                $("#VOYAGE_OUT").val(data.voyage_out);
                $("#NM_AGEN").val(data.operator_name);
                $("#KD_AGEN").val(data.operator_id);
                $("#NO_UKK").val(data.id_vsb_voyage);
                console.log(data.id_vsb_voyage);
                if (data.vessel_code && data.id_vsb_voyage) {
                    $("#NO_BOOKING").val(`BP${data.vessel_code}${data.id_vsb_voyage}`);
                }
                $("#TGL_BERANGKAT").val(data.atd)
                $("#TGL_STACKING").val(data.open_stack)
                $("#TGL_MUAT").val(data.closing_time_doc)
                $("#POL").val(data.id_pol);
                $("#POD").val(data.id_pod);
                $("#KD_PELABUHAN_ASAL").val(data.id_pol)
                $("#KD_PELABUHAN_TUJUAN").val(data.id_pod);
                $("#NM_PELABUHAN_ASAL").val(data.pol)
                $("#NM_PELABUHAN_TUJUAN").val(data.pod);
                $("#CALL_SIGN").val(data.call_sign);
                $("#ETD").val(data.etd)
                $("#ETA").val(data.eta)
                $("#OPEN_STACK").val(data.open_stack)
                $("#CONT_LIMIT").val(data.container_limit)
                $("#VOYAGE").val(data.voyage);
                return false;
            })
            $('#NM_KAPAL').on('select2:clear', function(e) {
                $form[0].reset()
            })
        });

        $(document).ready(function() {
            $('#PNKN_BY').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.getPmbByName') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.nm_pbm,
                                text: item.nm_pbm,
                                ...item
                            }))
                        };
                    }
                }
            });

            $('#PNKN_BY').on('select2:select', function(e) {
                var data = e.params.data;
                $("#ID_PNKN_BY").val(data.kd_pbm);
                $("#ACC_PNKN").val(data.no_account_pbm);
                return false;
            })
            $('#PNKN_BY').on('select2:clear', function(e) {
                $form[0].reset()
            })
        });
    </script>
@endsection
