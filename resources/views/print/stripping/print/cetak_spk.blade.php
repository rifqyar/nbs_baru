<html>

<body>
    <style>
        body {
            margin: 0px;
            padding: 0px;
            width: 100%;
            height: 100%;
        }

        .txc {
            height: 2px;
            padding: 0px;
            margin: 0px;
            line-height: 9px
        }
    </style>
    <div style="height:5px; width:767px; height:470px; border:0px solid  #FFF">
        @foreach ($row_list as $row)
        <table align="center" border="0" width="600" height="150">
            <tr>
                <td><img src='{{asset('assets/images/MTI-Logo.jpg')}}' width="100px"></td>
                <td width="500"></td>
            </tr>
            <tr>
                <td colspan="3" align="center">
                    <font size="2" face="verdana"><b>SURAT PERINTAH KERJA KEGIATAN STRIPPING</b></font><br>
                </td>
            </tr>
        </table>
        <br>
        <table align="center" border="0" width="650" height="100">
            <tr>
                <td width="200">
                    <font size="2" face="verdana">NO Request</font>
                </td>
                <td>:</td>
                <td>
                    <font size="2" face="verdana">{{$row->no_request}}</font>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <font size="2" face="verdana">
                        Dengan ini diberikan Surat Perintah Kerja untuk melakukan kegiatan STRIPPING dengan rincian
                        kegiatan sebagai berikut :
                    </font>
                </td>
            </tr>
        </table>
        <br>
        <table border="1" style="solid" align="center">
            <tr>
                <th width="30">No</th>
                <th width="120">No Container</th>
                <th width="120">Size/Type/Status</th>
                <th width="120">Consignee</th>
                <th width="120">Vessel/Voy</th>
                <th width="150">Tgl Berlaku </th>
            </tr>
            <tr align="center">
                <td>1</td>
                <td>{{$row->no_container}}</td>
                <td>{{$row->size_ ?? ''}}/{{$row->type_}}/{{$row->status ??''}}</td>
                <td>{{$row->emkl}}</td>
                <td>{{$row->o_vessel ?? ''}}/{{$row->o_voyin ?? ''}}</td>
                <td>
                    @if ($row->status_req ?? '' == 'PERP')
                        <?php
                        $no_co = $row->no_container;
                        $no_re = $row->no_request;

                        $tgl_awal = "SELECT ((TO_DATE(a.TGL_APPROVE)+3)+b.PERP_KE-1) TGL_AWAL FROM container_stripping a, request_stripping b where a.no_request = b.no_request
                                    and a.no_container = '$no_co' AND a.no_request = '$no_re'";

                        $perp_ = Illuminate\Support\Facades\DB::connection('uster')->selectOne($tgl_awal);
                        echo $perp_->tgl_awal . "-" . $perp_->tgl_awal;
                        ?>
                    @else
                        {{$row->tgl_awal}} - {{$row->tgl_akhir}}
                    @endif
                </td>
            </tr>
        </table>
        <br height="50">
        <table align="center" border="0" width="700" height="100">
            <tr>
                <td width="200" align=""></td>
                <td width="150"></td>
                <td width="350" align="center">
                    <font size="2" face="verdana">Pontianak, {{$row->sysdate_}}</font>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td align="center">
                    a.n {{$rowspv->jabatan ?? ''}}<br>
                    <font size="2" face="verdana"></font>
                </td>
            </tr>
            <tr height="50">
            </tr>
            <tr>
                <td align="center"></td>
                <td></td>
                <td align="center">{{$rowspv->nama_pegawai ?? ''}}</td>
            </tr>
        </table>
        <hr />
        @endforeach
    </div>
</body>

</html>
