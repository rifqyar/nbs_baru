<div class="table-responsive px-3">
    <table class="display no-wrap table data-table" width="100%" id="data-list">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">No. Container</th>
                <th class="text-center">No. Request</th>
                <th class="text-center">Size</th>
                <th class="text-center">Type</th>
                <th class="text-center">Status</th>
                <th class="text-center">Kegiatan</th>
                <th class="text-center">Tgl. Approval</th>
                <th class="text-center">Tgl. Realisasi</th>
                <th class="text-center">Eksekutor</th>
                <th class="text-center">Tgl. Placement</th>
                <th class="text-center">Pemilik Barang</th>
                <th class="text-center">Kapal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $key => $dt)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $dt->no_container }}</td>
                    <td>{{ $dt->no_request }}</td>
                    <td>{{ $dt->size_ }} </td>
                    <td>{{ $dt->type_ }} </td>
                    <td>{{ $dt->status_cont }} </td>
                    <td>{{ $dt->kegiatan }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->tgl_approve)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->tgl_realisasi)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td>{{ $dt->nama_lengkap }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->tgl_placement)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td>{{ $dt->nm_pbm }}</td>
                    <td>{{ $dt->nm_kapal }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15">
                        <h6 class="text-center text-danger">Tidak Ada Data</h6>
                    </td>
                </tr>
            @endforelse
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
