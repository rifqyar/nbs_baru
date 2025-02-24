@extends('layouts.app')

@section('title')
Preview Nota
@endsection


@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Billing</a></li>
            <li class="breadcrumb-item">Nota elivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.billing.notadelivery.index')}}">Delivery Ke Luar</a></li>
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
                <h3><b>Nota Delivery - SP2</b></h3>

                <div class="card">
                    <div class="card-body">
                        <p>PT. Multi Terminal Indonesia</p>
                        <div class="col align-self-end">
                            <p class="text-right">No. Nota : {{$rnota ?? '-'}}</p>
                            <p class="text-right">No. Doc : {{$no_req}}</p>
                            <p class="text-right">Tgl. Proses : {{$tgl_nota}}</p>
                        </div>
                        <center>
                            <p>PERHITUNGAN PELAYANAN JASA <br /> KEGIATAN DELIVERY </p>
                        </center>
                        <hr>
                        <p>NAMA PERUSAHAAN :{{$row_nota->emkl}}</p>
                        <p>NPWP : {{$row_nota->npwp}}</p>
                        <p>ALAMAT : {{$row_nota->alamat}}</p>
                        <hr>
                        <table class="table">
                            <thead>
                                <th>KETERANGAN</th>
                                <th>TGL AWAL</th>
                                <th>TGL AKHIR</th>
                                <th>BOX</th>
                                <th>SZ</th>
                                <th>TY</th>
                                <th>ST</th>
                                <th>HZ</th>
                                <th>HR</th>
                                <th>TARIF</th>
                                <th>VAL</th>
                                <th>JUMLAH</th>
                            </thead>
                            <tbody>
                                @foreach($row_detail as $rw)
                                <tr>
                                    <td>{{$rw->keterangan}}</td>
                                    <td>{{$rw->start_stack}}</td>
                                    <td>{{$rw->end_stack}}</td>
                                    <td>{{$rw->jml_cont}}</td>
                                    <td>{{$rw->size_}}</td>
                                    <td>{{$rw->type_}}</td>
                                    <td>{{$rw->status}}</td>
                                    <td>{{$rw->hz}}</td>
                                    <td>{{$rw->jml_hari}}</td>
                                    <td>{{$rw->tarif}}</td>
                                    <td>IDR</td>
                                    <td>{{$rw->biaya}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <hr>
                        <div class="col align-self-end">
                            <p class="text-right">Administrasi : {{$row_adm->adm ?? '-'}}</p>
                            <p class="text-right">Dasar Pengenaan Pajak : {{$row_tot->total_all}}</p>
                            <p class="text-right">Jumlah PPN : {{$row_ppn->ppn}}</p>
                            @if($bea_materai > 0)
                            <p class="text-right">Bea Materai : {{$row_materai->materai}}</p>
                            @endif
                            <p class="text-right">Jumlah Dibayar : {{$row_bayar->total_bayar}}</p>
                        </div>
                        <p><b>Nota sebagai Faktur Pajak berdasarkan Peraturan Dirjen Pajak <br>
                                Nomor 13/PJ/2019 Tanggal 2 Juli 2019</b></p>
                        <div class="col align-self-end">
                            <p class="text-right">{{$nama_peg->jabatan}}</p>
                            <br>
                            <br>

                            <p class="text-right mr-5 pr-5">{{$nama_peg->nama_pegawai}}</p>
                        </div>
                    </div>
                </div>
                <a onclick="save_nota()" style="cursor:pointer" target="_blank" class="btn btn-info text-white" border='0'>
                    PRINT NOTA
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pages-js')
<script>
    function save_nota() {
        if ("{{$jenis}}" == 'gerak')
            window.open("{{route('uster.billing.notadelivery.printproforma')}}" + "?no_req=" + "{{$no_req}}" + "&koreksi='N'", "_self");
        else
            window.open("{{route('uster.billing.notadelivery.printproformapnkn')}}" + "?no_req=" + "{{$no_req}}" + "&koreksi='N'", "_self");
    }
</script>
@endsection