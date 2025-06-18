@extends('layouts.app')

@section('title')
    Perencanaan Stripping Petikemas
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
    <style>
        .ui-autocomplete-loading {
          background-image: url("/assets/images/animated_loading.gif");
          background-repeat: no-repeat;
          background-position: center right calc(.375em + .1875rem);
          padding-right:  calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Add Request Perencanaan Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.stripping.stripping_plan.awal_tpk')}}">Stripping Plan</a></li>
                <li class="breadcrumb-item active">Add Request</li>
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
                    <div class="card-title">
                        <h4>Form Add Request</h4>
                    </div>

                    <form action="javascript:void(0)" class="form-horizontal m-t-20" id="form-add" novalidate>
                        @csrf
                        <input type="hidden" name="type" value="add">

                        <input type="hidden" id="NO_BOOKING" name="NO_BOOKING"/>
                        <input type="hidden" id="IDVSB" name="IDVSB"/>
                        <input type="hidden" id="CALLSIGN" name="CALLSIGN"/>
                        <input type="hidden" id="VESSEL_CODE" name="VESSEL_CODE"/>
                        <input type="hidden" id="TANGGAL_JAM_TIBA" name="TANGGAL_JAM_TIBA"/>
                        <input type="hidden" id="TANGGAL_JAM_BERANGKAT" name="TANGGAL_JAM_BERANGKAT"/>
                        <input type="hidden" id="OPERATOR_NAME" name="OPERATOR_NAME"/>
                        <input type="hidden" id="OPERATOR_ID" name="OPERATOR_ID"/>
                        <input type="hidden" id="POD" name="POD"/>
                        <input type="hidden" id="POL" name="POL"/>
                        <input type="hidden" id="VOYAGE" name="VOYAGE"/>

                        <input type="hidden" name="ID_CONSIGNEE" id="ID_CONSIGNEE">
                        <input type="hidden" name="NO_ACC_CONS" id="NO_ACC_CONS" />
                        <input type="hidden" name="ALMT_CONSIGNEE" id="ALMT_CONSIGNEE" />
                        <input type="hidden" name="NPWP_CONSIGNEE" id="NPWP_CONSIGNEE" />

                        <div class="row justify-content-center align-items-start">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="CONSIGNEE">Penerima / Consignee <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="CONSIGNEE" id="CONSIGNEE" required style="text-transform: uppercase !important">
                                    <div class="invalid-feedback">Harap Masukan Penerima / Consignee</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="CONSIGNEE_PERSONAL">Nama Personal Consignee</label>
                                <input type="text" name="CONSIGNEE_PERSONAL" class="form-control" id="CONSIGNEE_PERSONAL">
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row align-items-start">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="NM_KAPAL">Nama Kapal <small class="text-danger">*</small></label>
                                            <input type="text" id="NM_KAPAL" class="form-control" name="NM_KAPAL" required>
                                            <div class="invalid-feedback">Harap Masukan Nama Kapal</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="VOYAGE_IN">&nbsp;</label>
                                            <input type="text" id="VOYAGE_IN" class="form-control" name="VOYAGE_IN">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                        <div class="form-group">
                                            <label for="VOYAGE_OUT">&nbsp;</label>
                                            <input type="text" id="VOYAGE_OUT" class="form-control" name="VOYAGE_OUT">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="NO_DO">Nomor D.O</label>
                                    <input type="text" class="form-control" name="NO_DO" id="NO_DO">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="NO_BL">Nomor B.L</label>
                                    <input type="text" class="form-control" name="NO_BL" id="NO_BL">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="NO_SPPB">Nomor SPPB</label>
                                    <input type="text" class="form-control" name="NO_SPPB" id="NO_SPPB">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="TGL_SPPB"> Tanggal SPPB </label>
                                    <input type="date" class="form-control" name="TGL_SPPB" id="TGL_SPPB">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="TYPE_S">Type Stripping</label>
                                    <select name="TYPE_S" id="TYPE_S" class="form-control form-select">
                                        <option value="D">DALAM NEGERI</option>
                                        <option value="I">LUAR NEGERI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 col-12">
                                <div class="form-group">
                                    <label for="keterangan">Keterangan</label>
                                    <input type="text" class="form-control" name="keterangan" id="KETERANGAN">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end m-t-40">
                            <a href="{{route('uster.new_request.stripping.stripping_plan.add')}}" class="btn btn-warning mr-3"> <i class="mdi mdi-chevron-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-info"><i class="mdi mdi-content-save"></i> Simpan </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{asset('assets/plugins/moment/moment.js')}}"></script>
    <script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>
    <script src="{{asset('pages/request/stripping/stripping_plan.js')}}"></script>
@endpush
