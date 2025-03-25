@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Receiving
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
            <h3 class="text-themecolor">View Request Receiving</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.receiving.receiving_luar')}}">Receiving</a></li>
                <li class="breadcrumb-item active">View Data</li>
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
                                <h4 class="">Request Header</h4>
                            </div>

                            <form action="javascript:void(0)" id="request-header" class="form-horizontal m-t-20" novalidate>
                                @csrf
                                <input type="hidden" name="type" value="edit">
                                <div class="row justify-content-center align-items-start">
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="no_req">Nomor Request</label>
                                        <input type="text" name="no_req" class="form-control" readonly id="no_req" value="{{$request->no_request}}">
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="consignee">Penerima / Consignee <small class="text-danger">*</small></label>
                                        <input type="text" name="consignee" id="consignee" class="form-control" value="{{$request->consignee}}" required>
                                        <input type="hidden" name="kd_consignee" id="kd_consignee" value="{{$request->kd_consignee}}">
                                        <div class="invalid-feedback"> Mohon pilih Penerima / Consignee</div>
                                    </div>

                                    <div class="col-md-12 col-12 mb-2">
                                        <label for="almt_consignee">Alamat Consignee</label>
                                        <textarea class="form-control" name="almt_consignee" id="almt_consignee" rows="3" readonly>{{$request->almt_pbm}}</textarea>
                                    </div>

                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="npwp">No. NPWP</label>
                                        <input type="text" name="npwp_consignee" class="form-control" readonly id="npwp" value="{{$request->no_npwp_pbm}}">
                                    </div>

                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="no_ro">No. RO</label>
                                        <input type="text" name="no_ro" class="form-control" id="no_ro" value="{{$request->no_ro}}">
                                    </div>

                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="di">D / I</label>
                                        <select id="di" name="di" class="form-control form-select">
                                            <option value="Domestik">Domestik</option>
                                            <option value="International">International</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="keterangan">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control" id="keterangan" value="{{$request->keterangan}}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{route('uster.new_request.receiving.receiving_luar')}}" class="btn btn-warning mr-3"> <i class="mdi mdi-chevron-left"></i> Kembali</a>
                                    <button type="submit" class="btn btn-info"><i class="mdi mdi-content-save"></i> Simpan Hasil Edit</button>
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
                                            <a href="javascript:void(0)" class="btn btn-outline-info" onclick="addContainer()">Tambah Container (F2)</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="form-card-container" style="display: none">
                                <div class="card-body">
                                    <div class="card-title">
                                        <h5>Form Tambah Container</h5>
                                    </div>

                                    @include('request.receiving.form-add-cont')
                                </div>
                            </div>

                            <div class="table-responsive px-3">
                                <table class="display nowrap table" czllspacing="0" width="100%" id="table-contlist">
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
                                    <tbody id="data-contlist-view">
                                        {{-- @php $i = 1 @endphp
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
                                                        <button class="btn btn-rounded btn-danger" onclick="delCont(`{{base64_encode($cnt->no_container)}}`, `{{base64_encode($request->no_request)}}`)">
                                                            <i class="mdi mdi-delete h5"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach --}}
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
