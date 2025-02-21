<div class="d-flex">
    <div class="card-title m-b-40">
        <h5>Data Nota Receiving</h5>
    </div>
</div>

<div class="table-responsive px-3">
    <table class="display nowrap table data-table" cellspacing="0" width="100%" id="table-nota-list">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">No. Nota</th>
                <th class="text-center">No. Request</th>
                <th class="text-center">Kegiatan</th>
                <th class="text-center">Tgl. Kegiatan</th>
                <th class="text-center">Pemilik Barang</th>
                <th class="text-center">Total Tagihan</th>
                <th class="text-center">Status AR</th>
                <th class="text-center">Status Receipt</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($data as $items)
                <tr>
                    <td> {{ $i }} </td>
                    <td> {{ $items->no_nota }} </td>
                    <td> {{ $items->no_request }} </td>
                    <td> {{ $items->nama_modul }} </td>
                    <td> {{ $items->tgl_kegiatan }} </td>
                    <td> {{ $items->nm_emkl }} </td>
                    <td> Rp. {{ $items->total_tagihan }},- </td>
                    <td> {{ $items->simkeu_proses_ar }} </td>
                    <td> {{ $items->simkeu_proses_receipt }} </td>
                </tr>
            @php
                $i++;
            @endphp
            @endforeach
        </tbody>
    </table>
</div>
