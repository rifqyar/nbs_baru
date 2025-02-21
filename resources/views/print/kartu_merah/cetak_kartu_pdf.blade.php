<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Cetak Kartu Merah</title>
    <style>
        /*body { margin:0px; padding-top:15px; width:100%; height:100%;}*/
        /*body { margin:0px; padding-top:0px; width:100%; height:100%;}*/
        body {
            margin: 0px;
            padding-top: 0px;
            width: 100%;
            height: 100%;
            font-family: monospace
        }

        .txc {
            height: 0px;
            padding: 0px;
            margin: 0px;
            line-height: 5px
        }

        .style1 {
            height: 0px;
            padding: 0px;
            margin: 0px;
            line-height: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @foreach ($nota as $dt)
        <table border="0" width="767px" height="340" cellpadding="3" cellspacing="3">
            <tr>
                <th width="13%" height="12" class="txc" scope="col"></th>
                <th class="txc" width="5%" scope="col"></th>
                <th class="txc" width="5%" scope="col"></th>
                <th class="txc" width="5%" scope="col"></th>
                <th class="txc" width="5%" scope="col"></th>
                <th class="txc" width="8%" scope="col"></th>
                <th class="txc" width="15%" scope="col"></th>
                <th class="txc" width="13%" scope="col"></th>
                <th class="txc" colspan="2" align="left" scope="col"
                    style="font-size:11px; font-family:Arial">
                    &nbsp;&nbsp;{{ $dt->no_request }}</th>
                <th class="txc" width="1%" scope="col"></th>
            </tr>
            <tr>
                <th height="12" class="txc" scope="row"></th>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc"></td>
                <td colspan="2" class="style1" style="font-size:11px; font-family:Arial">
                    &nbsp;&nbsp;{{ $dt->tgl_nota }}
                </td>
                <td class="txc"></td>
            </tr>
            <tr>
                <th height="21" class="txc" scope="row"></th>
                <td class="txc"></td>
                <td class="txc"></td>
                <td class="txc" colspan="5">
                    <div style="padding-right:150px; font-size:13px"></div>
                </td>
                <td colspan="2" class="style1" style="font-size:11px; font-family:Arial"></td>
                <td class="txc"></td>
            </tr>
            <tr>
                <th height="20" colspan="8" valign="top" align="left" scope="row"
                    style="font-size:20px; padding-left:15px; margin-top: 30px">VIA {{ $dt->via }} </th>
                <td width="10%"></td>
                <td width="8%"></td>
                <td></td>
            </tr>
            <tr>
                <th height="10" scope="row"></th>
                <td colspan="6" style="padding-left: 25px; padding-top: 15px"><b style="font-size:26px;">{{ $dt->no_container }}</b>
                </td>
                <td></td>
                <td colspan="2" align="left" style="font-size:12px"><strong>&nbsp; {{ $dt->nm_kapal }} &nbsp;
                        &nbsp;
                        &nbsp; &nbsp; {{ $dt->voyage_out ?? '' }} </strong></td>
                <td align="left">&nbsp;</td>
                <td></td>
            </tr>
            <tr>
                <th height="0" scope="row"></th>
                <td align="right"><strong>{{ $dt->size_ }}</strong></td>
                <td colspan="3" align="center"><strong>{{ $dt->type_ }}</strong></td>
                <td align="left"><strong>{{ $dt->status }}</strong></td>
                <td></td>
                <td align="center">{{ $dt->pelabuhan_tujuan ?? '' }}</td>
                <td colspan="2"></td>
                <td></td>
            </tr>
            <tr>
                <th height="20" scope="row"></th>
                <td colspan="5" align="left" style="font-size:10px">
                    @if ($dt->ex_kapal != null)
                        Ex Kapal : {{ $dt->ex_kapal }}
                    @endif
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th height="25" scope="row"></th>
                <td valign="bottom" align="right"><strong>&nbsp;&nbsp;{{ $dt->area_ }}</strong></td>
                <td valign="bottom" align="right"><strong></strong></td>
                <td align="center"><strong></strong></td>
                <td align="center"><strong></strong></td>
                <td align="center"><strong> </strong></td>
                <td></td>
                <td colspan="3"><span style="font-size:10px"><strong>{{ $dt->emkl ?? '' }}</strong></span>
                </td>
                <td></td>
            </tr>
            <tr>
                <th height="26" scope="row"></th>
                <td colspan="4">REC DARI : {{ $dt->receiving_dari }}</td>
                <td></td>
                <td></td>
                <td colspan="3" style="font-size:10px">&nbsp;</td>
                <td></td>
            </tr>
            <tr>
                <th height="26" scope="row"></th>
                <td valign="bottom" colspan="5">
                    <strong> {{ $dt->nama_yard }} </strong>
                </td>
                <td></td>
                <td style="font-size:14px"></td>
                <td style="font-size:14px"></td>
                <td align="left" valign="top" style="font-size:14px"><strong>
                    </strong></td>
                <td>&nbsp;</td>
            </tr>
        </table>
    @endforeach
</body>

</html>
