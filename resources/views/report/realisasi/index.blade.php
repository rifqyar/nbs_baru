@extends('layouts.app')

@section('title')
    Laporan Realisasi Stuffing Stripping
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Laporan Realisasi Stuffing Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active">Realisasi Stuffing & Stripping</li>
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
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="start_date">Tanggal Awal <small
                                                            class="text-danger">*</small></label>
                                                    <input type="text" class="form-control" name="tgl_awal"
                                                        id="start_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Awal</div>
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
                                                        id="end_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Akhir</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="option_kegiatan">Kegiatan <small
                                                    class="text-danger">*</small></label>
                                            <select name="option_kegiatan" id="option_kegiatan"
                                                class="form-control form-select" required>
                                                <option value="" hidden selected> -- Pilih Kegiatan -- </option>
                                                <option value="STRIPPING"> Stripping </option>
                                                <option value="STUFFING"> Stuffing </option>
                                            </select>
                                            <div class="invalid-feedback">Harap Pilih Kegiatan</div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 col-lg-12">
                                        <div class="row justify-content-center align-items-center">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="id_menu1">Pengurutan Berdasarkan</label>
                                                    <select id="id_menu1" name="menu1" multiple="multiple"
                                                        class="form-control form-select">
                                                        <option value="NO_CONTAINER">No. Container</option>
                                                        <option value="NO_REQUEST">No. Request</option>
                                                        <option value="TGL_REQUEST">Tgl. Request</option>
                                                        <option value="TGL_REALISASI">Tgl. Realisasi</option>
                                                        <option value="SIZE_">Size</option>
                                                        <option value="TYPE_">Type</option>
                                                        <option value="NM_PBM">Nama Consignee</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-12">
                                                <button class="btn btn-info btn-sm btn-block" type="button" id="sort_asc">
                                                    <i class="mdi mdi-arrow-right-bold-circle"></i>
                                                    ASC
                                                </button>

                                                <button class="btn btn-info btn-sm btn-block" type="button" id="sort_desc">
                                                    <i class="mdi mdi-arrow-right-bold-circle"></i>
                                                    DESC
                                                </button>

                                                <button class="btn btn-warning btn-sm btn-block" type="button"
                                                    id="clear_data">
                                                    <i class="mdi mdi-reload"></i>
                                                    Reload
                                                </button>
                                            </div>
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="id_menu2"> &nbsp;</label>
                                                    <select id="id_menu2" name="menu2[]" multiple="multiple"
                                                        class="form-control form-select">

                                                    </select>
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
    <script src="{{ asset('pages/report/realisasi.js') }}"></script>
@endpush
