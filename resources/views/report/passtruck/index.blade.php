@extends('layouts.app')

@section('title')
    Laporan Pass Truck
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Laporan Pass Truck</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item">Produksi</li>
                <li class="breadcrumb-item active">Pass Truck</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p></h6>
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
                                <h4>Form Pencarian Laporan Pass Truck</h4>
                            </div>
                            <form action="javascript:void(0)" method="GET" id="form_pass_truck" class="m-t-40" novalidate>
                                <div class="row align-items-start" id="search-data">
                                    <!-- Tanggal Awal & Tanggal Akhir -->
                                    <div class="col-lg-6 col-md-12">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="start_date">Periode Kegiatan <small class="text-danger">*</small></label>
                                                    <input type="text" class="form-control" name="tgl_awal" id="start_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Awal</div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-12 text-center hidden-lg-down">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <small>s/d</small>
                                                </div>
                                            </div>
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="end_date">&nbsp;</label>
                                                    <input type="text" class="form-control" name="tgl_akhir" id="end_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Akhir</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Opsi Kegiatan -->
                                    <div class="col-lg-6 col-md-12">
                                        <div class="form-group">
                                            <label for="option_kegiatan">Kegiatan <small class="text-danger">*</small></label>
                                            <select name="option_kegiatan" id="option_kegiatan" class="form-control form-select" required>
                                                <option value="ALL" selected>All</option>
                                                <option value="RECEIVING">Receiving</option>
                                                <option value="DELIVERY">Delivery</option>
                                                <option value="STRIPPING">Stripping</option>
                                                <option value="STUFFING">Stuffing</option>
                                            </select>
                                            <div class="invalid-feedback">Harap Pilih Kegiatan</div>
                                        </div>
                                    </div>

                                    <!-- Sorting / Pilih Pengurutan -->
                                    <div class="col-md-12 col-lg-12">
                                        <div class="row justify-content-center align-items-center">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="id_menu1">FIELDS</label>
                                                    <select id="id_menu1" name="menu1" multiple="multiple" class="form-control form-select" style="height: 120px;">
                                                        <option value="NO_REQUEST">NO. Request</option>
                                                        <option value="TANGGAL">Tanggal</option>
                                                        <option value="NO_NOTA">No. Nota</option>
                                                        <option value="KEGIATAN">Kegiatan</option>
                                                        <option value="JUMLAH_PASS">Jumlah Pass</option>
                                                        <option value="BIAYA">Biaya</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col-md-12 text-center">
                                                <button class="btn btn-info btn-sm btn-block mb-1" type="button" id="sort_asc">
                                                    <i class="mdi mdi-arrow-right-bold-circle"></i> ASC
                                                </button>
                                                <button class="btn btn-info btn-sm btn-block mb-1" type="button" id="sort_desc">
                                                    <i class="mdi mdi-arrow-right-bold-circle"></i> DESC
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-block" type="button" id="clear_data">
                                                    <i class="mdi mdi-reload"></i> RESET
                                                </button>
                                            </div>
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-group">
                                                    <label for="id_menu2">SORT BY</label>
                                                    <select id="id_menu2" name="menu2[]" multiple="multiple" class="form-control form-select" style="height: 120px;">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-secondary btn-rounded mr-2" onclick="resetSearch()">
                                        Reset <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="button" id="btn_generate" class="btn btn-rounded btn-info mr-2">
                                        Generate Report <i class="mdi mdi-settings"></i>
                                    </button>
                                    <button type="button" id="btn_export" class="btn btn-success btn-rounded">
                                        Generate Excel <i class="mdi mdi-file-excel"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Result Section -->
                    <div class="card mt-4" id="data-section" style="display: none">
                        <div class="card-body">
                            <div class="card-title mb-3">
                                <h5>Hasil Laporan Pass Truck</h5>
                            </div>
                            <div class="alert alert-info py-2 font-weight-bold" style="font-size: 16px;">
                                <span class="mr-4">TOTAL PASS : <span id="lbl_total_pass" class="badge badge-primary px-3 py-1 font-weight-bold">0</span></span>
                                <span>TOTAL BIAYA : Rp <span id="lbl_total_biaya" class="badge badge-success px-3 py-1 font-weight-bold">0</span></span>
                            </div>
                            <div id="data-body" class="table-responsive">
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
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}"></script>
    <script src="{{ asset('pages/report/passtruck.js') }}"></script>
@endpush
