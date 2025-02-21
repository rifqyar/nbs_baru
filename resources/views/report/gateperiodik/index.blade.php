@extends('layouts.app')

@section('title')
    Report Gate Periodik
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
            <h3 class="text-themecolor">Gate Per Periodik</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</li>
                <li class="breadcrumb-item active">Gate Per Periodik</li>
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
                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-md-12 col-lg-5">
                                                <div class="form-group">
                                                    <label for="start_date">Periode Gate <small
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
                                                    <label for="end_date"> &nbsp; </label>
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
                                                    <label for="option_kegiatan">Jenis Kegiatan <small
                                                            class="text-danger">*</small></label>
                                                    <select name="option_kegiatan" id="option_kegiatan"
                                                        class="form-control form-select" required
                                                        onchange="change_visible()">
                                                        <option value="" selected="selected" disabled hidden> ---
                                                            Pilih Jenis Kegiatan
                                                            --- </option>
                                                        <option value="GATI"> GATE IN </option>
                                                        <option value="GATO"> GATE OUT </option>
                                                        <option value="ALL"> ALL </option>
                                                    </select>
                                                    <div class="invalid-feedback">Harap Pilih Jenis Kegiatan</div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group">
                                                    <label for="lokasi">Lokasi Gate <small
                                                            class="text-danger">*</small></label>
                                                    <select name="lokasi" id="lokasi" class="form-control form-select"
                                                        required onchange="change_visible()">
                                                        <option value="" selected="selected" disabled hidden> ---
                                                            Pilih Lokasi Gate ---
                                                        </option>
                                                        <option value="08"> Gate 08 </option>
                                                        <option value="06"> Gate 06 </option>
                                                        <option value="03"> Gate 03 </option>
                                                        <option value="ALL"> ALL </option>
                                                    </select>
                                                    <div class="invalid-feedback">Harap Pilih Lokasi Gate</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group m-b-40">
                                                    <label for="shift">Shift <small class="text-danger">*</small></label>
                                                    <select name="shift" id="shift" class="form-control form-select"
                                                        required>
                                                        <option value="" selected="selected" disabled hidden> -- Pilih
                                                            -- </option>
                                                        <option value="1"> Shift 1 (07.00 s/d 15.30) </option>
                                                        <option value="2"> Shift 2 (15.30 s/d 23.00)</option>
                                                        <option value="4"> Shift 3 (23.00 s/d 07.00)</option>
                                                        <option value="3"> Kegiatan 1 Hari (00.00 s/d 23.59)</option>
                                                        <option value="ALL"> ALL </option>
                                                    </select>
                                                    <div class="invalid-feedback">Harap Pilih Shift</div>
                                                </div>
                                            </div>

                                            <div class="col-md-12 col-lg-6">
                                                <div class="form-group m-b-40">
                                                    <label for="size">Size <small class="text-danger">*</small></label>
                                                    <select name="size" id="size" class="form-control form-select"
                                                        required>
                                                        <option value="" selected="selected" disabled hidden> -- Pilih
                                                            -- </option>
                                                        <option value="20"> 20" </option>
                                                        <option value="40"> 40" </option>
                                                        <option value="45"> 45" </option>
                                                        <option value="ALL"> ALL </option>
                                                    </select>
                                                    <div class="invalid-feedback">Harap Pilih Container Size</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 col-lg-6">
                                        <div class="row justify-content-center align-items-end">
                                            <div class="col-md-12 col-lg-4">
                                                <div class="form-group">
                                                    <label for="status">Status <small
                                                            class="text-danger">*</small></label>
                                                    <select name="status" id="status"
                                                        class="form-control form-select" required>
                                                        <option value="" selected="selected" disabled hidden> --
                                                            Pilih -- </option>
                                                        <option value="MTY"> MTY </option>
                                                        <option value="FCL"> FCL </option>
                                                        <option value="LCL"> LCL </option>
                                                        <option value="ALL"> ALL </option>
                                                    </select>
                                                    <div class="invalid-feedback">Harap Pilih Status</div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-lg-8">
                                                <div class="form-group">
                                                    <label for="NM_KAPAL">Nama Kapal</label>
                                                    <input type="text" class="form-control" id="NM_KAPAL"
                                                        name="NM_KAPAL" placeholder="Masukan Nama Kapal / Voyage In">
                                                    <input type="hidden" name="NO_UKK" id="NO_UKK" />
                                                    <input type="hidden" name="VOYAGE" id="VOYAGE" />
                                                    <input type="hidden" name="NO_BOOKING" id="NO_BOOKING" />
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
                                    <h5>Data Gate Per Periodik</h5>
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
    <script src="{{ asset('pages/report/gateperiodik.js') }}"></script>
@endpush
