<div class="table-responsive px-3">
    <table class="display no-wrap table data-table" width="100%" id="data-list">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">No. Nota</th>
                <th class="text-center">No. Faktur Pajak</th>
                <th class="text-center">No. Request</th>
                <th class="text-center">Kegiatan</th>
                <th class="text-center">Tgl. Kegiatan</th>
                <th class="text-center">Pemilik Barang</th>
                <th class="text-center">Pembayaran</th>
                <th class="text-center">Total Tagihan</th>
                <th class="text-center">Status Lunas</th>
                <th class="text-center">Status Batal/Tidak</th>
                <th class="text-center">Status Nota</th>
                <th class="text-center" style="white-space: pre-wrap !important">Status Transfer ke Simkeu</th>
                <th class="text-center">Bank</th>
            </tr>
        </thead>
        <tbody id="nota-body">
            {{-- @forelse ($data as $key => $dt)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $dt->no_nota_mti }}</td>
                    <td>{{ $dt->no_faktur_mti }}</td>
                    <td>{{ $dt->no_request }}</td>
                    <td>{{ $dt->kegiatan }}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i>
                            {{ \Carbon\Carbon::parse($dt->tgl_nota)->translatedFormat('Y-m-d') }}
                        </span>
                    </td>
                    <td data-toggle="tooltip" data-placement="top" title="{{ $dt->emkl }}">
                        {{ Str::limit($dt->emkl, 25, '...') }}</td>
                    <td>{{ $dt->bayar }}</td>
                    <td>Rp. {{ str_replace(',', '.', $dt->total_tagihan) }}</td>
                    <td>{{ $dt->lunas }}</td>
                    <td>{{ $dt->status }}</td>
                    <td>
                        <span class="badge bg-success rounded-pill p-2 text-white">Ready to Transfer</span>
                    </td>
                    <td>
                        @if ($dt->transfer == 'Y')
                            <span class="badge bg-info rounded-pill p-2 text-white">Sudah Transfer</span>
                        @else
                            <span class="badge bg-danger rounded-pill p-2 text-white">Belum Transfer</span>
                        @endif
                    </td>
                    <td style="white-space: pre-wrap !important" class="text-center">{{ $dt->receipt_account }}</td>
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

{{-- <script>
    $(function() {
        const tooltipTriggerList = document.querySelectorAll('[data-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(
            tooltipTriggerEl))
    })
</script> --}}
