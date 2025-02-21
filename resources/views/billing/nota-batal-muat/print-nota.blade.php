@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table table-borderless">
            <tr>
                <td width="100"></td>
                <td align="center">
                    <h1>PREVIEW NOTA</h1>
                    <br />
                    <hr />
                    <div class="border border-primary p-3">
                        <table class="table table-borderless">
                            <tr height="25">
                                <td colspan="32" align="left"></td>
                            </tr>
                            <tr>
                                <td colspan="32" align="left"><b>PT. Multi Terminal Indonesia</b></td>
                            </tr>
                            <tr height="25">
                                <td colspan="32" align="left"></td>
                            </tr>
                            <tr>
                                <td colspan="26"></td>
                                <td colspan="5" align="right"><b></b></td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="4" align="right"><b>No. Nota</b></td>
                                <td colspan="1" align="right">:</td>
                                {{-- <td colspan="10" align="left"><b>{{ $rnota }}</b></td> --}}
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="4" align="right">No. Doc</td>
                                <td colspan="1" align="right">:</td>
                                <td colspan="10" align="left">{{ $no_req }}</td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="4" align="right">Tgl.Proses</td>
                                <td colspan="1" align="right">:</td>
                                <td colspan="10" align="left">{{ $tgl_nota }}</td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="4" align="right">
                                    @if ($st_gate != 2)
                                        No. Stuffing Baru
                                    @else
                                        No. Request Repo Baru
                                    @endif
                                </td>
                                <td colspan="1" align="right">:</td>
                                <td colspan="10" align="left">{{ $no_req_baru }}</td>
                            </tr>
                            <tr height="30">
                                <td colspan="38" align="left"></td>
                            </tr>
                            <tr>
                                <td colspan="38" align="center"><b>PERHITUNGAN PELAYANAN JASA</b></td>
                            </tr>
                            <tr>
                                <td colspan="38" align="center"><b>BATAL MUAT</b></td>
                            </tr>
                            <tr height="30">
                                <td colspan="38" align="left">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">Nama Perusahaan </td>
                                <td colspan="11" align="left">: {{ $row_nota->emkl }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6">N.P.W.P</td>
                                <td colspan="11" align="left">: {{ $row_nota->npwp }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="6">Alamat</td>
                                <td colspan="11" align="left">: {{ $row_nota->alamat }}</td>
                                <td></td>
                            </tr>
                            <tr height="30">
                                <td colspan="38" align="left">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="5" width="250"><b>KETERANGAN</b></td>
                                <td align="center" width="80"><b>TGL AWAL</b></td>
                                <td align="center" width="80"><b>TGL AKHIR</b></td>
                                <td colspan="2" align="center" width="20"><b>BOX</b></td>
                                <td colspan="2" align="center" width="20"><b>SZ</b></td>
                                <td colspan="2" align="center" width="30"><b>TY</b></td>
                                <td colspan="2" align="center" width="30"><b>ST</b></td>
                                <td colspan="2" align="center" width="20"><b>HZ</b></td>
                                <td colspan="2" align="center"><b>HR</b></td>
                                <td colspan="4" align="center"><b>TARIF</b></td>
                                <td colspan="2" align="center"><b>VAL</b></td>
                                <td colspan="5" align="right"><b>JUMLAH</b></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="38">
                                    <hr>
                                </td>
                            </tr>

                            @foreach ($row_detail as $row)
                                <tr>
                                    <td colspan="3"></td>
                                    <td colspan="5">{{ $row->keterangan }}</td>
                                    <td align="center">{{ $row->start_stack }}</td>
                                    <td align="center">{{ $row->end_stack }}</td>
                                    <td colspan="2" align="center">{{ $row->jml_cont }}</td>
                                    <td colspan="2" align="center">{{ $row->size_ ?? '' }}</td>
                                    <td colspan="2" align="center">{{ $row->type_ ?? '' }}</td>
                                    <td colspan="2" align="center">{{ $row->status  ?? ''}}</td>
                                    <td colspan="2" align="center">{{ $row->hz  ?? ''}}</td>
                                    <td colspan="2" align="center">{{ $row->jml_hari }}</td>
                                    <td colspan="4" align="center">{{ $row->tarif }}</td>
                                    <td colspan="2" align="center">IDR</td>
                                    <td colspan="5" align="right">{{ $row->biaya }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach

                            <tr>
                                <td colspan="38">
                                    <hr>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="38"></td>
                            </tr>
                            <tr>
                                <td colspan="38"></td>
                            </tr>
                            <tr>
                                <td colspan="38"></td>
                            </tr>

                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="right">Discount :</td>
                                <td colspan="7" align="right">{{ $row_discount->discount }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="right">Administrasi :</td>
                                <td colspan="7" align="right">{{ $row_adm->adm }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="right">Dasar Pengenaan Pajak :</td>
                                <td colspan="7" align="right">{{ $row_tot->total_all }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="right">Jumlah PPN :</td>
                                <td colspan="7" align="right">{{ $row_ppn->ppn }}</td>
                                <td colspan="2"></td>
                            </tr>
                            @if ($bea_materai > 0)
                                <tr>
                                    <td colspan="18"></td>
                                    <td colspan="10" align="right">Bea Materai :</td>
                                    <td colspan="7" align="right">{{ $row_materai->bea_materai }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="18"></td>
                                    <td colspan="10" align="right"></td>
                                    <td colspan="7" align="right"></td>
                                    <td colspan="2"></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="right">Jumlah Dibayar :</td>
                                <td colspan="7" align="right">{{ $row_bayar->total_bayar }}</td>
                                <td colspan="2"></td>
                            </tr>
                            <tr height="50">
                                <td colspan="38"></td>
                            </tr>
                            <tr>
                                <td colspan="38"><b>Nota sebagai Faktur Pajak berdasarkan Peraturan Dirjen Pajak <br>
                                        Nomor 13/PJ/2019 Tanggal 2 Juli 2019</b>
                                </td>
                            </tr>
                            <tr height="25">
                                <td colspan="38"></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="center">{{ $nama_peg->jabatan }} </td>
                                <td colspan="7" align="right"></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr height="30">
                                <td colspan="38"></td>
                            </tr>
                            <tr>
                                <td colspan="18"></td>
                                <td colspan="10" align="center">{{ $nama_peg->nama_pegawai }}</td>
                                <td colspan="7" align="right"></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr height="30">
                                <td colspan="38"></td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="100"></td>
            </tr>
        </table>
    </div>

    <div class="container text-center my-5">
        <a onclick="saveNota()" class="btn btn-primary">
            <i class="fas fa-save"></i>
            <font color='#0000FF'>
                <font size='5'> &nbsp SAVE NOTA</font>
            </font>
        </a>
    </div>

    <script type="text/javascript">
        function saveNota() {
            var url = "{{ route('uster.billing.nota_batalmuat.insert_proforma', ['no_req' => $no_req]) }}";
            var koreksi = "{{ request('koreksi') }}";
            window.open(url + "&koreksi=" + koreksi);
        }
    </script>
    
@endsection
