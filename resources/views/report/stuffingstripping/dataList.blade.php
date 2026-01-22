<div class="table-responsive px-3">
    <table class="display no-wrap table data-table" width="100%" id="data-list">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">No. Request</th>
                <th class="text-center">Tgl. Request</th>
                <th class="text-center">No. Container</th>
                <th class="text-center">PIN</th>
                <th class="text-center">Size / Type</th>
                <th class="text-center">Kegiatan</th>
                <th class="text-center">Lokasi TPK</th>
                <th class="text-center">Lokasi Uster</th>
                <th class="text-center">Tgl. Approval</th>
                <th class="text-center">Active To</th>
                <th class="text-center">Tgl. Realisasi</th>
                <th class="text-center">Pemilik Barang</th>
                <th class="text-center">Komoditi</th>
                <th class="text-center">Kapal / VOY</th>
            </tr>
        </thead>
        <tbody id="nota-body">
            {{-- @forelse ($data as $key => $dt)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $dt->no_request }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->tgl_request)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td>{{ $dt->no_container }}</td>
                    <td>{{ $dt->pin_number }}</td>
                    <td>{{ $dt->size_ }} / {{$dt->type_}} </td>
                    <td>{{ $dt->kegiatan }}</td>
                    <td>{{ $dt->lokasi_tpk }}</td>
                    <td>{{ $dt->loc_uster }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ $dt->tgl_approve != null ? \Carbon\Carbon::parse($dt->tgl_approve)->translatedFormat('Y-m-d') : '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-warning rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->active_to)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-primary rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ $dt->tgl_realisasi != null ? \Carbon\Carbon::parse($dt->tgl_realisasi)->translatedFormat('Y-m-d') : '-' }}
                        </span>
                    </td>
                    <td>{{ $dt->nm_pbm }}</td>
                    <td>{{ $dt->commodity }}</td>
                    <td>{{ $dt->nm_kapal }} / {{$dt->voyage}} </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">
                        <h6 class="text-center text-danger">Tidak Ada Data</h6>
                    </td>
                </tr>
            @endforelse --}}
        </tbody>
    </table>
</div>

<script>
    $(function() {
        const tooltipTriggerList = document.querySelectorAll('[data-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
            tooltipTriggerEl))
    })
</script>
