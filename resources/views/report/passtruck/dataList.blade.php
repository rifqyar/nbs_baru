<table class="table table-bordered table-striped table-hover font-14" id="table_passtruck">
    <thead class="bg-info text-white">
        <tr>
            <th class="text-center" style="width: 50px;">NO</th>
            <th class="text-center">NO REQUEST</th>
            <th class="text-center">TANGGAL</th>
            <th class="text-center">NO NOTA</th>
            <th class="text-center">NO FAKTUR</th>
            <th class="text-center">KETERANGAN</th>
            <th class="text-center">COA</th>
            <th class="text-center">KEGIATAN</th>
            <th class="text-right">TARIF</th>
            <th class="text-center">JUMLAH PASS</th>
            <th class="text-right">BIAYA</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center font-weight-bold text-primary">{{ $row->no_request ?? '-' }}</td>
                <td class="text-center">{{ $row->tanggal ?? '-' }}</td>
                <td class="text-center">{{ $row->no_nota ?? '-' }}</td>
                <td class="text-center">{{ $row->no_faktur ?? '-' }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
                <td class="text-center text-info font-weight-bold">{{ $row->coa ?? 'RUPA' }}</td>
                <td class="text-center font-weight-bold">{{ $row->kegiatan ?? '-' }}</td>
                <td class="text-right">{{ number_format($row->tarif ?? 0, 0, ',', '.') }}</td>
                <td class="text-center font-weight-bold">{{ number_format($row->jumlah_pass ?? 0, 0, ',', '.') }}</td>
                <td class="text-right font-weight-bold text-success">{{ number_format($row->biaya ?? 0, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center py-4 text-muted">
                    <i class="mdi mdi-alert-circle-outline font-20 d-block mb-1"></i>
                    Tidak ada data pass truck ditemukan pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($data) > 0)
        <tfoot class="bg-light font-weight-bold">
            <tr>
                <td colspan="9" class="text-right">TOTAL :</td>
                <td class="text-center text-primary">{{ number_format($total_pass ?? 0, 0, ',', '.') }}</td>
                <td class="text-right text-success">Rp {{ number_format($total_biaya ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    @endif
</table>
