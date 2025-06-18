<table border=1 width="100%" align="center">
    <tr align="center">
        <th class="grid-header" style="font-size:8pt">No.</th>
        <th class="grid-header" style="font-size:8pt">BLOCK STUFFING INVENTORY</th>
        <th class="grid-header" style="font-size:8pt">{{$tanggal[0]->h}}</th>
        <th class="grid-header" style="font-size:8pt">{{$tanggal[0]->h_1}}</th>
        <th class="grid-header" style="font-size:8pt">{{$tanggal[0]->h_2}}</th>
        <th class="grid-header" style="font-size:8pt">{{$tanggal[0]->h_3}}</th>
    </tr>
    <fill src='row' var='row'>
        @foreach ($row as $item)
            <tr>
                <td align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->no }}</td>
                <td align="left" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->kegiatan }}</td>
                <td align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->h }}</td>
                <td align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->h1 }}</td>
                <td align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->h2 }}</td>
                <td align="center" valign="middle" class="grid-cell" style="font-size:9pt">{{ $item->h3 }}</td>
            </tr>
        @endforeach
    </fill>
    
</table>
