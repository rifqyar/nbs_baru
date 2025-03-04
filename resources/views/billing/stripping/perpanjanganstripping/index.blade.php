@extends('layouts.app')

@section('title')
    Nota Perpanjangan Stripping
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
            <h3 class="text-themecolor">Nota Perpanjangan Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Billing</a></li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item active">Nota Perpanjangan Stripping</li>
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
                            <form action="javascript:void(0)" id="search-data" class="m-t-40">
                                @csrf
                                <input type="hidden" name="search" value="false">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="col-12 col-md-12">
                                            <div class="form-group m-b-40">
                                                <label for="no_request">No. Request</label>
                                                <input type="text" class="form-control" name="no_request" style="text-transform:uppercase" id="no_request">
                                                <span class="bar"></span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-12">
                                            <div class="form-group">
                                                <div class="row justify-content-center align-items-end">
                                                    <div class="col-5">
                                                        <label for="start_date">Periode Tanggal Request</label>
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
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3" onclick="resetSearch()">
                                        Reset Pencarian
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="submit" class="btn btn-rounded btn-info">
                                        Cari Data
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="card-title m-b-40">
                                    <h5>Data Nota Perpanjangan Stripping</h5>
                                </div>
                            </div>

                            <div class="table-responsive px-3">
                                <table class="display nowrap table data-table" cellspacing="0" width="100%" id="example">
                                    <thead >
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center" width="200px">Action</th>
                                            <th class="text-center">No. Request/Perp Dari</th>
                                            <th class="text-center">Tgl. Request</th>
                                            <th class="text-center">No. DO/BL</th>
                                            <th class="text-center">Penumpukan Oleh / Consignee</th>
                                            <th class="text-center">Jumlah Container</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
    <script src="{{asset('pages/billing/perpanjanganstripping.js')}}"></script>
@endpush
