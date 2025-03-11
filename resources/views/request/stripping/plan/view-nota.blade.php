@extends('layouts.app')

@section('title')
    Perencanaan Stripping
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
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
            <h3 class="text-themecolor">View Perencanaan Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item"><a
                        href="{{ route('uster.new_request.stripping.stripping_plan.awal_tpk') }}">Stripping Plan</a></li>
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
                    <a href="{{ route('uster.new_request.stripping.stripping_plan.awal_tpk') }}" type="button"
                        class="btn btn-outline-warning btn-rounded mb-4">
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
                                {{-- @dd($request->row_request) --}}
                                <input type="hidden" name="type" value="edit">
                                <input type="hidden" id="PENUMPUKAN" name="penumpukan" class="kdemkl"
                                    placeholder="{{ $request->row_request->nama_penumpuk }}" />

                                <input name="id_penumpukan" id="ID_PENUMPUKAN" type="hidden" />
                                <input name="almt_penumpukan" id="ALMT_PENUMPUKAN" type="hidden" />
                                <input name="npwp_penumpukan" id="NPWP_PENUMPUKAN" type="hidden" />
                                <input type="hidden" name="id_consignee" id="ID_CONSIGNEE"
                                    value="{{ $request->row_request->kd_consignee }}">
                                <input name="almt_consignee" id="ALMT_CONSIGNEE" type="hidden" />
                                <input name="npwp_consignee" id="NPWP_CONSIGNEE" type="hidden" />
                                <input type="hidden" id="CURR_VOYAGE" value="{{ $request->row_request->o_voyage }}">
                                <input type="hidden" id="VESCODE" value="{{ $request->row_request->o_vescode }}">
                                <input type="hidden" name="NO_REQUEST_RECEIVING" id="NO_REQUEST_RECEIVING"
                                    value="{{ $request->row_request->no_request_receiving }}">

                                <div class="row justify-content-center align-items-start">
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="no_req">Nomor Request</label>
                                        <input type="text" name="no_req" class="form-control" readonly id="no_req"
                                            value="{{ $request->row_request->no_request_plan }}">
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="no_req2">&nbsp;</label>
                                        <input type="text" name="no_req2" class="form-control" readonly id="no_req2"
                                            value="{{ $request->row_request->o_reqnbs }}">
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="consignee">Penerima / Consignee <small
                                                class="text-danger">*</small></label>
                                        <input type="text" name="consignee" id="CONSIGNEE" class="form-control"
                                            value="{{ $request->row_request->nama_pemilik }}" required>
                                        <div class="invalid-feedback"> Mohon pilih Penerima / Consignee</div>
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="consignee_personal">Nama Personal Consignee</label>
                                        <input type="text" id="consignee_personal" name="consignee_personal"
                                            class="form-control" value="{{ $request->row_request->consignee_personal }}">
                                    </div>

                                    <div class="col-md-6 col-12 mb-2">
                                        <label for="almt_consignee">Nama Kapal</label>
                                        <div class="row align-items-start">
                                            <div class="col-md-5 col-12">
                                                <input id="NM_KAPAL" name="nm_kapal" type="text"
                                                    class="form-control" value="{{ $request->row_request->o_vessel }}" />
                                            </div>
                                            <div class="col-md-2 col-12">
                                                <input type="text" id="VOYAGE_IN" class="form-control"
                                                    name="voyage_in" value="{{ $request->row_request->o_voyin }}" />
                                            </div>
                                            <div class="col-md-2 col-12">
                                                <input type="text" id="VOYAGE_OUT" class="form-control"
                                                    name="voyage_out" value="{{ $request->row_request->o_voyout }}" />
                                            </div>
                                            <div class="col-md-3 col-12">
                                                <input type="text" id="IDVSB" class="form-control" name="IDVSB"
                                                    value="{{ $request->row_request->o_idvsb }}" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12 mb-2">
                                        <div class="row align-items-start">
                                            <div class="col-md-6 col-12">
                                                <label for="no_do"> Nomor D.O</label>
                                                <input type="text" class="form-control" id="no_do" name="no_do"
                                                    value="{{ $request->row_request->no_do }}" />
                                            </div>
                                            <div class="col-md-6 col-12">
                                                <label for="no_bl"> Nomor B.L</label>
                                                <input type="text" class="form-control" id="no_bl" name="no_bl"
                                                    value="{{ $request->row_request->no_bl }}" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="no_sppb">Nomor SPPB</label>
                                        <input type="text" id="no_sppb" class="form-control" name="no_sppb"
                                            value="{{ $request->row_request->no_sppb }}" />
                                    </div>
                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="tgl_sppb">Tanggal SPPB</label>
                                        <input type="text" id="tgl_sppb" class="form-control" name="tgl_sppb"
                                            value="{{ $request->row_request->tgl_sppb }}" />
                                    </div>
                                    <div class="col-md-4 col-12 mb-2">
                                        <label for="type_stripping">Type Stripping</label>
                                        <select name="type_s" id="type_s" class="form-control form-select">
                                            @if ($request->row_request->type_stripping == 'D')
                                                <option value='D'> DALAM NEGERI </option>
                                                <option value='I'> LUAR NEGERI </option>
                                            @else
                                                <option value="I"> LUAR NEGERI </option>
                                                <option value="D"> DALAM NEGERI </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-12 col-12 mb-2">
                                        <label for="keterangan">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control" id="keterangan"
                                            value="{{ $request->row_request->keterangan }}">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{ route('uster.new_request.stripping.stripping_plan.awal_tpk') }}"
                                        class="btn btn-warning mr-3"> <i class="mdi mdi-chevron-left"></i> Kembali</a>
                                    @if ($request->row_request->approve == 'NY')
                                        <button type="submit" class="btn btn-info"><i class="mdi mdi-content-save"></i>
                                            Simpan Hasil Edit</button>
                                    @endif
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
                                            @if ($request->row_request->approve != 'Y')
                                                <a href="javascript:void(0)" class="btn btn-outline-info"
                                                    onclick="addContainer()">Tambah Container (F2)</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card" id="form-card-container" style="display: none">
                                <div class="card-body">
                                    <div class="card-title">
                                        <h5>Form Tambah Container</h5>
                                    </div>

                                    @include('request.stripping.plan.form-add-cont')
                                </div>
                            </div>

                            <div class="table-responsive px-3">
                                <table class="display table" czllspacing="0" width="100%" id="list-cont-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center" scope="col">#</th>
                                            <th class="text-center" scope="col">Approve</th>
                                            <th class="text-center" scope="col">No. Container</th>
                                            <th class="text-center" scope="col">Size / Type</th>
                                            <th class="text-center" scope="col">Asal Cont</th>
                                            <th class="text-center" scope="col">Tgl. Bongkar</th>
                                            <th class="text-center" scope="col">Tgl. Mulai</th>
                                            <th class="text-center" scope="col">Tgl. App Mulai</th>
                                            <th class="text-center" scope="col">Tgl. Selesai</th>
                                            <th class="text-center" scope="col">Tgl. App Selesai</th>
                                            <th class="text-center" scope="col">Remarks</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="data-contlist-view">
                                        @php
                                            $i = 1;
                                        @endphp
                                        @foreach ($container as $cnt)
                                            @php
                                                $cek = $cekFunction->cek($cnt->no_request, $cnt->no_container);
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $i }}</td>
                                                <td>
                                                    @if ($cek == null && $closing != 'CLOSED')
                                                        @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                            <button class="btn btn-sm btn-warning"
                                                                onclick="updateTglApprove(`{{ $cnt->no_container }}`, `{{ $i }}`)">Approve</button>
                                                            <button class="btn btn-sm btn-info mt-2"
                                                                onclick="infoLapangan()">Info</button>
                                                        @else
                                                            Approval Di Uster
                                                        @endif
                                                    @elseif($cek == null && $closing == 'CLOSED')
                                                        @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                            {{-- <button class="btn btn-sm btn-warning" onclick="updateTglApprove(`{{$cnt->no_container}}`, `{{$i}}`)">Approve</button> --}}
                                                            <span class="badge badge-warning p-2">Unaproved</span>
                                                            <button class="btn btn-sm btn-info"
                                                                onclick="infoLapangan()">Info</button>
                                                        @else
                                                            Approval Di Uster
                                                        @endif
                                                    @else
                                                        @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                            <span class="badge badge-info p-2">Approved</span>
                                                            <button class="btn btn-sm btn-info"
                                                                onclick="infoLapangan()">Info</button>
                                                        @else
                                                            Approval Di Uster
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $cnt->no_container }}
                                                    <input type="hidden" id="no_cont"
                                                        name="no_cont_{{ $i }}"
                                                        value="{{ $cnt->no_container }}">
                                                </td>
                                                <td class="text-center">
                                                    @if ($cnt->asal_cont == 'TPK')
                                                        {{ $cnt->kd_size }} / {{ $cnt->kd_type }}
                                                    @else
                                                        {{ $cnt->ukuran }} / {{ $cnt->type }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $cnt->asal_cont }}
                                                    <input type="hidden" name="asal_cont_{{ $i }}"
                                                        value="{{ $cnt->asal_cont }}">
                                                </td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($cnt->tgl_bongkar)->translatedFormat('d-M-Y') }}
                                                    <input type="hidden" id="tgl_bongkar"
                                                        name="tgl_bongkar_{{ $i }}"
                                                        value="{{ \Carbon\Carbon::parse($cnt->tgl_bongkar)->translatedFormat('Y-m-d') }}">
                                                </td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($cnt->tgl_mulai)->translatedFormat('d-M-Y') }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($cek == null && $closing != 'CLOSED')
                                                        @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                            <input type="date" name="TGL_APPROVE_{{ $i }}"
                                                                class="form-control"
                                                                value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                                                        @elseif(Session::get('id_group') == '6')
                                                            {{ \Carbon\Carbon::parse($cnt->tgl_approve)->translatedFormat('d-M-Y') }}
                                                        @endif
                                                    @else
                                                        {{ \Carbon\Carbon::parse($cnt->tgl_approve)->translatedFormat('d-M-Y') }}
                                                        <input type="hidden" name="TGL_APPROVE_{{ $i }}"
                                                            class="form-control"
                                                            value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ \Carbon\Carbon::parse($cnt->tgl_selesai)->translatedFormat('d-M-Y') }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($cek == null && $closing != 'CLOSED')
                                                        @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                            <input type="date"
                                                                name="TGL_APPROVE_SELESAI_{{ $i }}"
                                                                class="form-control"
                                                                value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                                                        @elseif(Session::get('id_group') == '6')
                                                            {{ \Carbon\Carbon::parse($cnt->tgl_app_selesai)->translatedFormat('d-M-Y') }}
                                                        @endif
                                                    @else
                                                        {{ \Carbon\Carbon::parse($cnt->tgl_app_selesai)->translatedFormat('d-M-Y') }}
                                                        <input type="hidden"
                                                            name="TGL_APPROVE_SELESAI_{{ $i }}"
                                                            class="form-control"
                                                            value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $cnt->remark }}
                                                    @if (Session::get('id_group') == 'J' || Session::get('id_group') == 'K')
                                                        <input type="text" name="remarks_{{ $i }}"
                                                            class="form-control" value="{{ $cnt->remark }}">
                                                    @elseif(Session::get('id_group') == '6')
                                                        {{ $cnt->remark }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="delCont(`{{ $cnt->no_container }}`, `{{ $request->row_request->no_request_plan }}`, `{{ $request->row_request->o_reqnbs }}`)">Hapus</button>
                                                </td>
                                            </tr>
                                            @php
                                                $i++;
                                            @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <center>
                                <a href="javascript:void(0)" class="btn btn-info mt-3"
                                    onclick="saveReq({{ count($container) }})">Save Request</a>
                            </center>
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
    <script src="{{ asset('pages/request/stripping/stripping_plan.js') }}"></script>
@endpush
