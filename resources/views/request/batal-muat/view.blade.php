@extends('layouts.app')

@section('title')
    Batal Muat
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
                <li class="breadcrumb-item"><a href="{{ route('uster.koreksi.batal_muat') }}">Batal Muat</a>
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
                    <h3><b>Batal Muat</b></h3>

                    <form action="javascript:void(0)" id="requestStuffingPlan" class="form-horizontal m-t-20" novalidate>
                        @csrf
                        <div class="p-3">

                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Pengguna Jasa</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nama Pemilik : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" value ="{{ $row_request->nm_pbm }}" type="text"
                                        placeholder="Autocomplete" id="NM_PELANGGAN" name="NM_PELANGGAN"
                                        style="width:78%;" />
                                    <input class="form-control" value ="{{ $row_request->kd_emkl }}" type="text"
                                        id="KD_PELANGGAN" name="KD_PELANGGAN" style="width:20%;" readonly="1" /></td>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Jenis Batal Muat : </label>
                                </div>
                                <div class="col-md-4">

                                    {!! $jenis_bm !!}

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Dikenakan Biaya : </label>
                                </div>
                                <div class="col-md-4">
                                    {!! $biaya !!}
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Ex Kegiatan : </label>
                                </div>
                                <div class="col-md-4">
                                    {!! $status_gate !!}
                                </div>
                            </div>
                        </div>


                        <div class="p-3">
                            <h4 class="card-title mb-3 pb-3 border-bottom">Data Kapal Baru</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nama Kapal : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_KAPAL" name="KD_KAPAL" type="hidden"
                                        value="{{ $rwk->kd_kapal ?? '' }}" size="40" />
                                    <input class="form-control" id="NM_KAPAL" name="NM_KAPAL" type="text"
                                        value="{{ $rwk->nm_kapal ?? '' }}" size="40" />
                                    <input class="form-control" id="TGL_BERANGKAT" name="TGL_BERANGKAT" type="hidden"
                                        value="{{ $rwk->tgl_berangkat ?? '' }}" size="40" />
                                    <input class="form-control" id="TGL_STACKING" name="TGL_STACKING" type="hidden"
                                        value="{{ $rwk->tgl_stacking ?? '' }}" size="40" />
                                    <input class="form-control" id="TGL_MUAT" name="TGL_MUAT" type="hidden"
                                        value="{{ $rwk->tgl_muat ?? '' }}" size="40" />
                                    <input class="form-control" id="NO_BOOKING" name="NO_BOOKING" type="hidden"
                                        size="40" />
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Voyage : </label>
                                </div>
                                <div class="col-md-4">

                                    <input class="form-control"id="VOYAGE_IN" name="VOYAGE_IN" type="text"
                                        value="{{ $rwk->voyage_in ?? '' }}" readonly="1" />

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">ETA : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="ETA" name="ETA"
                                        value="{{ $rwk->eta ?? '' }}" type="text" readonly="1" />

                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">ETD : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="ETD" name="ETD"
                                        value="{{ $rwk->etd ?? '' }}" type="text" readonly="1" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Port Of Destination : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_PELABUHAN_ASAL" name="KD_PELABUHAN_ASAL"
                                        type="hidden" value="{{ $rwk->kd_pelabuhan_asal ?? '' }}" class="pod"
                                        readonly="1" />
                                    <input class="form-control" id="NM_PELABUHAN_ASAL" name="NM_PELABUHAN_ASAL"
                                        type="text" value="{{ $rwk->pelabuhan_asal ?? '' }}" maxlength="100"
                                        title="Autocomplete" class="pod" />
                                    <input class="form-control" id="id_TGL_STACK" name="TGL_STACK" type="hidden"
                                        value="{{ $tglskr ?? '' }}" size="19" maxlength="19" />
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Final Discharge : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" id="KD_PELABUHAN_TUJUAN" name="KD_PELABUHAN_TUJUAN"
                                        type="hidden" value="{{ $rwk->kd_pelabuhan_tujuan ?? '' }}" class="pod2"
                                        readonly="1" />
                                    <input class="form-control" id="NM_PELABUHAN_TUJUAN" name="NM_PELABUHAN_TUJUAN"
                                        type="text" value="{{ $rwk->pelabuhan_tujuan  ?? ''}}" maxlength="100"
                                        class="pod2" title="Autocomplete" />
                                </div>
                            </div>



                        </div>



                        <div class="p-3">
                            <h4 class="card-title mb-3 pb-3 border-bottom">Data Petikemas</h4>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Nomor Container : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" disabled="1" type="text" name="NO_CONT"
                                        ID="NO_CONT" placeholder="Autocomplete" />
                                    <input class="form-control" id="NO_REQUEST" name="NO_REQUEST" type="hidden" />
                                    <input class="form-control" id="NO_REQ_ICT" name="NO_REQ_ICT" type="hidden" />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Ukuran : </label>
                                </div>
                                <div class="col-md-4">

                                    <input class="form-control" type="text" name="SIZE" id="SIZE"
                                        readonly="readonly" />

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Type : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="TYPE" id="TYPE"
                                        readonly="readonly" />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Status : </label>
                                </div>
                                <div class="col-md-4">

                                    <input class="form-control" type="text" name="STATUS" id="STATUS"
                                        readonly="readonly" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Tgl Penumpukan : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="TGL_PNKN_START" id="TGL_PNKN_START"
                                        readonly="readonly" />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">s/d : </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="text" name="TGL_PNKN_END" id="TGL_PNKN_END"
                                        readonly="readonly" />
                                </div>
                            </div>
                        </div>
                        <div class="card p-2">
                            <div class="table-responsive">
                                <table class="display nowrap table data-table" cellspacing="0" width="100%"
                                    id="service-table">
                                    <thead>
                                        <tr>
                                            <th>NO</th>
                                            <th>No. Petikemas</th>
                                            <th>Size</th>
                                            <th>Status</th>
                                            <th>Type</th>
                                            <th>Tanggal Awal</th>
                                            <th>Tanggal Akhir</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <a href="{{route('uster.koreksi.batal_muat')}}" class="btn btn-primary float-right">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush


@section('pages-js')
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.koreksi.batal_muat.viewContainerByRequest', ['no_req' => request('no_req')]) !!}',
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
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'start_pnkn',
                        name: 'start_pnkn'
                    },
                    {
                        data: 'end_pnkn',
                        name: 'end_pnkn'
                    },

                ],
            });
        });
    </script>
@endsection
