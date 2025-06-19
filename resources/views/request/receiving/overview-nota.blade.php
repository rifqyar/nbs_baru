@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Receiving
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Overview Request Receiving</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.receiving.receiving_luar')}}">Receiving</a></li>
                <li class="breadcrumb-item active">Overview Data</li>
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
                    <a href="{{route('uster.new_request.receiving.receiving_luar')}}" type="button" class="btn btn-outline-warning btn-rounded mb-4">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <span>Kembali</span>
                    </a>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <h4 class="">Data Request</h4>
                            </div>

                            <div class="row align-items-center">
                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Nomor Request
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->no_request}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Penerima/Consignee
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->consignee}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Alamat
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->almt_pbm}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    NPWP
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->no_npwp_pbm}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Keterangan
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->keterangan == null ? '-' : $request->keterangan}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="card-title m-b-40">
                                <h5>Data Container</h5>
                            </div>

                            <div class="table-responsive px-3">
                                <table class="display nowrap table" czllspacing="0" width="100%" id="example">
                                    <thead >
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">No. Container</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Ukuran</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">Berbahaya</th>
                                            <th class="text-center">Depo Tujuan</th>
                                            <th class="text-center">Owner</th>
                                            @if (!$overview)
                                                <th class="text-center">Action</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1 @endphp
                                        @foreach ($container as $cnt)
                                            <tr>
                                                <td class="text-center">{{$i++}}</td>
                                                <td class="text-center">{{$cnt->no_container}}</td>
                                                <td class="text-center">{{$cnt->status}}</td>
                                                <td class="text-center">{{$cnt->size_}}</td>
                                                <td class="text-center">{{$cnt->type_}}</td>
                                                <td class="text-center">{{$cnt->hz}}</td>
                                                <td class="text-center">{{$cnt->nama_yard}}</td>
                                                <td class="text-center">{{$cnt->kd_owner}}</td>
                                                @if (!$overview)
                                                    <td class="text-center">
                                                        <button class="btn btn-rounded btn-danger">
                                                            <i class="mdi mdi-delete h5"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
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
    <script src="{{asset('pages/request/receiving/receiving.js')}}"></script>
@endpush
