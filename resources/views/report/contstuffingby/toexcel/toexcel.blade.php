@php


header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=LAP-" . $jenis . "-" . $tanggal . ".xls");
header("Pragma: no-cache");
header("Expires: 0");


@endphp

<div id="list">
    <table class="grid-table" border='0' cellpadding="1" cellspacing="1" width="100%">
        <tr style=" font-size:10pt">
            <th valign="top" class="grid-header" style="font-size:8pt">No </th>
            <th valign="top" class="grid-header" style="font-size:8pt">No. Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No. Container</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Kegiatan</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Approval</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Realisasi</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Pemilik Barang</th>
        </tr>
        @php
        $i = 0;
        $before = "";
        @endphp
        @foreach ($row_list as $rows)
        <tr bgcolor="#f9f9f3" onMouseOver=this.style.backgroundColor="#BAD5FC" onMouseOut=this.style.backgroundColor="">
            @if ($rows->no_request != $before)
            @php $i++; @endphp
            <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt">{{ $i }} </td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b>{{ $rows->no_request }}</b></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $rows->no_container }}</td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->kegiatan }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_approve }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_realisasi }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->nm_pbm }}</font>
            </td>
            @else
            <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt"></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt"></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $rows->no_container }}</td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->kegiatan }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_approve }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_realisasi }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->nm_pbm }}</font>
            </td>
            @endif
            @php
            $before = $rows->no_request;
            @endphp
        </tr>
        @endforeach
    </table>
</div>