@extends('layouts.app')

@section('title')
    Batal Container Stripping
@endsection

@push('after-style')
    <style>
        .ui-autocomplete-loading {
          background: white url("/assets/images/animated_loading.gif") right center no-repeat;
          background-repeat: no-repeat;
          background-position: center right calc(.375em + .1875rem);
          padding-right:  calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Koreksi</a></li>
                <li class="breadcrumb-item active">Batal Container Stripping</li>
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
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-start">
                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label>No. Request Stripping</label>
                                    <input type="text" class="form-control" name="NO_REQ" id="NO_REQ" readonly>
                                </div>

                                <div class="form-group">
                                    <label>SIZE</label>
                                    <input type="text" class="form-control" name="SIZE" id="SIZE" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Stripping Dari</label>
                                    <input type="text" class="form-control" name="STRIPPING_DARI" id="STRIPPING_DARI"
                                        readonly>
                                    <input type="hidden" readonly name="NO_REQUEST_RECEIVING" id="NO_REQUEST_RECEIVING" />
                                    <input type="hidden" readonly name="VIA" id="VIA" />
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-12">
                                <div class="form-group">
                                    <label>Tgl. Request Stripping</label>
                                    <input type="text" class="form-control" name="TGL_REQ" id="TGL_REQ" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Type</label>
                                    <input type="text" class="form-control" name="TYPE" id="TYPE" readonly>
                                </div>
                            </div>
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
    <script src="{{asset('pages/koreksi/batalstripping.js')}}"></script>
@endpush
