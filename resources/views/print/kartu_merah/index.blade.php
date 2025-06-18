@extends('layouts.app')

@section('title')
    Cetak Kartu Receiving
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Cetak Kartu Receiving</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Print</li>
                <li class="breadcrumb-item active">Cetak Kartu Receiving</li>
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
                    @if (Session::has('error'))
                        <div class="alert alert-danger fade show" role="alert">
                            <h4 class="mb-0 text-center">
                                <span class="text-size-5">
                                    {!! Session::get('error') !!}
                                </span>
                                <i class="fas fa-exclamation-circle"></i>
                            </h4>
                        </div>
                    @endif

                    <div class="card card-secondary">
                        <div class="card-body">
                            <div class="card-title">
                                <h4>Form Pencarian</h4>
                            </div>
                            <form action="javascript:void(0)" method="GET" id="search-data" class="m-t-40" novalidate>
                                <input type="hidden" name="search" value="0">
                                <div class="row align-items-start">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="no_request">No. Request</label>
                                            <input type="text" name="no_request" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="start_date">Tanggal Request</label>
                                                    <input type="text" class="form-control" name="tgl_awal"
                                                        id="start_date">
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-12 text-center hidden-lg-down">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <small>-</small>
                                                </div>
                                            </div>
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="end_date"> &nbsp;</label>
                                                    <input type="text" class="form-control" name="tgl_akhir"
                                                        id="end_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3" onclick="resetSearch()">
                                        Reset Pencarian
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="submit" data-action="search" class="btn btn-rounded btn-info mr-3">
                                        Cari Data
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section">
                        <div class="card-body">
                            <div class="card-title m-b-40">
                                <h5>List Data</h5>
                            </div>

                            <div id="data-body">
                                @include('print.kartu_merah.dataList')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}">
    </script>
    <script src="{{ asset('pages/print/kartu_merah.js') }}"></script>
@endpush
