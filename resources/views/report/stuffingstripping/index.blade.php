@extends('layouts.app')

@section('title')
    Laporan Stuffing Stripping
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
            <h3 class="text-themecolor">Laporan Stuffing Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active">Stuffing & Stripping</li>
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
                    <div class="card card-secondary">
                        <div class="card-body">
                            <div class="card-title">
                                <h4>Form Pencarian</h4>
                            </div>
                            <form action="javascript:void(0)" method="GET" id="generate_nota" class="m-t-40" novalidate>
                                <div class="row align-items-start" id="search-data">
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="start_date">Tanggal Awal <small
                                                    class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="tgl_awal" id="start_date"
                                                required>
                                            <div class="invalid-feedback">Harap Masukan Tanggal Awal</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="option_kegiatan">Kegiatan</label>
                                            <select name="option_kegiatan" id="option_kegiatan"
                                                class="form-control form-select">
                                                <option value=""> All </option>
                                                <option value="STRIPPING"> Stripping </option>
                                                <option value="STUFFING"> Stuffing </option>
                                            </select>
                                            <div class="invalid-feedback">Harap Pilih Kegiatan</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="end_date">Tanggal Akhir <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="tgl_akhir" id="end_date"
                                                required>
                                            <div class="invalid-feedback">Harap Masukan Tanggal Akhir</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="status_req">Status Request</label>
                                            <select name="status_req" id="status_req" class="form-control form-select">
                                                <option value=""> All </option>
                                                <option value="NEW"> NEW </option>
                                                <option value="PERP"> PERPANJANGAN </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3" onclick="resetSearch()">
                                        Reset Pencarian
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="submit" data-action="generate" class="btn btn-rounded btn-info mr-3">
                                        Generate Nota
                                        <i class="mdi mdi-settings"></i>
                                    </button>
                                    <button type="submit" data-action="exportExcel" class="btn btn-primary btn-rounded">
                                        <i class="mdi mdi-file-export"></i>
                                        Export to Excel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section" style="display: none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="card-title m-b-40">
                                    <h5>List Data</h5>
                                </div>
                                <div class="flex-row-reverse">

                                </div>
                            </div>

                            <div id="data-body">

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
    <script src="{{ asset('pages/report/stuffingstripping.js') }}"></script>
@endpush
