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
                    <h3><b>Send Delivery TPK</b></h3>

                    <form action="javascript:void" id="requestGateIn" novalidate>
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NO REQUEST : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="NO_REQ" name="NO_REQ" class="suggestuwriter"
                                        type="text">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">JENIS : </label>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" name="JENIS_REQ" id="JENIS_REQ" class="suggestuwriter">
                                        <option value="STRIPPING">STRIPPING</option>
                                        <option value="PERP_STRIP">PERP_STRIP</option>
                                        <option value="STUFFING">STUFFING</option>
                                        <option value="DELIVERY">DELIVERY</option>
                                        <option value="BATAL_MUAT">BATAL_MUAT</option>
                                    </select>

                                </div>
                            </div>

                        </div>
                        <button type="submit" class="btn btn-info" onclick="postData()">Simpan Data</button>
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
        function postData() {
            var NO_REQ = $("#NO_REQ").val();
            var JENIS_REQ = $("#JENIS_REQ").val();

            // Check if the fields are not empty
            if (NO_REQ.trim() === '' || JENIS_REQ.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all the fields!',
                });
                return;
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: 'Check Apakah Request Sudah Lunas ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('uster.maintenance.send_delivery_tpk.checklunas') }}",
                        contentType: "application/json",
                        data: JSON.stringify({
                            "ID_REQUEST": NO_REQ,
                            "JENIS": JENIS_REQ,
                            "BANK_ACCOUNT_NUMBER": '',
                            "PAYMENT_CODE": ''
                        }),
                        dataType: "json",
                        processData: false,
                        success: function(response) {
                            jsonResponse = response;
                            // Check the response code
                            if (jsonResponse.code === "0") {
                                // Show an error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: jsonResponse.msg,
                                });
                            } else {
                                // Handle success jsonResponse here
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: jsonResponse.msg,
                                    willClose: () => {
                                        sendPraya();
                                    }
                                });
                                console.log(jsonResponse);
                            }
                        },
                        error: function(error) {
                            console.log("Error:", error);
                            // Handle error response here
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    });
                }
            });
        }

        function sendPraya() {
            var NO_REQ = $("#NO_REQ").val();
            var JENIS_REQ = $("#JENIS_REQ").val();

            // Check if the fields are not empty
            if (NO_REQ.trim() === '' || JENIS_REQ.trim() === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all the fields!',
                });
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: 'Request Sudah lunas , Apakah Yakin Ingin Di Kirim Ke Praya',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        type: "POST",
                        url: "{{ route('uster.maintenance.send_delivery_tpk.save_payment_external') }}",
                        contentType: "application/json",
                        data: JSON.stringify({
                            "ID_REQUEST": NO_REQ,
                            "JENIS": JENIS_REQ,
                            "BANK_ACCOUNT_NUMBER": '',
                            "PAYMENT_CODE": ''
                        }),
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Processing...',
                                text: 'Please wait while we send your request.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            jsonResponse = response;
                            // Check the response code
                            if (jsonResponse.code === "0") {
                                // Show an error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: jsonResponse.msg,
                                });
                            } else {
                                // Handle success jsonResponse here
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: jsonResponse.msg,
                                });
                            }
                        },
                        error: function(error) {
                            console.log("Error:", error);
                            // Handle error response here
                            let errorMsg = 'Something went wrong!';
                            if (error.responseJSON && error.responseJSON.msg) {
                                errorMsg = error.responseJSON.msg;
                            } else if (error.responseText) {
                                try {
                                    let errObj = JSON.parse(error.responseText);
                                    if (errObj.msg) errorMsg = errObj.msg;
                                } catch (e) {}
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: errorMsg,
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
