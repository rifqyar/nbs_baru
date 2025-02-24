@extends('layouts.app')

@section('title')
    NBS | Batal Request
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Request Batal SPPS</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item active">Batal SPPS</li>
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
                    <div class="card" id="data-section">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="card-title m-b-40">
                                    <h5>Data Request Receiving</h5>
                                </div>

                                <div class="ml-auto">
                                    <div class="float-end">
                                        <!-- Tombol Tambah -->
                                        <a href="{{route('uster.new_request.batal_spps.add')}}"
                                            class="btn btn-icon icon-left btn-outline-info rounded-pill "
                                            style="margin-right: 10px">
                                             Batal SPPS <i class="mdi mdi-plus-circle-outline"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive px-3">
                                <table class="display nowrap table data-table" cellspacing="0" width="100%" id="example">
                                    <thead >
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">No. BA</th>
                                            <th class="text-center">No. Req SPPS</th>
                                            <th class="text-center">No. Container</th>
                                            <th class="text-center">Tgl. Batal SPPS</th>
                                            <th class="text-center">Vessel</th>
                                            <th class="text-center">Voyage In</th>
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
    <script src="{{asset('pages/request/batalSpps/batalspps.js')}}"></script>
@endpush
