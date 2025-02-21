@extends('layouts.app')

@section('title')
Perencanaan Kegiatan Delivery
@endsection

@section('pages-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">New Request</a></li>
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.new_request.delivery.delivery_luar.index')}}">Delivery Ke Luar</a></li>
            <li class="breadcrumb-item active">Edit</li>
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
                <h3><b>Request Delivery - SP2 ke LUAR DEPO</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Jenis Repo: </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="JN_REPO" id="JN_REPO" class="form-control" value="{{ $data['row_request']->jn_repo}}" readonly>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No Request Delivery : </label>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" name="NO_REQUEST" id="NO_REQUEST" class="form-control" value="{{ $data['row_request']->no_request}}">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="NO_REQUEST2" id="NO_REQUEST2" class="form-control" value="{{ $data['no_req2'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2" style="display:none">
                                    <label for="tb-fname">Penumpukan Oleh : </label>
                                </div>
                                <div class="col-md-4" style="display:none">
                                    <input class="form-control" id="KD_PELANGGAN" name="KD_PELANGGAN" type="hidden" value="{{$data['row_request']->kd_pbm}}" readonly />
                                    <input class="form-control" id="NPWP" name="NPWP" type="hidden" value="{{$data['row_request']->no_npwp_pbm ?? ''}}" readonly />
                                    <input class="form-control" id="ALAMAT" name="ALAMAT" type="hidden" value="{{$data['row_request']->alamat ?? ''}}" readonly />
                                    <!-- <input class="form-control" id="NM_PELANGGAN" name="NM_PELANGGAN" type="text" value="{{$data['row_request']->nama_pbm}}" title="Autocomplete" /> -->
                                    <select name="NM_PELANGGAN" id="NM_PELANGGAN" class="form-control w-100">
                                        <option value="{{ $data['row_request']->nama_pbm}}" selected> {{ $data['row_request']->nama_pbm}} </option>
                                    </select>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">E.M.K.L : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_PELANGGAN2" name="KD_PELANGGAN2" type="hidden" value="{{$data['row_request']->kd_pbm2}}" readonly />
                                    <input class="form-control" id="NPWP2" name="NPWP2" type="hidden" value="{{$data['row_request']->no_npwp_pbm ?? ''}}" readonly />
                                    <input class="form-control" id="ALAMAT2" name="ALAMAT2" type="hidden" value="{{$data['row_request']->alamat ?? ''}}" readonly />
                                    <!-- <input class="form-control" id="NM_PELANGGAN2" name="NM_PELANGGAN2" type="text" value="{{$data['row_request']->nama_pbm2}}"/> -->
                                    <select name="NM_PELANGGAN2" id="NM_PELANGGAN2" class="form-control w-100">
                                        <option value="{{ $data['row_request']->nama_pbm2}}" selected> {{ $data['row_request']->nama_pbm2}} </option>
                                    </select>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No P.E.B : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="id_NO_PEB" name="NO_PEB" type="text" value="{{$data['row_request']->peb}}" />
                                    <input class="form-control" id="id_KD_CABANG" name="KD_CABANG" type="hidden" value="{{$data['kd_cbg']}}" /></td>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No. N.P.E : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="id_NO_NPE" name="NO_NPE" type="text" value="{{$data['row_request']->npe}}" /></td>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No RO : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="NO_RO" name="NO_RO" class="form-control" value="{{$data['row_request']->no_ro}}">
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Kapal : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_KAPAL" name="KD_KAPAL" type="hidden" value="{{$data['row_request']->kd_kapal}}" />
                                    <select name="NM_KAPAL" id="NM_KAPAL" class="form-control w-100">
                                        <option value="{{ $data['row_request']->nm_kapal}}" selected> {{ $data['row_request']->nm_kapal}} </option>
                                    </select>
                                    <input class="form-control" id="CALL_SIGN" name="CALL_SIGN" type="hidden" value="{{$data['row_request']->call_sign ?? ''}}" />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Voyage : </label>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input class="form-control" id="VOYAGE_IN" name="VOYAGE_IN" type="text" value="{{$data['row_request']->voyage_in}}" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <input class="form-control" id="VOYAGE" name="VOYAGE" type="text" value="{{$data['row_request']->voyage}}" readonly />
                                        </div>
                                    </div>
                                    <input class="form-control" id="VOYAGE_OUT" name="VOYAGE_OUT" type="hidden" value="{{$data['row_request']->voyage_out}}" readonly />
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">ETD : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" value="{{$data['row_request']->tgl_berangkat}}" id="ETD" name="ETD" type="text" readonly />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">ETA : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" value="{{$data['row_request']->tgl_tiba}}" id="ETA" name="ETA" type="text" readonly />
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Agen : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_AGEN" name="KD_AGEN" type="hidden" value="{{$data['row_request']->kd_agen}}" readonly />
                                    <input class="form-control" id="TGL_BERANGKAT" name="TGL_BERANGKAT" type="hidden" value="{{$data['row_request']->tgl_berangkat}}" readonly />
                                    <input class="form-control" id="NM_AGEN" name="NM_AGEN" type="text" value="{{ $data['row_request']->nm_agen}}" readonly />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No PKK : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="NO_UKK" name="NO_UKK" type="text" value="{{$data['row_request']->no_ukk}}" readonly maxlength="16" title="Autocomplete" class="pkkkapal" />
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No Booking : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="NO_BOOKING" name="NO_BOOKING" type="text" value="{{$data['row_request']->no_booking}}" maxlength="16" readonly />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Port Of Destination : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="POL" name="POL" type="hidden" value="{{$data['row_request']->kd_pelabuhan_tujuan}}" readonly />
                                    <input class="form-control" id="KD_PELABUHAN_ASAL" name="KD_PELABUHAN_ASAL" type="hidden" value="IDPNK" class="pod" readonly />

                                    <select name="NM_PELABUHAN_ASAL" id="NM_PELABUHAN_ASAL" class="form-control w-100">
                                        <option value="PONTIANAK, INDONESIA" selected> PONTIANAK, INDONESIA </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Final Discharge : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_PELABUHAN_TUJUAN" name="KD_PELABUHAN_TUJUAN" type="hidden" value="{{$data['row_request']->kd_pelabuhan_tujuan}}" readonly />
                                    <input class="form-control" id="POD" name="POD" type="hidden" value="{{$data['row_request']->kd_pelabuhan_asal}}" readonly />
                                    <select name="NM_PELABUHAN_TUJUAN" id="NM_PELABUHAN_TUJUAN" class="form-control w-100">
                                        <option value="{{$data['row_request']->nm_pelabuhan_tujuan ?? ''}}" selected> {{$data['row_request']->nm_pelabuhan_tujuan ?? ''}} </option>
                                    </select>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Keterangan : </label>
                                </div>
                                <div class="col-md-4">
                                    <textarea class="form-control" id="KETERANGAN" name="KETERANGAN" cols="40" rows="1">{{$data['row_request']->keterangan}}</textarea>
                                </div>
                            </div>

                            <div class="row">

                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Bayar Reefer : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="id_SHIFT_RFR" name="SHIFT_RFR" type="text" value="2" /><b>* Shift</b>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Calculator Shift Reefer : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="TGL_STACKING" name="TGL_STACKING" type="hidden" value="{{$data['row_request']->tgl_stacking}}" />
                                    <input class="form-control" id="TGL_MUAT" name="TGL_MUAT" type="hidden" value="{{$data['row_request']->tgl_muat}}" />
                                    <div class="row">
                                        <div class="col-md-5">
                                            <input class="form-control" type="text" maxlength="19" value="" id="ID_TGL_MULAI" name="TGL_MULAI" readonly />&nbsp;
                                        </div>
                                        <div class="col-md-2">
                                            s/d
                                        </div>
                                        <div class="col-md-5">
                                            <input class="form-control" type="text" maxlength="19" value="" id="ID_TGL_NANTI" name="TGL_NANTI" readonly />&nbsp;
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="row text-center mb-3">
                        <div class="col">
                            <button onclick="cekearly()" class="btn btn-info">Simpan</button>
                        </div>
                    </div>

                </div>

                <div class="border rounded my-3">
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor Container: </label>
                            </div>
                            <div class="col-md-4">
                                <select name="NO_CONT" id="NO_CONT" class="form-control w-100"></select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Ukuran : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" class="form-control" type="text" name="SIZE" id="SIZE" readonly="readonly" />
                                <input class="form-control" type="hidden" name="TGL_STACK" id="TGL_STACK" readonly="readonly" />
                                <input class="form-control" type="hidden" name="ASAL" id="ASAL" readonly="readonly" />
                                <input class="form-control" id="NO_REQUEST" name="NO_REQUEST" type="hidden" value="{{$data['row_request']->no_request}}" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Height : </label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" id="hg" name="hg">
                                    <option value="8.6">8.6</option>
                                    <option value="9.6">9.6</option>
                                    <option value="OOG">OOG</option>
                                </select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Status : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="STATUS" id="STATUS" readonly="readonly" />
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Tipe : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="TYPE" id="TYPE" readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Temp : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="temp" id="temp" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">HZ/IMO/UN NUMBER : </label>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-4">
                                        <select class="form-control" name="HZ" id="HZ" onchange="check_hz()">
                                            <option value="N">T</option>
                                            <option value="Y">Y</option>
                                        </select>
                                    </div>
                                    <div class="col-4"><input class="form-control" type="text" id="imo" name="imo" disabled /></div>
                                    <div class="col-4"><input class="form-control" type="text" id="unnumber" name="unnumber" disabled /></div>
                                </div>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Komoditi : </label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" name="KOMODITI" id="KOMODITI">
                                </select>
                                <input class="form-control" type="hidden" name="ID_KOMODITI" ID="ID_KOMODITI" />
                                <input class="form-control" type="hidden" name="EX_PMB" ID="EX_PMB" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Via : </label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" name="via" id="via">
                                    <option value="darat">DARAT</option>
                                    <option value="tongkang">TONGKANG</option>
                                    <!-- <option value="ship_side">SHIP-SIDE</option>   -->
                                </select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Berat : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="berat" ID="berat" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">OH-OW-OL : </label>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-4">
                                        <input class="form-control" type="text" name="oh" id="oh" />
                                    </div>
                                    <div class="col-4">
                                        <input class="form-control" type="text" name="ow" id="ow" />
                                    </div>
                                    <div class="col-4">
                                        <input class="form-control" type="text" name="ol" id="ol" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Seal : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="no_seal" ID="no_seal" />
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Tgl Stack : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" readonly="readonly" name="TGL_STACK2" ID="TGL_STACK2" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">End Stack : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" readonly="readonly" name="END_STACK" ID="END_STACK" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Carrier : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="carrier" ID="carrier" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Keterangan : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="keterangan" ID="keterangan" />
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col">
                                <button class="btn btn-info" onclick="add_cont()">Tambahkan Container</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="border rounded my-3">
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table class="datatables-service table " id="container-table">
                                <thead>
                                    <tr>
                                        <th>No </th>
                                        <th>No Container</th>
                                        <th>Status </th>
                                        <th>Ukuran</th>
                                        <th>Tipe</th>
                                        <th>Komoditi</th>
                                        <th>No Seal</th>
                                        <th>Berat</th>
                                        <th>Via</th>
                                        <th>Hz</th>
                                        <th>Tgl Awal Stack</th>
                                        <th>Tgl Delivery</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                            </table>
                        </div>
                        <div id="button_save" class="text-center my-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pages-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#NM_PELANGGAN2').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.pbm") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.kd_pbm,
                            text: arr.nm_pbm,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_PELANGGAN2').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NM_PELANGGAN2").val(data.nm_pbm);
            $("#KD_PELANGGAN2").val(data.kd_pbm);
            $("#ALAMAT2").val(data.almt_pbm);
            $("#NPWP2").val(data.no_npwp_pbm);
        });


        $('#NM_PELABUHAN_ASAL').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.master_pelabuhan_palapa") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.cdg_port_code,
                            text: arr.cdg_port_name,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_PELABUHAN_ASAL').on('select2:select', function(e) {
            var data = e.params.data;
            $("#KD_PELABUHAN_ASAL").val(data.cdg_port_code);
            $("#NM_PELABUHAN_ASAL").val(data.cdg_port_name);
        });

        $('#NM_PELABUHAN_TUJUAN').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.master_pelabuhan_palapa") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.cdg_port_code,
                            text: arr.cdg_port_name,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_PELABUHAN_TUJUAN').on('select2:select', function(e) {
            var data = e.params.data;
            $("#KD_PELABUHAN_TUJUAN").val(data.cdg_port_code);
            $("#NM_PELABUHAN_TUJUAN").val(data.cdg_port_name);
        });

        var v_kapal = $("#KD_KAPAL").val();
        var v_voyage = $("#VOYAGE").val();
        $('#carrier').select2({
            minimumInputLength: 2, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.carrier_praya") !!}' + "?voyage=" + v_voyage,
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    if (data == 'N') {
                        return false;
                    }
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.operatorCode,
                            text: arr.operatorCode,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#carrier').on('select2:select', function(e) {
            var data = e.params.data;
            $("#carrier").val(data.operatorCode);
            $("#cardet").val(data.operatorName);
        });

        $('#NM_KAPAL').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.master_vessel_palapa") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.vessel,
                            text: arr.vessel + ' | ' + arr.voyage_in + '/' + arr.voyage_out,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_KAPAL').on('select2:select', function(e) {
            var data = e.params.data;
            $("#KD_KAPAL").val(data.vessel_code);
            $("#NM_AGEN").val(data.operator_name);
            $("#KD_AGEN").val(data.operator_id);
            $("#NM_KAPAL").val(data.vessel);
            $("#VOYAGE_IN").val(data.voyage_in);
            $("#VOYAGE_OUT").val(data.voyage_out);
            $("#NO_UKK").val(data.id_vsb_voyage);
            if (data.vessel_code && data.id_vsb_voyage) {
                $("#NO_BOOKING").val(`BP${data.vessel_code}${data.id_vsb_voyage}`);
            }
            $("#CALL_SIGN").val(data.call_sign);
            $("#TGL_BERANGKAT").val(data.atd)
            $("#TGL_STACKING").val(data.open_stack)
            $("#TGL_MUAT").val(data.closing_time_doc)
            $("#ETA").val(data.eta)
            $("#ETD").val(data.etd)
            $("#POL").val(data.id_pol);
            $("#POD").val(data.id_pod);
            $("#NM_PELABUHAN_ASAL").val(data.pol);
            // Create a new option element
            var newOption = new Option(data.pol, data.pol, true, true);

            // Append the new option to the Select2 dropdown
            $('#NM_PELABUHAN_ASAL').append(newOption).trigger('change');

            $("#KD_PELABUHAN_ASAL").val(data.id_pol);
            $("#OPEN_STACK").val(data.open_stack);
            $("#CONT_LIMIT").val(data.container_limit);
            $("#CLOSING_TIME").val(data.closing_time);
            $("#CLOSING_TIME_DOC").val(data.closing_time_doc);
            $("#VOYAGE").val(data.voyage);;
        });

        $('#NO_CONT').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                // url: 'uster.new_request.delivery.delivery_luar.cont_delivery")' + "?jn_repo=" + $("#JN_REPO").val(),
                url: '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.contdelivery") !!}' + "?jn_repo=" + $("#JN_REPO").val(),
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_container,
                            text: arr.no_container + ' | ' + arr.status + ' | ' + arr.size_ + ' | ' + arr.type_,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NO_CONT').on('select2:select', function(e) {
            var data = e.params.data;
            if ($("#JN_REPO").val() == 'EKS_STUFFING') {

                if (data.no_booking != $("#NO_BOOKING").val() && data.source == 'EXSTUF') {
                    $.blockUI({
                        message: '<h1>Kapal tidak sesuai saat req. stuffing, Harap Melakukan batal muat terlebih dahulu!</h1>',
                        timeout: 2000
                    });
                    $("#NO_CONT").val('');
                } else {

                    $("#NO_CONT").val(data.no_container);
                    $("#SIZE").val(data.size_);
                    $("#TYPE").val(data.type_);
                    $("#STATUS").val(data.status);
                    $("#TGL_STACK").val(data.realisasi_stuffing);
                    $("#TGL_STACK2").val(data.realisasi_stuffing);
                    $("#ASAL").val(data.asal);
                    $("#EX_PMB").val(data.no_booking);
                    if (data.status == null) {
                        $("#STATUS").val('MTY');
                    }
                    $("#END_STACK").val($("#TGL_BERANGKAT").val());
                    if (data.type_ == 'HQ') {
                        $("#hg").val('9.6');
                    }
                }
            } else {
                $("#NO_CONT").val(data.no_container);
                $("#SIZE").val(data.size_);
                $("#TYPE").val(data.type_);
                $("#STATUS").val(data.status);
                $("#TGL_STACK").val(data.tgl_stack);
                $("#TGL_STACK2").val(data.tgl_stack);
                $("#ASAL").val(data.asal);
                $("#EX_PMB").val(data.no_booking);
                if (data.status == null) {
                    $("#STATUS").val('MTY');
                }
                if (data.type_ == 'HQ') {
                    $("#hg").val('9.6');
                }
                if (data.asal == 'UST') {
                    var jn_repo = $("#JN_REPO").val();
                    var csrfToken = '{{ csrf_token() }}';
                    $.post('{!! route("uster.new_request.delivery.delivery_luar.get_tgl_stack") !!}', {
                        _token: csrfToken,
                        no_cont: data.no_container,
                        JN_REPO: jn_repo
                    }, function(data) {
                        // var datax = $.parseJSON(data);
                        console.log(data);
                        $("#TGL_STACK2").val(data.tgl_bongkar);
                    });
                }
                $("#END_STACK").val($("#TGL_BERANGKAT").val());
            }
        });


        $('#KOMODITI').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.commodity") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.nm_commodity,
                            text: arr.nm_commodity,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.editcontlist") !!}' + '?no_req=' + '{!!  $data["row_request"]->no_request !!}' + "&no_req2=" + '{!! $data["no_req2"] !!}',
                type: 'GET',
                data: function(d) {
                    d.noReq = $('#NO_REQ').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex', // Use the special 'DT_RowIndex' property provided by DataTables
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        // Render the sequence number
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'no_container',
                    name: 'no_container'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'size_',
                    name: 'size_'
                },
                {
                    data: 'type_',
                    name: 'type_'
                },
                {
                    data: 'komoditi',
                    name: 'komoditi'
                },
                {
                    data: 'no_seal',
                    name: 'no_seal'
                },
                {
                    data: 'berat',
                    name: 'berat'
                },
                {
                    data: 'via',
                    name: 'via'
                },
                {
                    data: 'hz',
                    name: 'hz'
                },
                {
                    data: 'start_stack',
                    name: 'start_stack'
                },

                {
                    data: 'tgl_delivery',
                    name: 'tgl_delivery',
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        noReq = data['no_request'];
                        return '<button value="Hapus" onclick="del_cont(\'' + data['no_container'] + '\',\'' + data['ex_bp_id'] + '\',' + meta.row + ')" class="btn btn-danger"> Hapus </button>';
                    }
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                $(row).append('<input type="hidden" id="NO_CONT_' + rowIdx + '" class="hidden-input" value="' + data.no_container + '">');
            },
            lengthMenu: [10, 20, 50, 100], // Set the default page lengths
            pageLength: 10, // Set the initial page length
            initComplete: function() {
                var table = $('#container-table').DataTable();
                var totalRows = table.rows().count();
                $('#button_save').html('<button type="button" onclick="save_tgl_delivery(' + totalRows + ')" value="Save" class="btn btn-info">Save</button>');
            }
        });

    });


    ///==================================================///

    function check_hz() {
        var hz_ = $("#HZ").val();
        //alert(hz_);
        if (hz_ == 'Y') {
            $("#imo").removeAttr('disabled');
            $("#unnumber").removeAttr('disabled');
        } else {
            $("#imo").attr('disabled', 'disabled');
            $("#unnumber").attr('disabled', 'disabled');
        }
    }

    function cekearly() {
        //	alert ("Undifined Result");
        $NO_BOOKING = $("#NO_BOOKING").val();
        $KD_PBM = $("#KD_PELANGGAN").val();
        $KD_PBM2 = $("#KD_PELANGGAN2").val();
        $NO_UKK = $("#NO_UKK").val();
        if ($NO_UKK != '' && $KD_PBM != '' && $KD_PBM2 != '' && $NO_BOOKING != '') {
            var csrfToken = '{{ csrf_token() }}';
            $.post('{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.cekearly") !!}', {
                _token: csrfToken,
                NO_BOOKING: $NO_BOOKING,
                KD_PBM: $KD_PBM,
                NO_UKK: $NO_UKK
            }, function(data) {
                //alert (data);
                if (data == 'T') {
                    sAlert('', 'Closing Time Sudah Berakhir', "info");
                    return false;
                } else if (data == 'N') {
                    sAlert('', 'EMKL Tidak di izin kan Early Stacking!', "info");
                    return false;
                } else if (data == 'Y') {
                    var formData = $('#dataCont').serialize();
                    ajaxPostJson('{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.edit_do") !!}', formData, 'input_success')
                } else {

                    sAlert("", "Undifined Result", 'info');
                }
            });
        } else if ($NO_UKK == '' || $KD_PBM == '' || $KD_PBM2 == '') {
            sAlert("Peringatan", "Kapal dan EMKL Harus Diisi", 'info');
            return false;
        } else if ($NO_BOOKING == '') {
            sAlert("Peringatan", "Belum Booking Stack", 'info');
            return false;
        }
    }

    function add_cont() {
        var url = '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.ceknocont") !!}';
        var csrfToken = '{{ csrf_token() }}';
        var komo = $("#ID_KOMODITI").val();
        var gross = $("#berat").val();

        if (komo == '' || gross == '') {
            sAlert('Peringatan', 'Komoditi harap diisi', 'warning');
            return false;
        } else {
            var $nocont = $('#NO_CONT').val();
            var $NO_UKK = $("#NO_UKK").val();
            var $NO_BOOKING = $('#NO_BOOKING').val();
            var $TYPE_NAME = $('#TYPE').val();
            var $JENIS = $('#STATUS').val();
            var $SIZE = $('#SIZE').val();

            if ($('#NO_CONT').val().length >= 1) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: csrfToken,
                        "NO_UKK": $NO_UKK,
                        "NO_CONTAINER": $nocont,
                        "NO_BOOKING": $NO_BOOKING,
                        "TYPE": $TYPE_NAME,
                        "JENIS": $JENIS,
                        "SIZE": $SIZE
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(data) {
                        Swal.close();
                        if (data == 'Y') {
                            var no_cont_ = $("#NO_CONT").val();
                            var hz_ = $("#HZ").val();
                            var no_req_ = $("#NO_REQUEST").val();
                            var no_req2_ = $("#NO_REQUEST2").val();
                            var status_ = $("#STATUS").val();
                            var komoditi_ = $("#KOMODITI").val();
                            var kd_komoditi_ = $("#ID_KOMODITI").val();
                            var keterangan_ = $("#keterangan").val();
                            var no_seal_ = $("#no_seal").val();
                            var berat_ = $("#berat").val();
                            var via_ = $("#via").val();
                            var size_ = $("#SIZE").val();
                            var tipe_ = $("#TYPE").val();
                            var no_booking_ = $("#NO_BOOKING").val();
                            var no_ukk_ = $("#NO_UKK").val();
                            var tgl_delivery = $("#TGL_BERANGKAT").val();
                            var tgl_stack = $("#TGL_STACK2").val();
                            var asal = $("#ASAL").val();
                            var jn_repo = $("#JN_REPO").val();
                            var ex_pmb = $("#EX_PMB").val();
                            var url = '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.addcont") !!}';
                            var imo_ = $("#imo").val();
                            var unnumber_ = $("#unnumber").val();
                            var temp_ = $("#temp").val();
                            var carrier_ = $("#carrier").val();
                            var oh_ = $("#oh").val();
                            var ow_ = $("#ow").val();
                            var ol_ = $("#ol").val();
                            var height = $("#hg").val();
                            var cont_limit_ = $("#CONT_LIMIT").val();
                            if (carrier_ == '') {
                                sAlert("Peringatan", 'CARRIER HARUS DIISI', "warning");
                                return false;
                            } else if (tgl_stack == '') {
                                sAlert("Peringatan", 'Tgl Stack Tidak Boleh Kosong', "warning");
                                return false;
                            } else if (size_ == '' || tipe_ == '' || status_ == '') {
                                sAlert("Peringatan", 'Size, Type, dan Status tidak boleh kosong', "warning");
                                return false;
                            } else if (hz_ == 'Y') {
                                if (imo_ == '' || unnumber_ == '') {
                                    sAlert("Peringatan", 'IMO CLASS DAN UN NUMBER WAJIB DIISI', "warning");
                                    return false;
                                }
                            } else if (tipe_ == 'RFR' && temp_ == '') {
                                sAlert("Peringatan", 'TEMPERATURE WAJIB DIISI', "warning");
                                return false;
                            } else {
                                $.ajax({
                                    url: url,
                                    type: 'POST',
                                    data: {
                                        _token: csrfToken,
                                        KETERANGAN: keterangan_,
                                        NO_SEAL: no_seal_,
                                        BERAT: berat_,
                                        VIA: via_,
                                        KOMODITI: komoditi_,
                                        KD_KOMODITI: kd_komoditi_,
                                        NO_CONT: no_cont_,
                                        NO_REQ: no_req_,
                                        STATUS: status_,
                                        HZ: hz_,
                                        SIZE: size_,
                                        TIPE: tipe_,
                                        NO_BOOKING: no_booking_,
                                        NO_UKK: no_ukk_,
                                        NO_REQ2: no_req2_,
                                        tgl_delivery: tgl_delivery,
                                        tgl_stack: tgl_stack,
                                        asal: asal,
                                        JN_REPO: jn_repo,
                                        EX_PMB: ex_pmb,
                                        IMO: imo_,
                                        UNNUMBER: unnumber_,
                                        HEIGHT: height,
                                        TEMP: temp_,
                                        CARRIER: carrier_,
                                        OH: oh_,
                                        OW: ow_,
                                        OL: ol_,
                                        CONT_LIMIT: cont_limit_
                                    },
                                    beforeSend: function() {
                                        Swal.fire({
                                            title: 'Loading...',
                                            allowOutsideClick: false,
                                            onBeforeOpen: () => {
                                                Swal.showLoading();
                                            }
                                        });
                                    },
                                    success: function(data) {
                                        Swal.close();
                                        if (data == "NOT_EXIST") {
                                            sAlert("Peringatan", "Container Belum Terdaftar", "warning");
                                        } else if (data == "CLOSING_TIME") {
                                            sAlert("Peringatan", "Masa Closing Time Sudah Habis, Silakan Lakukan Booking Stack Pada Kapal Lain", "warning");
                                        } else if (data == "BLM_PLACEMENT") {
                                            sAlert("Peringatan", "Container Belum Placement", "warning");
                                        } else if (data == "SDH_REQUEST") {
                                            sAlert("Peringatan", "Container Sudah Mengajukan Request Delivery", "warning");
                                        } else if (data == "EXIST_REC") {
                                            sAlert("Peringatan", "Container Masih Aktif di Req Receiving / Belum Gate In", "warning");
                                        } else if (data == "EXIST_DEL_BY_BOOKING") {
                                            sAlert("Peringatan", "Container Sudah Di Request Pada Voyage Tersebut", "warning");
                                        } else if (data == "EXIST_MUAT") {
                                            sAlert("Peringatan", "Container Dalam Proses Muat", "warning");
                                        } else if (data == "EXIST_TPK") {
                                            sAlert("Peringatan", "Container Masih Aktif di TPK", "warning");
                                        } else if (data == "EXIST_STRIP") {
                                            sAlert("Peringatan", "Container Masih Aktif di Req Stripping / Belum Realisasi", "warning");
                                        } else if (data == "EXIST_STUF") {
                                            sAlert("Peringatan", "Container Masih Aktif di Req Stuffing / Belum Realisasi", "warning");
                                        }

                                        if (data.status['code'] == 200) {
                                            sAlert('Berhasil!', data.status['msg'], 'success');
                                        } else {
                                            sAlert('Gagal!', data.status['msg'], 'danger');
                                        }

                                        $('#container-table').DataTable().ajax.reload();
                                    },
                                    error: function(xhr, status, error) {
                                        Swal.close();
                                        sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
                                    },
                                    complete: function() {
                                        $("#NO_CONT").val('');
                                        $("#HZ").val('');
                                        $("#STATUS").val('');
                                        //$("#KOMODITI").val('');
                                        //$("#ID_KOMODITI").val('');
                                        $("#keterangan").val('');
                                        $("#no_seal").val('');
                                        $("#via").val('');
                                        $("#SIZE").val('');
                                        $("#TYPE").val('');
                                        $("#TGL_STACK2").val('');
                                        $("#END_STACK").val('');
                                    }
                                });
                            }
                            //prosedur add comment disini, method $.post, include user id dan content id
                        }
                        //}
                        else if (data == 'T') {
                            sAlert('Peringatan', 'Container Dalam Proses Muat!', 'info');
                        } else if (data == 'Z') {
                            sAlert('Peringatan', 'Closing Time Dry/Reefer Sudah Habis!', 'info');

                        } else if (data == 'X') {
                            sAlert('Peringatan', 'Jumlah Booking Teus Sudah Habis!', 'info');
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
                    }
                });
            }
        }
    }

    function del_cont($no_cont, $bp_id = '', rowId) {
        var no_req_ = "{{ $data['row_request']->no_request }}";
        var no_req2_ = $("#NO_REQUEST2").val();
        var csrfToken = '{{ csrf_token() }}';
        $.post('{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.delcont") !!}', {
            _token: csrfToken,
            NO_CONT: $no_cont,
            NO_REQ: no_req_,
            NO_REQ2: no_req2_,
            EX_BP: $bp_id
        }, function(data) {
            if (data.status['code'] == 200) {
                sAlert('Berhasil!', data.status['msg'], 'success');
                var table = $('#container-table').DataTable();
                table.row($('#container-table tbody tr:contains(' + rowId + ')')).remove().draw();
            } else {
                sAlert('Gagal!', data.status['msg'], 'danger');
            }
        });
    }

    function save() {

        var formData = $('#dataCont').serialize();

        $.ajax({
            url: '{!! route("uster.new_request.delivery.delivery_luar.savedataedit") !!}', // Ganti dengan URL yang sesuai
            type: 'POST', // Ganti dengan metode HTTP yang sesuai (GET/POST)
            data: formData,
            beforeSend: function() {
                // Tampilkan pesan SweetAlert sebelum permintaan dikirim
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                // Sembunyikan pesan SweetAlert setelah permintaan berhasil
                Swal.close();
                // Tampilkan pesan sukses menggunakan SweetAlert
                if (data.status['code'] == 200) {
                    sAlert('Berhasil!', data.status['msg'], 'success');
                } else {
                    sAlert('Gagal!', data.status['msg'], 'danger');
                }
                // Lakukan tindakan tambahan sesuai kebutuhan, misalnya memperbarui tampilan
            },
            error: function(xhr, status, error) {
                // Sembunyikan pesan SweetAlert setelah permintaan gagal
                Swal.close();
                // Tampilkan pesan error menggunakan SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Ada masalah saat menyimpan data',
                });
                // Lakukan penanganan kesalahan tambahan sesuai kebutuhan
            }
        });
    }

    function save_tgl_delivery(total) {
        var url = '{!! route("uster.new_request.delivery.delivery_luar.updatedatadelivery") !!}';
        var no_request = $("#NO_REQ").val();

        for (var i = 1; i <= total; i++) {
            (function(i) {
                var no_cont = $("#NO_CONT_" + i).val();
                var tgl_delivery = $("#tgl_delivery_" + i).val();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        NO_REQ: no_request,
                        TGL_DELIVERY: tgl_delivery,
                        NO_CONT: no_cont,
                        INDEX: i,
                        TOTAL: total,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(data) {
                        Swal.close();
                        if (data.status['code'] == 200) {
                            sAlert('Berhasil!', data.status['msg'], 'success');
                        } else {
                            sAlert('Gagal!', data.status['msg'], 'danger');
                        }

                        $('#container-table').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        sAlert('Error!', 'Failed to update data', 'error');
                    },
                    complete: function() {
                        if (i === total) {
                            Swal.close();
                            sAlert('Success!', 'Tanggal Delivery Berhasil Disimpan', 'success');
                        }
                    }
                });
            })(i);
        }
    }
</script>
@endsection