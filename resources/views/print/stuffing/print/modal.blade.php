<div id="list">
    <table width="100%" border="1">
        <tr>
            <td> NO </td>
            <td> NO CONTAINER</td>
            <td> AKSI</td>
        </tr>
        @foreach($rw_cont as $rw)
        <tr>
            <td> {{ $loop->index + 1 }} </td>
            <td> {{ $rw->no_container }} </td>
            <td> <a href="{{route('uster.print.stuffing.Cetak')}}?no_req={{ $no_req }}&no_cont={{ $rw->no_container }}" target="_blank"><i class="fas fa-print"></i> Cetak</a></td>
        </tr>
        @endforeach
    </table>
</div>