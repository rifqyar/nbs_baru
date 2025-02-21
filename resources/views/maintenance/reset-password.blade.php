@extends('layouts.app')

@section('title')
    Reset Password
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
                <li class="breadcrumb-item"><a href="{{ route('maintenance.changepasswd/') }}">Reset Password</a>
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
                    <h3><b>Reset Password</b></h3>

                    <form action="javascript:void" id="requestGateIn" novalidate>
                        @csrf
                        <div class="p-3">

                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">New Password : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="password" name="password" type="password">
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Confirm Password : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="conf_passwd" name="conf_passwd" type="password">
                                </div>
                            </div>

                        </div>
                        <button type="submit" class="btn btn-info" onclick="resetPassword()">Simpan Data</button>
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
        function resetPassword() {

            var password = $('#password').val();
            var conf_passwd = $('#conf_passwd').val();
            var url = '{{ route('maintenance.changepasswdStore') }}';

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
                        password: password,
                        password_confirmation: conf_passwd // Menggunakan CONF_PASSWD untuk konfirmasi password
                    }, function(data) {
                        if (data.success) { // Menggunakan data.success bukan response.success
                            Swal.fire({
                                icon: 'success',
                                title: 'Password berhasil diubah',
                                showConfirmButton: false,
                                timer: 1000
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 1000
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Anda membatalkan perubahan password',
                    });
                }
            });

        }
    </script>
@endsection
