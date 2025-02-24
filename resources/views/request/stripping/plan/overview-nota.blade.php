@extends('layouts.app')

@section('title')
    Perencanaan Stripping Petikemas
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Overview Perencanaan Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.stripping.stripping_plan.awal_tpk')}}">Stripping Plan</a></li>
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
                    <a href="{{route('uster.new_request.stripping.stripping_plan.awal_tpk')}}" type="button" class="btn btn-outline-warning btn-rounded mb-4">
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
                                    <b class="mr-3">{{$request->no_request}}</b>
                                    <b class="strong mr-3">|</b>
                                    {{$no_req2}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Penerima/Consignee
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    <b class="strong">{{$request->nama_pemilik}}</b>
                                    {{-- <b class="mr-3"> | </b> --}}
                                    {{-- {{$request->nama_penumpuk}} --}}
                                </div>

                               <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    No D.O
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->no_do}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    No. B.L
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->no_bl}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Nomor SPPB
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->no_sppb != '' ? $request->no_sppb : ' - '}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Tanggal SPPB
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->tgl_sppb != '' ? $request->tgl_sppb : ' - '}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Type Stripping
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->type_stripping}}
                                </div>

                                <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                    Keterangan
                                </div>
                                <div class="col-md-1 col-1 mb-2">
                                    :
                                </div>
                                <div class="col-md-8 col-12 mb-2">
                                    {{$request->keterangan != '' ? $request->keterangan : ' - '}}
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
                                            <th class="text-center">Size / Type</th>
                                            <th class="text-center">Asal Cont</th>
                                            <th class="text-center">Tgl. Bonkar</th>
                                            <th class="text-center">Tgl. Mulai</th>
                                            <th class="text-center">Tgl. App Mulai</th>
                                            <th class="text-center">Tgl. Selesai</th>
                                            <th class="text-center">Tgl. App Selesai</th>
                                            <th class="text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1 @endphp
                                        @foreach ($container as $cnt)
                                            <tr>
                                                <td class="text-center">{{$i++}}</td>
                                                <td class="text-center">{{$cnt->no_container}}</td>
                                                <td class="text-center">
                                                    @if($cnt->asal_cont == "TPK")
                                                        {{$cnt->kd_size}} / {{$cnt->kd_type}}
                                                    @else
                                                        {{$cnt->ukuran}} / {{$cnt->type}}
                                                    @endif
                                                </td>
                                                <td class="text-center">{{$cnt->asal_cont}}</td>
                                                <td class="text-center">{{\Carbon\Carbon::parse($cnt->tgl_bongkar)->translatedFormat('d-M-Y')}}</td>
                                                <td class="text-center">{{\Carbon\Carbon::parse($cnt->tgl_mulai)->translatedFormat('d-M-Y')}}</td>
                                                <td class="text-center">{{\Carbon\Carbon::parse($cnt->tgl_approve)->translatedFormat('d-M-Y')}}</td>
                                                <td class="text-center">{{\Carbon\Carbon::parse($cnt->tgl_selesai)->translatedFormat('d-M-Y')}}</td>
                                                <td class="text-center">{{\Carbon\Carbon::parse($cnt->tgl_app_selesai)->translatedFormat('d-M-Y')}}</td>
                                                <td class="text-center">{{$cnt->remark}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    {{-- @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                        <tfoot>
                                            <tr>
                                                <td colspan="10">
                                                    <div class="d-flex justify-content-end">
                                                        @if ($closing == 'CLOSED')
                                                            <button class="btn btn-warning" onclick="unsave_request()">Unsave</button>
                                                        @else
                                                            <button class="btn btn-success" onclick="save_request()">Save</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    @endif --}}
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
    <script src="{{asset('pages/request/stripping/stripping_plan.js')}}"></script>
@endpush
