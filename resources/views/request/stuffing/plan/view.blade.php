@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
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
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item"><a href="{{ route('uster.new_request.stuffing.stuffing_plan') ?? '' }}">Stuffing
                        Plan</a>
                </li>
                <li class="breadcrumb-item active">View</li>
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
                    <h3><b>Perencanaan Kegiatan Stuffing</b></h3>

                    <div class="p-3">

                        <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Pengguna Jasa</h4>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Request : </label>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="no_req" name="NO_REQ"
                                    value="{{ $row_request->no_request ?? '' }}" readonly="readonly">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="NO_REQUEST2" id="NO_REQUEST2"
                                    value="{{ $no_req2 ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="NO_REQUEST3" id="NO_REQUEST3"
                                    value="{{ isset($no_req3) ? $no_req3 : '' ?? '' }}">

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama EMKL : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="EMKL" id="EMKL"
                                    placeholder="{{ $row_request->nama_emkl ?? '' }}">
                                <input type="hidden" name="ID_EMKL" id="ID_EMKL"
                                    value="{{ $row_request->id_emkl ?? '' }}" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Penumpukan Oleh : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" name="PNKN_BY" id="PNKN_BY" class="form-control"
                                    placeholder="{{ $row_request->nama_pnmt ?? '' }}" size="40" maxlength="100">
                                <input type="hidden" name="ID_PNKN_BY" id="ID_PNKN_BY"
                                    value="{{ $row_request->id_penumpukan ?? '' }}"></td>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Domestik : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="input" class="form-control" value="" name="DI" id="DI">
                            </div>
                        </div>
                    </div>


                    <div class="p-3">
                        <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Kapal</h4>

                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama Kapal: </label>
                            </div>
                            <div class="col-md-4">
                                <select name="NM_KAPAL" id="NM_KAPAL" class="form-control" style="width: 100%"
                                    value="{{ $row_request->nm_kapal ?? '' }}"></select>

                                <input id="TGL_MUAT" name="TGL_MUAT" type="hidden" />
                                <input id="NO_UKK" name="NO_UKK" type="hidden" />
                                <input id="TGL_STACKING" name="TGL_STACKING" type="hidden" />
                                <input id="TGL_BERANGKAT" name="TGL_BERANGKAT" type="hidden"
                                    value="{{ $row_request->tgl_berangkat ?? '' }}" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Voyage : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="VOYAGE_IN" name="VOYAGE_IN"
                                    value="{{ $row_request->voyage_in ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama Agen : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_AGEN" name="KD_AGEN" type="hidden" class="form-control" readonly />
                                <input id="NM_AGEN" name="NM_AGEN" type="text"
                                    value="{{ $row_request->nm_agen ?? '' }}" class="form-control" readonly />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Port Of Destination : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_PELABUHAN_ASAL" name="KD_PELABUHAN_ASAL" type="hidden"
                                    value="{{ $row_request->kd_pelabuhan_asal ?? '' }}" class="form-control" readonly />
                                <input id="NM_PELABUHAN_ASAL" name="NM_PELABUHAN_ASAL" type="text"
                                    value="{{ $row_request->nm_pelabuhan_asal ?? '' }}" title="Autocomplete"
                                    class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No PKK : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="NO_UKK" name="NO_UKK"
                                    readonly="readonly" title="Autocomplete" value="{{ $row_request->no_ukk ?? '' }}">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Final Discharge : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_PELABUHAN_TUJUAN" name="KD_PELABUHAN_TUJUAN" type="hidden"
                                    value="{{ $row_request->kd_pelabuhan_tujuan ?? '' }}" readonly />
                                <input class="form-control" id="NM_PELABUHAN_TUJUAN" name="NM_PELABUHAN_TUJUAN"
                                    type="text" value="{{ $row_request->nm_pelabuhan_tujuan ?? '' }}"
                                    title="Autocomplete" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Booking : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="NO_BOOKING" name="NO_BOOKING"
                                    value="{{ $row_request->no_booking ?? '' }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="p-3">
                        <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Dokumen Pendukung</h4>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No P.E.B : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor Dokumen : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No. N.P.E : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor JPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor D.O : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">BPRP : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor B.L : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor SPPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Tanggal SPPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Keterangan : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                    </div>

                    <div class="p-3">
                        <h4 class="card-title mb-3 pb-3 border-bottom">Entry No Container</h4>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Yard Stack : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="YARD_STACK" id="YARD_STACK"
                                    value="MTI" readonly="1">
                            </div>

                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Berbahaya : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="BERBAHAYA" id="BERBAHAYA" class="form-control">
                                    <option value="N">TIDAK</option>
                                    <option value="Y">YA</option>
                                </select>
                                <font color="red">* Harus Diisi</font>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Ex Batal SP2 : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="REMARK_SP2" id="REMARK_SP2" class="form-control">
                                    <option value="N">TIDAK</option>
                                    <option value="Y">YA</option>
                                </select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Seal : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" size="10" name="NO_SEAL"
                                    id="NO_SEAL">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor Container : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="NO_CONT" id="NO_CONT" class="form-control" style="width: 100%"></select>
                                </td>
                                <input class="form-control" type="hidden" name="BP_ID" id="BP_ID"
                                    readonly="readonly">
                                <input class="form-control" type="hidden" name="NO_UKK_CONT" id="NO_UKK_CONT"
                                    readonly="readonly">
                                <input class="form-control" type="hidden" name="TGL_STACK" id="TGL_STACK"
                                    readonly="readonly">
                                <input class="form-control" type="hidden" name="NO_REQ_SP2" id="NO_REQ_SP2"
                                    readonly="readonly">
                                <input class="form-control" type="hidden" name="SP2" id="SP2" value=""
                                    readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Commodity : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="COMMODITY" id="COMMODITY" class="form-control"
                                    style="width: 100%"></select>
                                <input class="form-control" type="hidden" name="KD_COMMODITY" id="KD_COMMODITY">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Ukuran : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" size="5" name="SIZE" id="SIZE"
                                    readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Stuffing Via : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="JENIS" id="JENIS" class="form-control">
                                    <option value="STUFFING_LAP">LAPANGAN</option>
                                    <option value="STUFFING_GUD_TONGKANG">GUDANG EKS TONGKANG</option>
                                    <option value="STUFFING_GUD_TRUCK">GUDANG EKS TRUCK</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Type : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" size="7" name="TYPE" id="TYPE"
                                    readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Status : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="STATUS" id="STATUS"
                                    readonly="readonly">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Asal Container : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="ASAL_CONT" id="ASAL_CONT"
                                    readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Depo Tujuan : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="NM_DEPO_TUJUAN" value="USTER"
                                    readonly="1">
                                <input class="form-control" type="text" name="DEPO_TUJUAN" value="1"
                                    readonly="1" hidden="1">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Tanggal Bongkar : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" size="17" name="TGL_BONGKAR"
                                    id="TGL_BONGKAR" readonly="readonly">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Berat : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="BERAT" id="BERAT">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Voyage / Kapal</label>
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" type="text" name="VOYAGE" id="VOYAGE"
                                    readonly="readonly" placeholder="LL08-0003">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control" name="VESSEL" id="VESSEL" readonly="readonly"
                                    placeholder="">
                                <input class="form-control" type="hidden" name="NO_BOOKING" id="NO_BOOKING"
                                    value="BPLL08202401150915162734">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Lokasi</label>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Block : </label>
                                    </div>
                                    <div class="col">
                                        <input class="form-control" size="4" type="text" name="BLOK"
                                            id="BLOK" readonly="readonly"> Slot :

                                    </div>
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">SLOT : </label>
                                    </div>
                                    <div class="col">
                                        <input class="form-control" size="4" type="text" name="SLOT"
                                            id="SLOT" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Penump Empty s/d</label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="TGL_EMPTY" id="TGL_EMPTY">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname"></label>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">ROW : </label>
                                    </div>
                                    <div class="col">
                                        <input class="form-control" size="4" type="text" name="ROW"
                                            id="ROW" readonly="readonly">

                                    </div>
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">TIER :</label>
                                    </div>
                                    <div class="col">
                                        <input class="form-control" size="4" type="text" name="TIER"
                                            id="TIER" readonly="readonly">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Keterangan : </label>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="text" name="KETERANGAN" size="30"
                                    id="KETERANGAN">
                            </div>
                        </div>
                    </div>
                    <div id="button_save" class="text-center my-3">
                        <button class="btn btn-info" onClick="add_cont()"><i class="fas fa-plus"></i> Tambah
                            Perencanaan
                            Stuffing</button>
                    </div>
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table class="datatables-service table " id="service-table">
                                <thead>
                                    <tr>
                                        <th>NO</th>
                                        <th>NO CONTAINER</th>
                                        <th>ASAL CONT</th>
                                        <th>SIZE</th>
                                        <th>TIPE</th>
                                        <th>HZ</th>
                                        <th>COMMODITY</th>
                                        <th>VIA START STACK EMPTY</th>
                                        <th>EMPTY S/D</th>
                                        <th>APPROVE</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    <div id="button_save" class="text-center my-3">
                        <button class="btn btn-info" onclick="save_request($('#total').val())"><i
                                class="fas fa-plus"></i>Simpan Request</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Informasi Stuffing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalContent" style="color:black"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('pages-js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#NM_KAPAL').select2({
                placeholder: 'Cari Nama Kapal',
                minimumInputLength: 3,
                ajax: {
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.master_vessel_palapa') !!}', // Adjust the route
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.vessel,
                                    text: item.vessel + ' | ' + item.voyage_in + ' / ' + item
                                        .voyage_out
                                };
                            })
                        };
                    }
                }
            });

            // If there's an initial value, set it in the select2 dropdown
            var initialVessel = "{{ $row_request->nm_kapal ?? '' }}";
            if (initialVessel) {
                // Manually create a new option and set it as selected
                var newOption = new Option(initialVessel, initialVessel, true, true);
                $('#NM_KAPAL').append(newOption).trigger('change');
            }

            // Handle select2 select event to populate the related fields
            $('#NM_KAPAL').on('select2:select', function(e) {
                var data = e.params.data;
                $("#NM_KAPAL").val(data.text);
                $("#KD_KAPAL").val(data.vessel_code);
                $("#VOYAGE").val(data.voyage);
                $("#ID_VSB_VOYAGE").val(data.id_vsb_voyage);
                $("#VESSEL_ID").val(data.vessel_code);
                $("#VOYAGE_IN").val(data.voyage_in);
                $("#VOYAGE_OUT").val(data.voyage_out);
                $("#KD_KAPAL").val(data.vessel_code);
                $("#NM_AGEN").val(data.operator_name);
                $("#KD_AGEN").val(data.operator_id);
                $("#NO_UKK").val(data.id_vsb_voyage);
                if (data.vessel_code && data.id_vsb_voyage) {
                    $("#NO_BOOKING").val(`bp${data.vessel_code}${data.id_vsb_voyage}`);
                }
                $("#TGL_BERANGKAT").val(data.atd);
                $("#TGL_STACKING").val(data.open_stack);
                $("#TGL_MUAT").val(data.closing_time_doc);
                $("#KD_PELABUHAN_ASAL").val(data.id_pol);
                $("#KD_PELABUHAN_TUJUAN").val(data.id_pod);
                $("#NM_PELABUHAN_ASAL").val(data.pol);
                $("#NM_PELABUHAN_TUJUAN").val(data.pod);
                return false;
            });
        });

        $(document).ready(function() {
            $('#COMMODITY').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.getCommodityByName') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const arrs = data;
                        return {
                            results: arrs.map((arr, i) => ({
                                id: arr.kd_commodity,
                                text: arr.nm_commodity,
                                ...arr
                            }))
                        };
                    }
                }
            });

            $('#COMMODITY').on('select2:select', function(e) {
                var data = e.params.data;
                $("#COMMODITY").val(data.nm_commodity);
                $("#KD_COMMODITY").val(data.kd_commodity);
                $("#TGL_EMPTY").focus();
            })
            $('#COMMODITY').on('select2:clear', function(e) {
                //
            })
        });

        $(document).ready(function() {
            if ($("#YARD_STACK").val() == 'TPK') {
                $('#NO_CONT').select2({
                    minimumInputLength: 3, // Set the minimum input length
                    ajax: {
                        // Implement your AJAX settings for data retrieval here
                        url: '{!! route('uster.new_request.stuffing.stuffing_plan.getContainerTPKByName') !!}',
                        dataType: 'json',
                        processResults: function(data) {
                            const arrs = data;
                            return {
                                results: arrs.map((arr, i) => ({
                                    id: arr.no_container,
                                    text: arr.no_container + ' | ' + arr.status,
                                    ...arr
                                }))
                            };
                        }
                    }
                });


                $('#NO_CONT').on('select2:select', function(e) {
                    var data = e.params.data;
                    $("#NO_CONT").val(data.containerNo);
                    $("#SIZE").val(data.containerSize);
                    $("#STATUS").val(data.containerStatus);
                    $("#TYPE").val(data.containerType);
                    $("#VOYAGE").val(data.voyage);
                    $("#VESSEL").val(data.vesselName);
                    $("#TGL_BONGKAR").val(data.vesselConfirm);
                    $("#TGL_STACK").val(data.tgl_stacl);
                    $("#NO_UKK_CONT").val(data.no_ukk);
                    $("#NM_AGEN").val(data.nm_agen);
                    $("#BP_ID").val(data.bp_id);
                    $("#BLOK").val(data.ydblock);
                    $("#SLOT").val(data.ydslot);
                    $("#ROW").val(data.ydrow);
                    $("#TIER").val(data.ydtier);
                    $("#TGL_EMPTY").val(data.empty_sd);
                    $("#ASAL_CONT").val('TPK');
                    $("#NO_REQ_SP2").val(data.no_request);
                    $("#TGL_EMPTY").val();
                    $("#COMMODITY").focus();

                    return false;


                })
                $('#NO_CONT').on('select2:clear', function(e) {
                    //
                })
            } else {
                $('#NO_CONT').select2({
                    minimumInputLength: 3, // Set the minimum input length
                    ajax: {
                        // Implement your AJAX settings for data retrieval here
                        url: '{!! route('uster.new_request.stuffing.stuffing_plan.getContainerByName') !!}',
                        dataType: 'json',
                        processResults: function(data) {
                            const arrs = data;
                            return {
                                results: arrs.map((arr, i) => ({
                                    id: arr.no_container,
                                    text: arr.no_container + ' | ' + arr.kd_type + ' | ' +
                                        arr.kd_size,
                                    ...arr
                                }))
                            };
                        }
                    }
                });


                $('#NO_CONT').on('select2:select', function(e) {
                    var data = e.params.data;
                    $("#NO_CONT").val(data.no_container);
                    $("#SIZE").val(data.kd_size);
                    $("#STATUS").val(data.status_cont);
                    $("#TYPE").val(data.kd_type);
                    $("#VOYAGE").val(data.voyage_in);
                    $("#VESSEL").val(data.nm_kapal);
                    $("#TGL_BONGKAR").val(data.tgl_bongkar);
                    $("#TGL_STACK").val(data.tgl_stack);
                    $("#NO_UKK").val(data.no_ukk);
                    $("#NM_AGEN").val(data.nm_agen);
                    $("#BP_ID").val(data.bp_id);
                    $("#BLOK").val(data.block_);
                    $("#SLOT").val(data.slot_);
                    $("#ROW").val(data.row_);
                    $("#TIER").val(data.tier_);
                    $("#TGL_EMPTY").val(data.empty_sd);
                    $("#ASAL_CONT").val(data.asal_cont);
                    if (data.asal_cont == 'DEPO') {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });

                        // Melakukan permintaan AJAX dengan token CSRF
                        $.post('{{ route('uster.new_request.stuffing.stuffing_plan.getTanggalStack') }}', {
                            no_cont: data.no_container
                        }, function(data) {
                            // Menangani respons dari server
                            $("#TGL_BONGKAR").val(data);
                        });
                    }
                    $("#COMMODITY").focus();
                    return false;


                })
                $('#NO_CONT').on('select2:clear', function(e) {
                    //
                })
            }


        });
    </script>

    <script>
        function add_cont() {

            var no_cont_ = $("#NO_CONT").val();
            var no_req_stuf = "{{ $row_request->no_request }}";
            var no_req_rec = "{{ $row_request->no_request_receiving }}";
            var no_req_del = "";
            var no_req_ict_ = $("#NO_REQUEST2").val();
            var size_ = $("#SIZE").val();
            var type_ = $("#TYPE").val();
            var status = $("#STATUS").val();
            var berbahaya_ = $("#BERBAHAYA").val();
            var commodity_ = $("#COMMODITY").val();
            var kd_commodity_ = $("#KD_COMMODITY").val();
            var jenis_ = $("#JENIS").val();
            var no_seal = $("#NO_SEAL").val();
            var berat = $("#BERAT").val();
            var keterangan = $("#KETERANGAN").val();
            var no_booking_ = $("#NO_BOOKING").val();
            var no_ukk_ = "202401150915162734";
            var no_ukk_cont = $("#NO_UKK_CONT").val();
            var bp_id = $("#BP_ID").val();
            var sp2 = $("#SP2").val();
            var tgl_stack = $("#TGL_STACK").val();
            var voyage = $("#VOYAGE").val();
            var vessel = $("#VESSEL").val();
            var tgl_bongkar = $("#TGL_BONGKAR").val();
            var depo_tujuan = $("#DEPO_TUJUAN").val();
            var no_do = $("#NO_DO").val();
            var no_bl = $("#NO_BL").val();
            var no_sppb = $("#NO_SPPB").val();
            var tgl_sppb = $("#TGL_SPPB").val();
            var blok_cont = $("#BLOK").val();
            var slot_cont = $("#SLOT").val();
            var rows_cont = $("#ROW").val();
            var tier_cont = $("#TIER").val();
            var ASAL_CONT = $("#ASAL_CONT").val();
            var TGL_EMPTY = $("#TGL_EMPTY").val();
            var EARLY_STUFF = $("#EARLY_STUFF").val();
            var remark_sp2 = $("#REMARK_SP2").val();
            var no_req_sp2 = $("#NO_REQ_SP2").val();

            var urlcek = '{{ route('uster.new_request.stuffing.stuffing_plan.CheckCapacityTPK') }}';
            var no_booking_ = $("#NO_BOOKING").val();

            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda yakin ingin Menambahkan kontainer?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Tambahkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    if ($("#TGL_EMPTY").val() == '') {
                        // Menggunakan SweetAlert2 untuk menampilkan pesan pop-up
                        Swal.fire({
                            icon: 'info',
                            text: 'Tgl Penumpukan Empty s/d Harus Diisi!',
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                        $("#TGL_EMPTY").focus();
                        return false;
                    }

                    $.get(urlcek, {
                        no_booking: no_booking_
                    }, function(data) {
                        if (data == 'T') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kapasitas Booking Stack TPK Tidak Mencukupi!',
                                timer: 1000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            });

                        } else {

                            var url =
                                "{{ route('uster.new_request.stuffing.stuffing_plan.addContainer') }}";

                            Swal.fire({
                                title: 'Menambahkan Data Container...',
                                allowOutsideClick: false,
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            $.post(url, {
                                NO_CONT: no_cont_,
                                NO_REQ_STUF: no_req_stuf,
                                NO_REQ_REC: no_req_rec,
                                NO_REQ_DEL: no_req_del,
                                NO_REQ_ICT: no_req_ict_,
                                SIZE: size_,
                                TYPE: type_,
                                STATUS: status,
                                COMMODITY: commodity_,
                                KD_COMMODITY: kd_commodity_,
                                JENIS: jenis_,
                                BERBAHAYA: berbahaya_,
                                NO_SEAL: no_seal,
                                BERAT: berat,
                                KETERANGAN: keterangan,
                                NO_BOOKING: no_booking_,
                                NO_UKK: no_ukk_,
                                BP_ID: bp_id,
                                SP2: sp2,
                                TGL_STACK: tgl_stack,
                                VOYAGE: voyage,
                                VESSEL: vessel,
                                TGL_BONGKAR: tgl_bongkar,
                                DEPO_TUJUAN: depo_tujuan,
                                NO_DO: no_do,
                                NO_BL: no_bl,
                                NO_SPPB: no_sppb,
                                TGL_SPPB: tgl_sppb,
                                BLOK: blok_cont,
                                SLOT: slot_cont,
                                ROW: rows_cont,
                                TIER: tier_cont,
                                ASAL_CONT: ASAL_CONT,
                                TGL_EMPTY: TGL_EMPTY,
                                EARLY_STUFF: EARLY_STUFF,
                                REMARK_SP2: remark_sp2,
                                NO_REQ_SP2: no_req_sp2,
                                ID_VSB: no_ukk_cont
                            }, function(data) {

                                if (data == "EXIST") {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Container Sudah terdaftar di request lain',
                                        text: 'Silahkan cek di history container'
                                    });
                                } else if (data == "BERBAHAYA") {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Status Berbahaya belum diisi'
                                    });
                                } else if (data == "EXIST_DEL") {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Masih Aktif di Request Delivery'
                                    });
                                } else if (data == "EXIST_REC") {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Container Masih Aktif di Req Receiving / Belum Gate In'
                                    });
                                } else if (data == "BELUM_REC") {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Container Belum IN'
                                    });
                                } else if (data == "SUDAH_REQUEST") {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Maaf no container ' + no_cont_ +
                                            ' telah mengajukan request stuffing'
                                    });
                                } else if (data == "OK") {
                                    $('#service-table').DataTable().ajax
                                        .reload();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Container Berhasil Di Tambahkan'
                                    });

                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Maaf Terjadi Error Saat Menambahkan Data Container ' +
                                            no_cont_,
                                        text: 'Silahkan Menghubungi Administrator'
                                    });
                                }



                            });
                        }
                    });
                }
            });
        }

        function del_cont($no_cont, $no_req_sp2) {


            var no_req_ = "{{ $row_request->no_request }}";
            var no_req2 = $("#NO_REQUEST2").val();
            var url = "{{ route('uster.new_request.stuffing.stuffing_plan.deleteContainer') }}";

            // Menampilkan konfirmasi sebelum penghapusan
            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda yakin ingin menghapus kontainer?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus Data Container...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    // Jika pengguna mengonfirmasi penghapusan, maka kirim permintaan AJAX untuk menghapus kontainer
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.post(url, {
                        NO_CONT: $no_cont,
                        NO_REQ: no_req_,
                        NO_REQ2: no_req2,
                        NO_REQ_SP2: $no_req_sp2
                    }, function(data) {
                        if (data == "OK") {
                            $('#service-table').DataTable().ajax
                                .reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil menghapus kontainer ' + $no_cont
                            });
                        } else {
                            $('#service-table').DataTable().ajax
                                .reload();
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal menghapus kontainer ' + $no_cont,
                                text: data
                            });
                        }

                    });
                }
            });
        }

        //Date Picker
        $(function() {

            $("#tgl_req_dev").datepicker();
            $("#tgl_req_dev").datepicker("option", "dateFormat", "yy-mm-dd");

            //$( "#TGL_EMPTY" ).datepicker();
            //$( "#TGL_EMPTY" ).datepicker({ minDate: 0, dateFormat: 'dd-mm-yy'});
            // $( "#TGL_EMPTY" ).datepicker({ dateFormat: 'dd-mm-yy'});
            $("#TGL_EMPTY").datepicker({
                dateFormat: 'dd-mm-yy',
                minDate: new Date()
            });

            $("#TGL_EARLY_STUFF").datepicker({
                dateFormat: 'dd-mm-yy'
            });

            $("#ID_TGL_MULAI").datepicker();
            $("#ID_TGL_MULAI").datepicker("option", "dateFormat", "yy-mm-dd");
            $("#ID_TGL_MULAI").val('');

            $("#ID_TGL_NANTI").datepicker();
            $("#ID_TGL_NANTI").datepicker("option", "dateFormat", "yy-mm-dd");
            $("#ID_TGL_NANTI").val('');

            $("#TGL_REQ").datepicker();
            $("#TGL_REQ").datepicker("option", "dateFormat", "yy-mm-dd");

            //$( "#TGL_SPPB" ).val('');
            $("#TGL_SPPB").datepicker();
            $("#TGL_SPPB").datepicker("option", "dateFormat", "yy-mm-dd");
            $("#TGL_SPPB").val('');

        });

        function update_tgl_approve($no_cont, $tgl_approve, $asal_cont) {

            // Menampilkan konfirmasi sebelum penghapusan
            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda yakin ingin menghapus kontainer?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menambahkan Data Container...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    if ($("#YARD_STACK").val() == 'TPK') {
                        console.log('TPK');
                    } else {
                        var no_req_ict_ = $("#NO_REQUEST2").val();
                        var no_req_ict2_ = $("#NO_REQUEST3").val();
                        var no_do = $("#NO_DO").val();
                        var no_bl = $("#NO_BL").val();
                        var no_booking_ = $("#NO_BOOKING").val();
                        var sp2 = $("#SP2").val();
                        var no_req = $("#no_req").val();
                        var no_req_rec = "REC0224000963";
                        var no_req_del = "";
                        var url = '{{ route('uster.new_request.stuffing.stuffing_plan.containerApprove') }}';

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.post(url, {
                            NO_REQ_REC: no_req_rec,
                            NO_REQ_DEL: no_req_del,
                            tgl_approve: $tgl_approve,
                            no_cont: $no_cont,
                            no_req: no_req,
                            NO_REQ_ICT: no_req_ict_,
                            NO_REQ_ICT2: no_req_ict2_,
                            NO_DO: no_do,
                            NO_BL: no_bl,
                            SP2: sp2,
                            NO_BOOKING: no_booking_,
                            ASAL_CONT: 'DEPO'
                        }, function(data) {
                            if (data == "OK") {
                                $('#service-table').DataTable().ajax
                                    .reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Menambah kontainer ' + $no_cont
                                });
                            } else {
                                $('#service-table').DataTable().ajax
                                    .reload();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal menghapus kontainer ' + $no_cont,
                                    text: data
                                });
                            }
                        });
                    }
                }
            });
        }
    </script>
@endsection

@push('after-script')
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.overview.datatable', request('no_req')) !!}',
                    type: 'GET',
                    data: function(d) {
                        d.active = 'view'
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
                        data: 'asal_cont',
                        name: 'asal_cont'
                    },
                    {
                        data: 'kd_size',
                        name: 'kd_size'
                    },
                    {
                        data: 'kd_type',
                        name: 'kd_type',

                    },
                    {
                        data: 'hz',
                        name: 'hz'
                    },
                    {
                        data: 'commodity',
                        name: 'commodity',
                    },
                    {
                        data: 'type_stuffing',
                        name: 'type_stuffing',
                    },
                    {
                        data: 'tgl_approve_input',
                        name: 'tgl_approve_input',
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return $('<div />').html(data).text(); // decode HTML entities
                        }
                    },
                    {
                        data: 'approve_input',
                        name: 'approve_input',
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return $('<div />').html(data).text(); // decode HTML entities
                        }
                    },
                    {
                        data: 'delete_input',
                        name: 'delete_input',
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return $('<div />').html(data).text(); // decode HTML entities
                        }
                    },
                ],
            });
        });

        function info_lapangan() {

            Swal.fire({
                title: 'Mendapatkan Data...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('uster.new_request.stuffing.stuffing_plan.infoContainerStuffingPlan') }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Update the modal content with the received data
                    $('#infoModal').modal('show');
                    $("#modalContent").html(response);
                    Swal.close();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.log("Error:", error);
                    Swal.close();
                }
            });
        }
    </script>
@endpush
