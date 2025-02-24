@php


header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=Container-".$kegiatan."-".$no_request.".xls");
header("Pragma: no-cache");
header("Expires: 0");


@endphp

<div id="list">
    <center> No.Request : <?= $no_request ?> <br /> <?= $vessel ?> (<?= $voy ?>)</center>
    <table class="grid-table" border='1' cellpadding="1" cellspacing="1" width="100%">
        <tr style=" font-size:10pt">
            <th valign="top" class="grid-header" style="font-size:8pt">NO </th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO CONTAINER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL AWAL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL AKHIR</th>
            <th valign="top" class="grid-header" style="font-size:8pt">SIZE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TYPE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">STATUS</th>
            <th valign="top" class="grid-header" style="font-size:8pt">HZ</th>
            <th valign="top" class="grid-header" style="font-size:8pt">COMMODITY</th>
            <th valign="top" class="grid-header" style="font-size:8pt">GROSS</th>
        </tr>
        @php $i = 0; @endphp
        @foreach ($row_q as $rows)
        @php $i++; @endphp
        <tr bgcolor="#f9f9f3" onMouseOver=this.style.backgroundColor="#BAD5FC" onMouseOut=this.style.backgroundColor="">
            <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt">{{ $i }} </td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b>{{ $rows->no_container }}</b></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_awal }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_akhir }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $rows->size_ }}</td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->type_ }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->status }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->hz }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->komoditi }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->berat }}</font>
            </td>
        </tr>
        @endforeach
    </table>
</div>