@extends('layouts.app')

@section('title')
View Pconect
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
    <style>
        .ui-autocomplete-loading {
          background: white url("/assets/images/animated_loading.gif") right center no-repeat;
          margin-right: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">View Pconect</h3>
            
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
                    <a href="/maintenance/pconnect" type="button" class="btn btn-outline-warning btn-rounded mb-4">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <span>Kembali</span>
                    </a>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <h4 class="">Pconect Data</h4>
                            </div>
                                <div class="row justify-content-center align-items-start">
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="no_req">Nomor NPWP</label>
                                        <input type="text" name="no_npwp" class="form-control" readonly id="no_npwp" value="{{$request->npwp}}">
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="nama">Nama<small class="text-danger">*</small></label>
                                        <input type="text" name="no_npwp" class="form-control" readonly id="no_npwp" value="{{$request->nama}}">
                                    </div>
                                    <div class="col-md-12 col-12 mb-2">
                                        <label for="almt_consignee">Alamat</label>
                                        <textarea class="form-control" name="almt_consignee" id="almt_consignee" rows="3" readonly>{{$request->alamat}}</textarea>
                                    </div>

                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="npwp">Telepon</label>
                                        <input type="text" name="npwp_consignee" class="form-control" readonly id="npwp" value="{{$request->telepon}}">
                                    </div>

                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="no_req">Email</label>
                                        <input type="text" name="no_npwp" class="form-control" readonly id="no_npwp" value="{{$request->email}}">
                                    </div>
                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="no_req">Badan Usahan</label>
                                        <input type="text" name="no_npwp" class="form-control" readonly id="no_npwp" value="{{$request->badan_usaha}}">
                                    </div>
                                </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{asset('assets/plugins/moment/moment.js')}}"></script>
    <script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>
 
@endpush
