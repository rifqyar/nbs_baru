@php


header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=LAP_Repo_Perkapal_tanggal-".$tanggal.".xls");
header("Pragma: no-cache");
header("Expires: 0");


@endphp

<div id="list">
    <center>
        <h2>KAPAL <?= $nm_kapal ?> (<?= $voyage ?>)</h2>
    </center>
    <table class="grid-table" border='1' cellpadding="1" cellspacing="1" width="100%">
        <tr style=" font-size:10pt">
            <th valign="top" class="grid-header" style="font-size:8pt">NO </th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO CONTAINER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">SIZE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TYPE</th>
            <th valign="top" class="grid-header" style="font-size:8pt">STATUS</th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO REQUEST</th>
            <th valign="top" class="grid-header" style="font-size:8pt">VIA</th>
            <th valign="top" class="grid-header" style="font-size:8pt">NOPOL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">TGL GATE OUT</th>
            <th valign="top" class="grid-header" style="font-size:8pt">USER</th>
            <th valign="top" class="grid-header" style="font-size:8pt">NO NOTA</th>
            <th valign="top" class="grid-header" style="font-size:8pt">LUNAS</th>
        </tr>

        @php $i = 0; @endphp
        @foreach ($row_q as $rows)
        @php $i++; @endphp
        <tr bgcolor="#f9f9f3" onMouseOver=this.style.backgroundColor="#BAD5FC" onMouseOut=this.style.backgroundColor="">
            <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt"><?= $i ?> </td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b><?= $rows->no_container ?></b></td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b><?= $rows->size_ ?></b></td>
            <td width="22%" align="center" valign="middle" class="grid-cell" style="font-family:Arial; font-size:11pt; color:#555555"><b><?= $rows->type_ ?></b></td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->status ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->no_request ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->via ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->nopol ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->tgl_in ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->username ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->no_nota ?></font>
            </td>
            <td width="15%" align="center" valign="middle" class="grid-cell" style="font-size:9pt;font-family:Arial;">
                <font color="#0066CC" style="font-size:10pt"><?= $rows->lunas ?></font>
            </td>
        </tr>
        @endforeach
    </table>
    <center>
        <h2>Total Jumlah Container = <?= $i ?> Box</h2>
    </center>
</div>