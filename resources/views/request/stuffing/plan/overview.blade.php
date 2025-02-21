@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.stuffing.stuffing_plan')?? ''}}">Stuffing Plan</a></li>
                <li class="breadcrumb-item active">Overview</li>
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
                            <div class="col">
                                <input type="text" class="form-control" id="no_req" name="NO_REQ"
                                    value="{{ $row_request->no_request ?? ''}}" readonly="readonly">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="NO_REQUEST2" id="NO_REQUEST2"
                                    value="{{ $no_req2 ?? ''}}">
                            </div>
                             <div class="col-md-3">
                                <input type="text" class="form-control" name="NO_REQUEST3" id="NO_REQUEST3" value="{{$no_req3?? ''}}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama EMKL : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="EMKL" id="EMKL"
                                    placeholder="{{ $row_request->nama_emkl ?? ''}}">
                                <input type="hidden" name="ID_EMKL" id="ID_EMKL"
                                    value="{{ $row_request->id_emkl ?? ''}}" />
                            </div>
                        </div>
                    </div>


                    <div class="p-3">
                        <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Kapal</h4>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama Kapal : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="NM_KAPAL" name="NM_KAPAL"
                                    value="{{ $row_request->nm_kapal ?? ''}}" style="background-color:#FFFFCC;"
                                    title="Autocomplete">
                                <input id="TGL_MUAT" name="TGL_MUAT" type="hidden"
                                    value="{{ $row_request->tgl_muat ?? ''}}" />
                                <input id="NO_UKK" name="NO_UKK" type="hidden" />
                                <input id="TGL_STACKING" name="TGL_STACKING" type="hidden"
                                    value="{{ $row_request->tgl_stacking ?? ''}}" />
                                <input id="TGL_BERANGKAT" name="TGL_BERANGKAT" type="hidden"
                                    value="{{ $row_request->tgl_berangkat ?? ''}}" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Voyage : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="VOYAGE_IN" name="VOYAGE_IN"
                                    value="{{ $row_request->voyage_in ?? ''}}" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nama Agen : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_AGEN" name="KD_AGEN" type="hidden" class="form-control" readonly />
                                <input id="NM_AGEN" name="NM_AGEN" type="text" value="{{ $row_request->nm_agen ?? ''}}"
                                    class="form-control" readonly />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Port Of Destination : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_PELABUHAN_ASAL" name="KD_PELABUHAN_ASAL" type="hidden"
                                    value="{{ $row_request->kd_pelabuhan_asal ?? ''}}" class="form-control" readonly />
                                <input id="NM_PELABUHAN_ASAL" name="NM_PELABUHAN_ASAL" type="text"
                                    value="{{ $row_request->nm_pelabuhan_asal ?? ''}}" title="Autocomplete"
                                    class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No PKK : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="NO_UKK" name="NO_UKK"
                                    readonly="readonly" title="Autocomplete">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Final Discharge : </label>
                            </div>
                            <div class="col-md-4">
                                <input id="KD_PELABUHAN_TUJUAN" name="KD_PELABUHAN_TUJUAN" type="hidden"
                                    value="{{ $row_request->kd_pelabuhan_tujuan ?? ''}}" readonly />
                                <input class="form-control" id="NM_PELABUHAN_TUJUAN" name="NM_PELABUHAN_TUJUAN"
                                    type="text" value="{{ $row_request->nm_pelabuhan_tujuan ?? ''}}"
                                    title="Autocomplete" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Booking : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="NO_BOOKING" name="NO_BOOKING"  value="{{$row_request->no_booking?? ''}}"   readonly >
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
                                        <th>VIA</th>
                                        <th>START STACK EMPTY</th>
                                        <th>EMPTY S/D</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.overview.datatable',request('no_req')) !!}',
                    type: 'GET',
                    data: function(d) {
                        d.active = 'overview';
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
                        data: 'start_stack',
                        name: 'start_stack',
                    },
                    {
                        data: 'tgl_approve_input',
                        name: 'tgl_approve_input',
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return $('<div />').html(data).text(); // decode HTML entities
                        }
                    },
                  
                ],
            });
        });
    </script>
@endpush
