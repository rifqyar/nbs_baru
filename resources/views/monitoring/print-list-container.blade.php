<center>
    <h2> Laporan Container By Status untuk kegiatan {{ $kegiatan_ }}<br /> Periode {{ $tgl_awal }} s/d
        {{ $tgl_akhir }}</h2>
</center>
@if ($kegiatan == 'receiving_tpk' or $kegiatan == 'receiving_luar')
    <table class="grid-table" border="1" cellpadding="1" cellspacing="1" width="100%">
        <tr>
            <th valign="top" class="grid-header" style="font-size:8pt">No</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Container</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Size</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Type</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Status</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">EMKL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Peralihan</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl GATE IN</th>
            <th valign="top" class="grid-header" style="font-size:8pt">NoPoL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Placement</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Lokasi</th>
        </tr>
        @php $no = 1; @endphp
        @foreach ($row_list as $row)
            <tr>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $no }}</td>
                <td width="5%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_container ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->size ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->type ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->status ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_request ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_request ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->emkl ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->peralihan ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_in ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->nopol ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_placement ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">
                    {{ $row->nama_blok ?? '' }}-{{ $row->slot ?? '' }}-{{ $row->row ?? '' }}-{{ $row->tier ?? '' }}</td>
            </tr>
            @php $no++; @endphp
        @endforeach
    </table>
@elseif ($kegiatan == 'stripping_tpk')
    <table class="grid-table" border='0' cellpadding="1" cellspacing="1" width="100%">
        <tr>
            <th valign="top" class="grid-header" style="font-size:8pt">No</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Container</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Size</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Type</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Status</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">EMKL</th>
            @if ($status != 'tgl_app')
                <th valign="top" class="grid-header" style="font-size:8pt">TGL IN</th>
                <!-- <th valign="top" class="grid-header"  style="font-size:8pt">NoPoL</th> -->
                <th valign="top" class="grid-header" style="font-size:8pt">Tgl Placement</th>
                <th valign="top" class="grid-header" style="font-size:8pt">Lokasi</th>
            @endif
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Approve</th>
            @if ($status != 'tgl_app')
                <th valign="top" class="grid-header" style="font-size:8pt">Tgl Realisasi</th>
                <th valign="top" class="grid-header" style="font-size:8pt">Tgl Relokasi</th>
                <th valign="top" class="grid-header" style="font-size:8pt">Lokasi</th>
            @endif

        </tr>
        @php $no = 1; @endphp
        @foreach ($row_list as $row)
            <tr>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $no }}</td>
                <td width="5%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_container ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->size ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->type ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->status ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_request ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_request ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->emkl ?? '' }}</td>
                @if ($status != 'tgl_app')
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_in ?? '' }}</td>
                    <!-- <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt">{{ $row->nopol }}</td> -->
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_placement ?? '' }}</td>
                    <td width="8%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">
                        {{ $row->nama_blok ?? '' }}-{{ $row->slot ?? '' }}-{{ $row->row ?? '' }}-{{ $row->tier ?? '' }}</td>
                @endif
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_approve ?? '' }}</td>
                @if ($status != 'tgl_app')
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_realisasi ?? '' }}</td>
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_relokasi ?? '' }}</td>
                    <td width="8%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">
                        {{ $row->nama_blok_mty ?? '' }}-{{ $row->slot_mty ?? '' }}-{{ $row->row_mty ?? '' }}-{{ $row->tier_mty ?? '' }}
                    </td>
                @endif
            </tr>
            @php $no++; @endphp
        @endforeach
    </table>
@elseif ($kegiatan == 'stuffing_depo' || $kegiatan == 'stuffing_tpk')
    <table class="grid-table" border='0' cellpadding="1" cellspacing="1" width="100%">
        <tr>
            <th valign="top" class="grid-header" style="font-size:8pt">No</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Container</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Size</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Type</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Status</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">EMKL</th>
            @if ($kegiatan == 'stuffing_tpk')
                <th valign="top" class="grid-header" style="font-size:8pt">Tgl In</th>
                <th valign="top" class="grid-header" style="font-size:8pt">Tgl Placement</th>
            @endif
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Realisasi</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Request Delivery</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Request Delivery</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Nm Kapal</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Gate Out</th>
        </tr>
        @php $no = 1; @endphp
        @foreach ($row_list as $row)
            <tr>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $no }}</td>
                <td width="5%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_container ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->size ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->type ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->status ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_request ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_request ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->emkl ?? '' }}</td>
                @if ($kegiatan == 'stuffing_tpk')
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_in ?? '' }}</td>
                    <td width="4%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_placement ?? '' }}</td>
                    <td width="8%" align="center" valign="middle" class="grid-cell"
                        style="color:#000; font-family:Arial; font-size:9pt">
                        {{ $row->nama_blok ?? '' }}-{{ $row->slot ?? '' }}-{{ $row->row ?? '' }}-{{ $row->tier ?? '' }}</td>
                @endif
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_realisasi ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_req_delivery ?? '' }}</td>
                <!--         <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_req_delivery }}</td>
         <td width="4%" align="center" valign="middle" class="grid-cell" style="color:#000; font-family:Arial; font-size:9pt">{{ $row->nm_kapal }}</td> -->
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_gate ?? '' }}</td>
            </tr>
            @php $no++; @endphp
        @endforeach
    </table>
@elseif ($kegiatan == 'delivery_tpk_mty' || $kegiatan == 'delivery_luar')
    <table class="grid-table" border='0' cellpadding="1" cellspacing="1" width="100%">
        <tr>
            <th valign="top" class="grid-header" style="font-size:8pt">No</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Container</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Size</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Type</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Status</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Komoditi</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Berat</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">No Request</th>
            <th valign="top" class="grid-header" style="font-size:8pt">EMKL</th>
            <th valign="top" class="grid-header" style="font-size:8pt">Active</th>
            <?php if ($kegiatan == 'delivery_tpk_mty') { ?>
            <th valign="top" class="grid-header" style="font-size:8pt">Nm Kapal</th>
            <?php } ?>
            <th valign="top" class="grid-header" style="font-size:8pt">Tgl Gate Out</th>
        </tr>
        @php $no = 1; @endphp
        @foreach ($row_list as $row)
            <tr>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $no }}</td>
                <td width="5%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_container ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->size ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->type ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->status ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->commodity ?? '' }}</td>
                <td width="2%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->berat ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_request ?? '' }}</td>
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->no_request ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->nm_pbm ?? '' }}</td>
                <td width="8%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_delivery ?? '' }}</td>
                @if ($kegiatan == 'delivery_tpk_mty')
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->nm_kapal ?? '' }}
                    ({{ $row->voyage_in ?? '' }})
                </td>
                @endif
                <td width="4%" align="center" valign="middle" class="grid-cell"
                    style="color:#000; font-family:Arial; font-size:9pt">{{ $row->tgl_gate ?? '' }}</td>
            </tr>
            @php $no++; @endphp
        @endforeach
    </table>
@endif
