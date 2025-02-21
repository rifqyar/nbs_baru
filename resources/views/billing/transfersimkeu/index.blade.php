@extends('layouts.app')

@section('title')
    Transfer SIMKEU
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
@endpush

@section('content')

    @if(Session::has("notifCekPaid"))
        <div class="alert alert-danger fade show" role="alert">
            <h4 class="mb-0 text-center">
                <span class="text-size-5">
                    {!! Session::get("notifCekPaid") !!}
                </span>
                <i class="fas fa-exclamation-circle"></i>
            </h4>
        </div>
    @endif

    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Transfer SIMKEU</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Billing</a></li>
                <li class="breadcrumb-item active">Transfer SIMKEU</li>
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
                            <form action="javascript:void(0)" id="transfer-simkeu" class="m-t-40 needs-validation" novalidate>
                                @csrf
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="col-12 col-md-12">
                                            <div class="form-group">
                                                <div class="row justify-content-center align-items-end">
                                                    <div class="col-5">
                                                        <label for="start_date">Periode Kegiatan</label>
                                                        <input type="text" class="form-control" name="tgl_awal" id="start_date">
                                                        <span class="bar"></span>
                                                    </div>
                                                    <div class="col-2 text-center">
                                                        <label>&nbsp;</label>
                                                        <small>-</small>
                                                    </div>
                                                    <div class="col-5">
                                                        <label for="end_date">&nbsp;</label>
                                                        <input type="text" class="form-control" name="tgl_akhir" id="end_date">
                                                        <span class="bar"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-12">
                                            <div class="form-group m-b-40">
                                                <label for="no_nota">No. Nota</label>
                                                <input type="text" class="form-control" name="no_nota" style="text-transform:uppercase" id="no_nota">
                                                <span class="bar"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3" onclick="generateNota()">
                                        Generate Nota
                                        <i class="mdi mdi-settings"></i>
                                    </button>
                                    <button type="submit" class="btn btn-rounded btn-info">
                                        Transfer SIMKEU
                                        <i class="mdi mdi-send"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section" style="display: none">
                        <div class="card-body" id="data-body">

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
    <script src="{{asset('pages/billing/transfersimkeu.js')}}"></script>
@endpush
