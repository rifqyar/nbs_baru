<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembelian Barang</title>
    <style>
        html {
            margin: 10px
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 3px;
        }

        th {
            background-color: #f2f2f2;
        }

        .title {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
        }

        .info {
            font-size: 7px;
            margin-bottom: 5px;
        }

        .thanks {
            font-size: 7px;
            margin-top: 10px;
            text-align: center;
        }

        .barcode {
            text-align: center;
            margin-bottom: 10px;
        }

        .barcode img {
            width: 250px;
            /* Atur lebar gambar */
            height: auto;
            /* Biarkan tinggi gambar disesuaikan secara proporsional */
        }


        .page-break {
            page-break-after: always;
        }

        .border-box {
            border: 1px solid #000;
            padding: 10px;
        }

        .stamp {
            position: absolute;
            width: 100%;
            height: 150px;
            /* Adjust height according to your image */
            background-image: url('data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/lunas.png'))) }}');
            background-size: cover;
            text-align: center;
            top: 20%;
            opacity: 0.3;
            /* Atur transparansi menjadi 50% */
        }
    </style>
</head>

<body>
    {{-- <div class="stamp"> </div> --}}
    <div class="barcode">
        <img src="data:image/png;base64,'.{{ base64_encode($barcode) }}.'" alt="barcode">
        <span>{{ $data->no_nota_mti }}</span><br>
        <span><strong>PENUMPUKAN STUFFING</strong></span>
    </div>
    <span><strong>Nomor Nota</strong> : {{ $data->no_nota_mti }}</span><br>
    <span><strong>Tanggal</strong> : {{ $date }}</span><br>
    <span><strong>Nomor SFP</strong> : {{ $data->no_request }}</span><br>
    <span><strong>Perusahaan</strong> : {{ $data->nama }}</span><br>
    <span><strong>Deskripsi</strong> : {{ $data->npwp }}</span><br>
    <span><strong>Alamat</strong> : {{ $data->alamat }}</span><br>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Deskripsi</th>
                <th>Tarif</th>
                <th>HZ</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
            @endphp
            @foreach ($detail as $item)
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>{{ $item->size_ . $item->type_ . $item->status }}</td>
                    <td>{{ $item->hz }}</td>
                    <td>{{ $item->tarif }}</td>
                    <td>{{ $item->biaya }}</td>
                </tr>
                @php
                    $i++;
                @endphp
            @endforeach
            <tr class="total">
                <td colspan="5">Administrasi</td>
                <td>{{ $data->adm_nota }}</td>
            </tr>
            <tr class="total">
                <td colspan="5">Dasar Peng. Pajak</td>
                <td>{{ $data->tagihan }}</td>
            </tr>
            <tr class="total">
                <td colspan="5">Jumlah PPN</td>
                <td>{{ $data->ppn }}</td>
            </tr>
            @if (intval($bea_materai = $data_mtr->bea_materai ?? 0) > 0)
                <tr class="total">
                    <td colspan="5">Bea Materai</td>
                    <td>{{ $bea_materai }}</td>
                </tr>
            @endif
            <tr class="total">
                <td colspan="5">Jumlah Dibayar</td>
                <td>{{ $data->total_tagihan }}</td>
            </tr>
        </tbody>
    </table>

    <br>
    {!! $nama_lengkap !!}


    @if (intval($bea_materai = $data_mtr->bea_materai ?? 0) > 0)
        <p><strong>Bea Materai Lunas Dengan Sistem Nomor Ijin</strong>: <br>{{ $no_mat }}</p>
        <div style="border: 1px solid #000; padding: 10px; text-align:center">
            Termasuk Bea Materai<br>
            Rp. {{ $bea_materai }}
        </div>
    @endif

    {{-- <div class="page-break"></div>
    <div class="stamp"> </div> --}}

    <h2 style="text-align: center">PT. Multi Terminal Indonesia</h2>
    <hr>
    <p>Nomor Invoice: {{ $data->no_nota_mti }}</p>
    <p>Customer: {{ $data->nama }}</p>
    <p>Jumlah Dibayar: Rp. {{ $data->total_tagihan }}</p>

    <table style="font-size: 5px">
        <thead>
            <tr>
                <th>Nomor Container</th>
                <th>Jenis</th>
                <th>Nomor Container</th>
                <th>Jenis</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 0; @endphp
            @foreach ($rcont as $item)
                @if ($count % 2 == 0)
                    <tr>
                        <td>{{ $item->no_container }}</td>
                        <td>{{ $item->size_ }}-{{ $item->type_ }}-{{ $item->status }}</td>
                        {{-- Jika container berjumlah ganjil dan ini adalah baris terakhir, tambahkan baris kosong --}}
                        @if ($count == count($rcont) - 1)
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                    </tr>
                @endif
            @else
                <td>{{ $item->no_container }}</td>
                <td>{{ $item->size_ }}-{{ $item->type_ }}-{{ $item->status }}</td>
                </tr>
            @endif
            @php $count++; @endphp
            @endforeach
        </tbody>
    </table>

</body>

</html>
