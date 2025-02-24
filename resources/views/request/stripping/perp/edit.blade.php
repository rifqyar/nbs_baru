@extends('layouts.app')

@section('title')
    Edit Perencanaan Stripping
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
            <h3 class="text-themecolor">Edit Perpanjangan Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.stripping.perpanjangan')}}">Perpanjangan Stripping</a></li>
                <li class="breadcrumb-item active">Edit Data</li>
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
                    <a href="{{route('uster.new_request.stripping.perpanjangan')}}" type="button" class="btn btn-outline-warning btn-rounded mb-4">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <span>Kembali</span>
                    </a>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <h4 class="">Data Request</h4>
                            </div>

                            <form action="javascript:void(0)" id="form-request" class="form-material">
                                <div class="row align-items-center">
                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nomor Request
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="no_request" id="" class="form-control" readonly value="{{$request->row_request->no_request}}">
                                        <input type="hidden" name="perp_dari" value="{{$request->row_request->perp_dari}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Penerima/Consignee
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="consignee" id="" class="form-control" readonly value="{{$request->row_request->nama_pemilik}}">
                                        <input type="hidden" name="id_consignee" id="" class="form-control" readonly value="{{$request->row_request->kd_consignee}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nama Personal Consignee
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="consignee_personal" id="" class="form-control" readonly value="{{$request->row_request->consignee_personal}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nama Kapal
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2 d-flex align-items-center">
                                        <input type="text" name="o_vessel" id="" class="form-control" readonly value="{{$request->row_request->o_vessel}}">
                                        <input type="text" name="o_voyin" id="" class="form-control" readonly value="{{$request->row_request->o_voyin}}">
                                        <input type="text" name="o_voyout" id="" class="form-control" readonly value="{{$request->row_request->o_voyout}}">

                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nomor D.O
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="no_do" id="" class="form-control" readonly value="{{$request->row_request->no_do}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nomor B.L
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="no_bl" id="" class="form-control" readonly value="{{$request->row_request->no_bl}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Nomor SPPB
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="no_sppb" id="" class="form-control" readonly value="{{isset($request->row_request->no_sppb) ? $request->row_request->no_sppb : ''}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Tanggal SPPB
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="tgl_sppb" id="" class="form-control" readonly value="{{isset($request->row_request->tgl_sppb) ? $request->row_request->tgl_sppb : ''}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Type Stripping
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="type_stripping" id="" class="form-control" readonly value="{{$request->row_request->type_stripping}}">
                                    </div>

                                    <div class="col-md-3 col-7 mb-2 font-weight-bold">
                                        Keterangan
                                    </div>
                                    <div class="col-md-1 col-1 mb-2">
                                        :
                                    </div>
                                    <div class="col-md-8 col-12 mb-2">
                                        <input type="text" name="keterangan" id="" class="form-control" value="{{isset($request->row_request->keterangan) ? $request->row_request->keterangan : ''}}">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="card-title m-b-40">
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-12 col-md-6 order-md-2 order-last">
                                        <h5>Data Container</h5>
                                    </div>
                                    <div class="col-12 col-md-6 order-md-2 order-first">
                                        <div class="d-flex justify-content-end">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="form-card-container" style="display: none">
                                <div class="card-body">
                                    <div class="card-title">
                                        <h5>Form Tambah Container</h5>
                                    </div>

                                    {{-- @include('request.stripping.plan.form-add-cont') --}}
                                </div>
                            </div>

                            <form action="javascript:void(0)" id="form-container">
                                <div class="table-responsive px-3">
                                    <table class="display nowrap table" czllspacing="0" width="100%" id="list-cont-table">
                                        <thead >
                                            <tr>
                                                <th class="text-center" scope="col">#</th>
                                                <th class="text-center" scope="col">No. Container</th>
                                                <th class="text-center" scope="col">Size / Type</th>
                                                <th class="text-center" scope="col">Commodity</th>
                                                <th class="text-center" scope="col">Tgl. Mulai</th>
                                                <th class="text-center" scope="col">Tgl. Akhir</th>
                                                <th class="text-center" scope="col">Tgl. Baru</th>
                                                <th class="text-center" scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="data-contlist-view">
                                            @php
                                                $i = 1;
                                            @endphp
                                            @foreach ($container->row_list as $cnt)
                                                <tr>
                                                    <td class="text-center">{{$i}}</td>
                                                    <td class="text-center">
                                                        @if ($overview)
                                                            {{$cnt->no_container}}
                                                        @else
                                                            <input type="text" name="no_container[]" class="form-control" value="{{$cnt->no_container}}" readonly>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{$cnt->kd_size}} / {{$cnt->kd_type}}</td>
                                                    <td class="text-center">{{$cnt->commodity}}</td>
                                                    <td class="text-center">{{$cnt->tgl_bongkar}}</td>
                                                    <td class="text-center">
                                                        <input type="text" name="start_perp_pnkn[]" id="" class="form-control" readonly value="{{\Carbon\Carbon::parse($cnt->tgl_selesai)->format('Y-m-d')}}">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="date" class="form-control" name="tgl_perp[]" value="{{\Carbon\Carbon::parse($cnt->end_stack_pnkn)->format('Y-m-d')}}">
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-danger" onclick="delCont(`{{$cnt->no_container}}`, `{{$request->row_request->no_request}}`)"><i class="fa fa-trash"></i> Hapus</button>
                                                    </td>
                                                </tr>
                                                @php
                                                    $i++;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                        @if (!$overview)
                                            <tfoot>
                                                <tr>
                                                    <td colspan="8">
                                                        <div class="d-flex align-items-center justify-content-center mt-3">
                                                            <button class="btn btn-outline-warning" onclick="saveEdit();"> <i class="fa fa-save mr-2"></i> Edit Perpanjangan Stripping</button>
                                                            <button class="btn btn-outline-info ml-5" onclick="approve(`{{$request->row_request->no_request}}`, `{{$request->row_request->perp_dari}}`);"> <i class="fa fa-check-circle mr-2"></i> Aprpove Request</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </form>
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
    <script src="{{asset('pages/request/stripping/stripping_perp.js')}}"></script>
@endpush
