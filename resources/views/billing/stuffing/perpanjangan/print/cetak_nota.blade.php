<!DOCTYPE html>
<html lang="en">

<head>
    @php
        $nota = $data->no_nota_mti ?? '';
        $no_request = $data->no_request ?? '';
        $nama = $data->nama ?? '';
        $npwp = $data->npwp ?? '';
        $alamat = $data->alamat ?? '';
        $adm_nota = $data->adm_nota ?? '';
        $tagihan = $data->alamat ?? '';
        $ppn = $data->ppn ?? '';
        $total_tagihan = $data->total_tagihan ?? '';
    @endphp
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CETAK PERPANJANGAN STUFFING {{ $no_request }}</title>
    <link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="http://127.0.0.1:8000/assets/plugins/sweetalert/sweetalert.css" rel="stylesheet" type="text/css">

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
            color: #000;
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

        @media only screen and (min-width: 400px) and (max-width: 1024px) {
            .container {
                padding-left: 5%;
                padding-right: 5%;
            }
        }

        @media only screen and (min-width: 1025px) {
            .container {
                padding-left: 30%;
                padding-right: 30%;
            }
        }




        span {
            color: #000;
        }

        p {
            color: #000;
        }

        .content {
            padding: 20px;
            border: #000 1px solid;
            border-radius: 10px;
        }
    </style>


</head>

<body>



    <div class="container">
        <div class="content">
            <h2 style="text-align: center">PT. Multi Terminal Indonesia</h2>
            <div class="barcode">
                <img src="data:image/png;base64,{{ base64_encode($barcode) }}" alt="barcode"><br>
                <span>{{ $nota }}</span><br>
                <span><strong>PERPANJANGAN STUFFING</strong></span>
            </div>
            <span><strong>Nomor Nota</strong> : {{ $nota }}</span><br>
            <span><strong>Tanggal</strong> : {{ $date }}</span><br>
            <span><strong>Nomor SFP</strong> : {{ $no_request }}</span><br>
            <span><strong>Perusahaan</strong> : {{ $nama }}</span><br>
            <span><strong>Deskripsi</strong> : {{ $npwp }}</span><br>
            <span><strong>Alamat</strong> : {{ $alamat }}</span><br>

            <div class="table-responsive">
                <table class="table table-bordered">
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
                            <td>{{ $adm_nota }}</td>
                        </tr>
                        <tr class="total">
                            <td colspan="5">Dasar Peng. Pajak</td>
                            <td>{{ $data->tagihan }}</td>
                        </tr>
                        <tr class="total">
                            <td colspan="5">Jumlah PPN</td>
                            <td>{{ $ppn }}</td>
                        </tr>
                        @if (intval($bea_materai = $data_mtr->bea_materai ?? 0) > 0)
                            <tr class="total">
                                <td colspan="5">Bea Materai</td>
                                <td>{{ $bea_materai }}</td>
                            </tr>
                        @endif
                        <tr class="total">
                            <td colspan="5">Jumlah Dibayar</td>
                            <td>{{ $total_tagihan }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>




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


            <hr>
            <p>Nomor Invoice: {{ $nota }}</p>
            <p>Customer: {{ $nama }}</p>
            <p>Jumlah Dibayar: Rp. {{ $total_tagihan }}</p>

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
            <div class="d-flex justify-content-center align-items-center mt-4">
                <button type="submit" class="btn btn-primary btn-sm" onclick="insertNota()">
                    <i class="mdi mdi-content-save"></i> Cetak Nota
                </button>
            </div>


        </div>
    </div>



</body>

<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function insertNota() {

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to save nota {{ request()->input('no_req') }} . This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, save nota',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: 'Saving Nota Request...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                var url = "{{ route('uster.billing.nota_ext_pnkn_stuffing.insert_proforma') }}";
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post(url, {
                    no_req: '{{ request()->input('no_req') }}',
                    koreksi: '{{ request()->input('koreksi') }}',
                }, function(data) {
                    if (data == 'OK') {
                        Swal.fire({
                            icon: 'success',
                            text: 'Save Nota Success',
                            title: 'Success',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(
                                    "{{ route('uster.billing.nota_ext_pnkn_stuffing.print_proforma') }}?no_req={{ request()->input('no_req') }}",
                                    '_blank');
                            }
                        });
                    } else if (data == 'OK-INSERT') {
                        Swal.fire({
                            icon: 'success',
                            text: 'Save Nota Success',
                            title: 'Success',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(
                                    "{{ route('uster.billing.nota_ext_pnkn_stuffing.print_proforma') }}?no_req={{ request()->input('no_req') }}&first=1",
                                    '_blank');
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Save Nota Failed',
                            text: data,
                        });
                    }
                });
            }
        });

    }
</script>

</html>
