@extends('layouts.app')

@section('title')
    Batal Container Stuffing
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
    <style>
        .ui-autocomplete-loading {
            background: white url("/assets/images/animated_loading.gif") right center no-repeat;
            background-repeat: no-repeat;
            background-position: center right calc(.375em + .1875rem);
            padding-right: calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Koreksi</a></li>
                <li class="breadcrumb-item active">Batal Container Stuffing</li>
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
                    <h3><b>Batal Container Stripping</b></h3>
                    <form action="javascript:void(0)" class="form-horizontal m-t-20" id="dataCont" novalidate>
                        @csrf
                        <div class="row">
                            <div class="col-lg-4 col-md-12">
                                <div class="form-group">
                                    <label for="">No. Container</label>
                                    <input type="text" class="form-control" name="NO_CONT" id="NO_CONT" required>
                                    <div class="invalid-feedback">
                                        Harap masukan nomor container
                                    </div>
                                    <input type="hidden" id="ASAL_CONT" />
                                    <input type="hidden" id="STUFFING_DARI" />
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-start">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label>Tgl. Mulai</label>
                                    <input type="date" class="form-control" name="TGL_MULAI" id="TGL_MULAI">
                                </div>

                                <div class="form-group">
                                    <label>SIZE</label>
                                    <input type="text" class="form-control" name="SIZE" id="SIZE" readonly>
                                </div>

                                <div class="form-group">
                                    <label>KOMODITI</label>
                                    <input type="text" class="form-control" name="KOMODITI" id="KOMODITI" readonly>
                                </div>

                                <div class="form-group">
                                    <label>JENIS STUFFING</label>
                                    <input type="text" class="form-control" name="TYPE" id="TYPE"
                                        readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tgl. Request Stuffing</label>
                                    <input type="text" class="form-control" name="TGL_REQUEST" id="TGL_REQUEST" readonly>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label>VIA</label>
                                    <input type="text" class="form-control" name="VIA" id="VIA" readonly>
                                </div>

                                <div class="form-group">
                                    <label>HZ</label>
                                    <input type="text" class="form-control" name="HZ" id="HZ" readonly>
                                </div>

                                <div class="form-group">
                                    <label>No. Request Stuffing</label>
                                    <input type="text" class="form-control" name="NO_REQ_STUFF" id="NO_REQ_STUFF"
                                        readonly>
                                </div>
                            </div>


                            <input id="NO_REQ_DEL" name="NO_REQ_DEL" type="hidden" />
                            <input id="NO_REQ_ICT" name="NO_REQ_ICT" type="hidden" />
                            <input id="KD_KOMODITI" name="KD_KOMODITI" type="hidden" />
                            <input id="NO_BOOKING" name="NO_BOOKING" type="hidden" />
                            <input id="NO_UKK" name="NO_UKK" type="hidden" />
                            <input id="NO_SEAL" name="NO_SEAL" type="hidden" />
                            <input id="BERAT" name="BERAT" type="hidden" />
                            <input id="KETERANGAN" name="KETERANGAN" type="hidden" />
                        </div>
                        <div class="d-flex">
                            <button type="submit" class="btn btn-info">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}">
    </script>
    <script src="{{ asset('pages/koreksi/batalstuffing.js') }}"></script>
@endpush
