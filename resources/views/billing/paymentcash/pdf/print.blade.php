<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content=width, initial-scale=1.0">
    <title>PDF Template</title>
    @if($data_mtr_biaya == 0)
    <style>
        .pad {
            margin-left: 280px;
        }
    </style>
    @endif
    <style>
        .container {
            /* border: 1px solid; */
            display: flex;
            justify-content: flex-end
        }

        .item {
            /* border: 1px solid;             */
            right: 0;
            float: right;
        }

        .imgs {
            width: 200px;
            /* height: 120px; */
            margin-left: -53px;
        }
    </style>
</head>

<body style="margin-top:-100px;">
    <table border=0>
        <tr>
            <td width=100><img src="{{$img}}" alt="" class="imgs"> </td>
            <td>
                <b>PT MULTI TERMINAL INDONESIA</b>
                <p style="font-size:9pt">Alamat : Jl. Pulau Payung No. 1 Tanjung Priok, Jakarta Utara<br />
                    NPWP <span style="padding-left: 3px;">:</span> 02.106.620.4-093.000</p>
            </td>
        </tr>

    </table>


    <table style="float: right; font-size: 9pt; margin-top: -50px;">
        <tr>
            <td>No. Nota</td>
            <td>:</td>
            <td><b>{{session()->get('NOTA_MTI')}}</b></td>
        </tr>
        <tr>
            <td>No. Faktur SAP</td>
            <td>:</td>
            <td><b>{{session()->get('FAKTUR_MTI')}}</b></td>
        </tr>
        <tr>
            <td>No. Request</td>
            <td>:</td>
            <td><b>{{$nota->no_request}}</b></td>
        </tr>
        <tr>
            <td>Date Of Request</td>
            <td>:</td>
            <td><b>{{$dt}}</b></td>
        </tr>
    </table>
    <!-- Add more HTML content here -->

    <div style="margin-top:30px">
        <center><b>{{ $nota_kd }}</b></center>

        <br />


        <div style="line-height: normal; font-size:10pt;">
            {{session()->get("emkl")}}<br />
            {{session()->get("npwp")}}<br />
            {{session()->get("alamat")}}
        </div>
    </div>

    <br />

    <table border='0' style="font-size:9pt; width: 100%;">
        <tr height="20">
            <td align="left"><b></b></td>
            <td></td>
            <td colspan="4"><b></b></td>
            <td colspan="8"></td>
        </tr>
        <tr>
            <td align="left">No. DO</td>
            <td>:</td>
            <td colspan="4">{{$no_do ?? '-'}}</td>
            <td colspan="8"></td>
        </tr>
        <tr>
            <td align="left">No. BL </td>
            <td>:</td>
            <td colspan="4">{{$no_bl ?? '-'}}</td>
            <td colspan="8"></td>
        </tr>
        <tr>
            <td align="left">No. Document </td>
            <td>:</td>
            <td colspan="4">-</td>
            <td align="left">Date Of Stacking </td>
            <td>:</td>
            <td colspan="6">-</td>

        </tr>
        <tr>
            <td align="left">Date Of Document </td>
            <td>:</td>
            <td colspan="4">-</td>
            <td align="left">Date Of Delivery </td>
            <td>:</td>
            <td colspan="6">-</td>
        </tr>
        @if(isset($teks_baru) && isset($no_req_baru) && $teks_baru != '' && $no_req_baru != '')
        <tr>
            <td align="left"></td>
            <td></td>
            <td colspan="4"></td>
            <td align="left">{{$teks_baru}} </td>
            <td>:</td>
            <td>{{$no_req_baru}}</td>
        </tr>
        @endif
        <tr>
            <td align="left"></td>
            <td></td>
            <td colspan="4"></td>
            <td colspan="8"></td>
        </tr>

        {{$listcont ?? ''}}

        <tr height="20">
            <td colspan="14"></td>
        </tr>
        <tr height="20">
            <td colspan="14"></td>
        </tr>
        <tr height="20">
            <td colspan="14"></td>
        </tr>
        <tr>
            <th colspan="3" align="left"><b>KETERANGAN</b></th>
            <th align="center"><b>TGL AWAL</b></th>
            <th align="center"><b>TGL AKHIR</b></th>
            <th align="center"><b>BOX</b></th>
            <th align="center"><b>SIZE</b></th>
            <th align="center"><b>TYPE</b></th>
            <th align="center"><b>STATUS</b></th>
            <th align="center"><b>HZ</b></th>
            <th align="center"><b>HR</b></th>
            <th align="center"><b>TARIF</b></th>
            <th align="center"><b>VAL</b></th>
            <th align="right"><b>JUMLAH</b></th>
        </tr>
        <tr>
            <td colspan="14">
                <hr>
            </td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        @foreach($rowdetail as $rd)
        <tr>
            <td colspan="3">{{$rd->keterangan}}</td>
            <th align="center">{{$rd->start_stack ?? ''}}</th>
            <th align="center">{{$rd->end_stack ?? ''}}</th>
            <td align="center">{{$rd->jml_cont ?? ''}}</td>
            <td align="center">{{$rd->size_}}</td>
            <td align="center">{{$rd->type_}}</td>
            <td align="center">{{$rd->status}}</td>
            <td align="center">{{$rd->hz}}</td>
            <td align="center">{{$rd->jml_hari}}</td>
            <td align="right">{{$rd->tarif}}</td>
            <td align="center">IDR</td>
            <td align="right">{{$rd->biaya}}</td>
        </tr>

        @endforeach
        <tr>
            <td colspan="14">
                <hr>
            </td>
        </tr>
    </table>

    <table border='0' style="font-size: 10pt;" class="pad">
        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Discount :</td>
            <td width="100" colspan="2" align="right"> - </td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Administrasi :</td>
            <td width="100" colspan="2" align="right">{{$nota->adm_nota}}</td>
            <td></td>
        </tr>

        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Dasar Pengenaan Pajak :</td>
            <td width="100" colspan="2" align="right">{{$nota->tagihan}}</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Jumlah PPN :</td>
            <td width="100" colspan="2" align="right">{{$nota->ppn}}</td>
            <td></td>
        </tr>

        @if($data_mtr_biaya > 0)
        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Bea Materai :</td>
            <td width="100" colspan="2" align="right">{{ $bea_materai }}</td>
            <td></td>
        </tr>
        @endif

        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right">Jumlah Dibayar :</td>
            <td width="100" colspan="2" align="right">{{$nota->total_tagihan}}</td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8"></td>
            <td width="225" colspan="4" align="right"></td>
            <td width="100" colspan="2"></td>
            <td></td>
        </tr>
        @if($data_mtr_biaya > 0)
        <tr>
            <td colspan="6" align="left">Bea Materai Lunas Dengan Sistem Nomor Ijin : {{ $no_mat }} </td>
            <td width="300" colspan="4"></td>
            <td width="130" colspan="2" align="center" border="1">Termasuk Bea Materai Rp. {{ $bea_materai }} </td>
            <td></td>
        </tr>
        @endif
    </table>
</body>

</html>
