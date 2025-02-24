@extends('layouts.app')

@section('title')
Perencanaan Kegiatan Delivery
@endsection


@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">New Request</a></li>
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.new_request.delivery.delivery_luar.index')}}">Delivery Ke Luar TPK</a></li>
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
                <h3><b>Delivery ke Luar TPK</b></h3>

                <div class="p-3">
                    <div class="row">
                        <div class="col-md-2 py-2">
                            <label for="tb-fname">Vessel/Voyage : </label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="VESSEL" id="VESSEL" value="{{ $data['row_request']->vessel}}" readonly>
                            <input type="text" class="form-control" name="VOYAGE" id="VOYAGE" value="{{ $data['row_request']->voyage}}" readonly>
                        </div>
                        <div class="col-md-2 py-2">
                            <label for="tb-fname">NO PEB - NPE : </label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="peb" class="form-control" value="{{ $data['row_request']->peb}}" readonly />
                            <input type="text" name="npe" class="form-control" value="{{ $data['row_request']->npe}}" readonly />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 py-2">
                            <label for="tb-fname">E.M.K.L :</label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="EMKL" id="EMKL" value="{{ $data['row_request']->nama_emkl}}" readonly>
                            <input type="hidden" class="form-control" name="ID_EMKL" id="ID_EMKL">
                        </div>
                        <div class="col-md-2 py-2">
                            <label for="tb-fname">Keterangan : </label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" value="{{$data['row_request']->keterangan}}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 py-2">
                            <label for="tb-fname">Tanggal Request Delivery : </label>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="tgl_dev" id="tgl_dev" class="form-control" value="{{$data['row_request']->tgl_request_delivery}}" readonly>
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
                                    <th>STATUS</th>
                                    <th>UKURAN</th>
                                    <th>TIPE</th>
                                    <th>KOMODITI</th>
                                    <th>NO SEAL</th>
                                    <th>BERAT</th>
                                    <th>VIA</th>
                                    <th>AWAL STACK</th>
                                    <th>ETD</th>
                                    <th>HZ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['result_table'] as $key => $tbl)
                                <tr>
                                    <td>{{$key + 1}}</td>
                                    <td>{{$tbl->no_container}}</td>
                                    <td>{{$tbl->status}}</td>
                                    <td>{{$tbl->size_}}</td>
                                    <td>{{$tbl->type_}}</td>
                                    <td>{{$tbl->komoditi}}</td>
                                    <td>{{$tbl->no_seal}}</td>
                                    <td>{{$tbl->berat}}</td>
                                    <td>{{$tbl->via}}</td>
                                    <td>{{$tbl->start_stack}}</td>
                                    <td>{{$tbl->tgl_delivery}}</td>
                                    <td>{{$tbl->hz}}</td>
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
@endsection