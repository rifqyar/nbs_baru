@extends('layouts.app')

@section('title')
    Nota Stripping
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
@endpush

@section('content')
    @if (Session::has('error'))
        <div class="alert alert-danger fade show" role="alert">
            <h4 class="mb-0 text-center">
                <span class="text-size-5">
                    {!! Session::get('error') !!}
                </span>
                <i class="fas fa-exclamation-circle"></i>
            </h4>
        </div>
    @endif

    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Nota Stripping</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Billing</a></li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item">Nota Stripping</li>
                <li class="breadcrumb-item active">Preview Nota</li>
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
                    <h3 class="text-center font-bold">Preview Nota</h3>
                    <div class="card mt-3 p-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <h5 class="font-bold">PT. Multi Terminal Indonesia</h5>
                                </div>

                                <div class="col-lg-8 col-md-12">&nbsp;</div>
                                {{-- Data Nota --}}
                                <div class="col-lg-4 col-md-12">
                                    <div class="row justify-content-end align-items-start">
                                        <div class="col-lg-5 col-md-11">
                                            <h6 class="font-bold text-right">No. Nota</h6>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                            <span>:</span>
                                        </div>
                                        <div class="col-lg-6 col-md-12">
                                            -
                                        </div>
                                    </div>
                                    <div class="row justify-content-end align-items-center">
                                        <div class="col-lg-5 col-md-11">
                                            <h6 class="text-right">No. Doc</h6>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                            <span>:</span>
                                        </div>
                                        <div class="col-lg-6 col-md-12 text-right">
                                            {{ $no_req }}
                                        </div>
                                    </div>

                                    <div class="row justify-content-end align-items-center">
                                        <div class="col-lg-5 col-md-11">
                                            <h6 class="text-right">Tgl. Proses</h6>
                                        </div>
                                        <div class="cocol-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                            <span>:</span>
                                        </div>
                                        <div class="col-lg-6 col-md-12 text-right">
                                            {{ $tgl_req }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4 pt-3">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <h6 class="text-center font-medium">PERHITUNGAN PELAYANAN JASA <br> KEGIATAN
                                            RECEIVING</h6>
                                    </div>
                                </div>
                            </div>

                            <hr class="w-100" style="border: 0.5px solid #000000">

                            {{-- Header Nota --}}
                            <div class="row align-items-center">
                                <div class="col-lg-3 col-md-11">
                                    <span class="text-dark font-medium">Nama Perusahaan</span>
                                </div>
                                <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                    <span class="text-dark font-medium">:</span>
                                </div>
                                <div class="col-lg-8 col-md-12">
                                    {{ $row_nota->emkl }}
                                </div>

                                <div class="col-lg-3 col-md-11">
                                    <span class="text-dark font-medium">NPWP</span>
                                </div>
                                <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                    <span class="text-dark font-medium">:</span>
                                </div>
                                <div class="col-lg-8 col-md-12">
                                    {{ $row_nota->npwp }}
                                </div>

                                <div class="col-lg-3 col-md-11">
                                    <span class="text-dark font-medium">Alamat</span>
                                </div>
                                <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">
                                    <span class="text-dark font-medium">:</span>
                                </div>
                                <div class="col-lg-8 col-md-12">
                                    {{ $row_nota->alamat }}
                                </div>
                            </div>

                            <hr class="w-100" style="border: 0.5px solid #000000">

                            {{-- Detail Nota --}}
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Keterangan</th>
                                            <th>Tgl. Awal</th>
                                            <th>Tgl. Akhir</th>
                                            <th>Box</th>
                                            <th>SZ</th>
                                            <th>TY</th>
                                            <th>ST</th>
                                            <th>HZ</th>
                                            <th>HR</th>
                                            <th>Tarif</th>
                                            <th>VAL</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row_detail as $detail)
                                            <tr>
                                                <td>{{ $detail['keterangan'] }}</td>
                                                <td> {{ $detail['start_stack'] }} </td>
                                                <td> {{ $detail['end_stack'] }} </td>
                                                <td>{{ $detail['jml_cont'] }}</td>
                                                <td>{{ $detail['size_'] }}</td>
                                                <td>{{ $detail['type_'] }}</td>
                                                <td>{{ $detail['status'] }}</td>
                                                <td>{{ $detail['hz'] }}</td>
                                                <td>{{ $detail['jml_hari'] }}</td>
                                                <td>{{ $detail['tarif'] }}</td>
                                                <td>IDR</td>
                                                <td>{{ $detail['biaya'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <hr class="w-100" style="border: 0.5px solid #000000">

                            {{-- Detail Harga --}}
                            <div class="d-flex justify-content-end">
                                <div class="col-lg-5 col-md-12">
                                    <div class="row align-items-start justify-content-end">
                                        <div class="col-lg-5 col-md-11 text-right">
                                            <span class="text-dark font-medium">Discount</span>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                        <div class="col-lg-4 col-md-12 text-right">
                                            <span class="text-dark">
                                                {{ $row_discount }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row align-items-center justify-content-end">
                                        <div class="col-lg-5 col-md-11 text-right">
                                            <span class="text-dark font-medium">Administrasi</span>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                        <div class="col-lg-4 col-md-12 text-right">
                                            <span class="text-dark">
                                                {{ $row_adm }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row align-items-center justify-content-end">
                                        <div class="col-lg-6 col-md-11 text-right">
                                            <span class="text-dark font-medium">Dasar Pengenaan Pajak</span>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                        <div class="col-lg-4 col-md-12 text-right">
                                            <span class="text-dark">
                                                {{ $row_tot }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row align-items-center justify-content-end">
                                        <div class="col-lg-5 col-md-11 text-right">
                                            <span class="text-dark font-medium">Jumlah PPN</span>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                        <div class="col-lg-4 col-md-12 text-right">
                                            <span class="text-dark">
                                                {{ $row_ppn['ppn'] }}
                                            </span>
                                        </div>
                                    </div>

                                    @if ($bea_materai > 0)
                                        <div class="row align-items-center justify-content-end">
                                            <div class="col-lg-5 col-md-11 text-right">
                                                <span class="text-dark font-medium">Bea Materai</span>
                                            </div>
                                            <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                            <div class="col-lg-4 col-md-12 text-right">
                                                <span class="text-dark font-medium">
                                                    {{ $row_materai }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row align-items-center justify-content-end">
                                        <div class="col-lg-5 col-md-11 text-right">
                                            <span class="text-dark font-medium">Jumlah Dibayar</span>
                                        </div>
                                        <div class="col-lg-1 col-md-1 d-none d-md-block d-lg-block">:</div>
                                        <div class="col-lg-4 col-md-12 text-right">
                                            <span class="text-dark">
                                                {{ $row_bayar }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="font-bold mt-5 pt-3 text-dark w-50 flex-wrap">
                                Nota sebagai Faktur Pajak berdasarkan Peraturan Dirjen Pajak
                                Nomor 13/PJ/2019 Tanggal 2 Juli 2019
                            </p>

                            <div class="d-flex justify-content-end mt-4 pt-3">
                                <div class="col-lg-4 col-md-12">
                                    <div class="row justify-align-center">
                                        <div class="col-lg-2 col-md-12"></div>
                                        <div class="col-lg-8 col-md-12 text-center">
                                            <span class="text-dark">{{ $pegawai['jabatan'] }}</span>
                                        </div>
                                        <div class="col-lg-2 col-md-12"></div>
                                    </div>

                                    <div class="row justify-align-center mt-5 pt-3">
                                        <div class="col-lg-2 col-md-12"></div>
                                        <div class="col-lg-8 col-md-12 text-center">
                                            <span class="text-dark">{{ $pegawai['nama_pegawai'] }}</span>
                                        </div>
                                        <div class="col-lg-2 col-md-12"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <a href="{{ route('uster.billing.nota_stripping.insert_proforma_relokmty', [base64_encode($no_req), 'koreksi=' . $koreksi]) }}"
                        class="btn btn-outline-info w-50 mb-5 row align-items-center">
                        <i class="mdi mdi-content-save mdi-18px"></i>
                        <span>
                            Save Nota
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
