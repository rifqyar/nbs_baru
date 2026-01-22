@extends('layouts.app')

@section('title')
    Report Nota Periodik
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Nota Per Periodik</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active">Nota Per Periodik</li>
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
                                <div class="row align-items-start">
                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end" id="search-data">
                                            <div class="col-md-12 col-lg-5">
                                                <div class="form-group">
                                                    <label for="start_date">Tanggal Awal <small
                                                            class="text-danger">*</small></label>
                                                    <input type="text" class="form-control" name="tgl_awal"
                                                        id="start_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Awal</div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-2 text-center hidden-lg-down">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <small>-</small>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-5">
                                                <div class="form-group">
                                                    <label for="end_date">Tanggal Akhir <small
                                                            class="text-danger">*</small></label>
                                                    <input type="text" class="form-control" name="tgl_akhir"
                                                        id="end_date" required>
                                                    <div class="invalid-feedback">Harap Masukan Tanggal Akhir</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label for="pembayaran">Pembayaran</label>
                                                    <select name="pembayaran" id="pembayaran"
                                                        class="form-control form-select">
                                                        <option value=""> Semua </option>
                                                        <option value="BANK">Bank</option>
                                                        <option value="CASH">Cash</option>
                                                        <option value="AUTODB">Autodb</option>
                                                        <option value="DEPOSIT">Deposit</option>
                                                    </select>
                                                    <span class="bar"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label for="status_bayar">Status Pembayaran</label>
                                                    <select name="status_bayar" id="status_bayar"
                                                        class="form-control form-select">
                                                        <option value=""> Semua </option>
                                                        <option value="YES">Lunas</option>
                                                        <option value="NO">Belum Lunas</option>
                                                    </select>
                                                    <span class="bar"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div class="form-group m-b-40">
                                            <label for="corporatetype">Pilih Perusahaan</label>
                                            <select name="corporatetype" id="corporatetype"
                                                class="form-control form-select">
                                                <option value="IPC">PT. PELABUHAN INDONESIA II</option>
                                                <option value="IPCTPK">PT. IPC TERMINAL PETIKEMAS</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label for="option_kegiatan">Kegiatan</label>
                                                    <select name="jenis" id="option_kegiatan"
                                                        class="form-control form-select">
                                                        <option value=""> Semua </option>
                                                        <option value="RECEIVING"> Receiving </option>
                                                        <option value="DELIVERY"> Delivery </option>
                                                        <option value="STRIPPING"> Stripping </option>
                                                        <option value="STUFFING"> Stuffing </option>
                                                    </select>
                                                    <span class="bar"></span>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label for="status_nota">Status Nota</label>
                                                    <select name="status_nota" id="status_nota"
                                                        class="form-control form-select">
                                                        <option value=""> Semua </option>
                                                        <option value="NEW"> New </option>
                                                        <option value="BATAL"> Batal </option>
                                                    </select>
                                                    <span class="bar"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3"
                                        onclick="resetSearch()">
                                        Reset Pencarian
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="submit" class="btn btn-rounded btn-info">
                                        Generate Nota
                                        <i class="mdi mdi-settings"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section" style="display: none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="card-title m-b-40">
                                    <h5>Data Nota Periodik</h5>
                                </div>
                                <div class="flex-row-reverse">
                                    <button class="btn btn-info" onclick="exportToExcel()">
                                        <i class="mdi mdi-file-export"></i>
                                        Export to Excel
                                    </button>
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
    <script src="{{ asset('pages/report/notaperiodik.js') }}?v={{ filemtime(public_path('pages/report/notaperiodik.js')) }}"></script>
@endpush
