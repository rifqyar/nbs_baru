<div class="table-responsive px-3">
    <table class="display no-wrap table data-table" width="100%"  @if (!empty($data)) <div id="data-list"></div> @endif>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">No. Container</th>
                <th class="text-center">No. Request</th>
                <th class="text-center">Tgl. Gate</th>
                <th class="text-center">Nopol No. Seal</th>
                <th class="text-center">Username</th>
                <th class="text-center">Nama PBM</th>
                <th class="text-center">Nama Yard</th>
                <th class="text-center">Kegiatan</th>
                @if (($jenis == 'GATO' && $lokasi == '06') || ($jenis == 'GATO' && $lokasi == 'ALL'))
                    <th class="text-center">Vessel</th>
                    <th class="text-center">VOY</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $key => $dt)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $dt->no_container }}</td>
                    <td>{{ $dt->no_request }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ $dt->tgl_in }}
                        </span>
                    </td>
                    <td>{{ $dt->nopol }} <br> {{ $dt->no_seal }} </td>
                    <td>{{ $dt->username }}</td>
                    <td>{{ $dt->nm_pbm }}</td>
                    <td>{{ $dt->nama_yard ?? '-' }}</td>
                    <td>{{ $dt->kegiatan }}</td>
                    @if (($jenis == 'GATO' && $lokasi == '06') || ($jenis == 'GATO' && $lokasi == 'ALL'))
                        <td>{{ $dt->vessel }}</td>
                        <td>{{ $dt->voyage }}</td>
                    @endif
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
