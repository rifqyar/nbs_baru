@php


header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=LAP-" . $jenis . "-perkapal-" . $tanggal . ".xls");
header("Pragma: no-cache");
header("Expires: 0");


@endphp

<div id="list">
    <center>
        <h2>KAPAL {{ $nm_kapal }} ({{ $voyage }})</h2>
    </center>
    <table class="grid-table" border='1' cellpadding="1" cellspacing="1" width="100%">
        <tr style=" font-size:10pt">
            <th valign="top" class="grid-header" style="font-size:8pt">NO </th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO CONTAINER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">SIZE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO REQUEST</th>
            @if ($kegiatan == "stuffing")
            <th valign="top" class="grid-header" style="font-size:8pt">NO REQUEST DELIVERY</th>
            @endif
            <th valign="top" class="grid-header" style="font-size:8pt">TGL REQUEST</th>
            <th valign="top" class="grid-header" style="font-size:8pt">KOMODITI</th>
            <th valign="top" class="grid-header" style="font-size:8pt">USER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">CONSIGNEE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL AWAL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL AKHIR</th>
            <th valign="top" class="grid-header" style="font-size:8pt">LUNAS</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL_GATE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL REALISASI</th>
            <th valign="top" class="grid-header" style="font-size:8pt">USER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">VIA</th>
            <th valign="top" class="grid-header" style="font-size:8pt">ALAT</th>
        </tr>
        @php $i = 0; @endphp
        @foreach ($row_q as $rows)
        @php $i++;    @endphp
        <tr bgcolor="#f9f9f3" onMouseOver=this.style.backgroundColor="#BAD5FC" onMouseOut=this.style.backgroundColor="">
            <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt"><?= $i ?> </td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b>{{ $rows->no_container }}</b></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $rows->size_ }}</td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->no_request }}</font>
            </td>
            @if ($kegiatan == "stuffing")
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->no_request_delivery }}</font>
            </td>
            @endif
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_request }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->commodity }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->nm_request }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->nm_pbm }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_awal }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_akhir }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->lunas }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_gate }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->tgl_realisasi }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->nm_realisasi }}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->status ?? '-'}}</font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt">{{ $rows->pemakaian_alat }}</font>
            </td>

        </tr>
        @endforeach
    </table>
    <center>
        <h2>Total Jumlah Container = <?= $i ?> Box</h2>
    </center>
</div>