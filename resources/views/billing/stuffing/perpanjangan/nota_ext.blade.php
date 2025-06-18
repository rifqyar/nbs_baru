@extends('layouts.app')

@section('title')
    Cetak Nota Perpanjangan Penumpukan Stuffing
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Billing</a></li>
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item active">Cetak Nota - Perpanjangan Penumpukan Stuffing</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p>
                </h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <h3><b>Cetak Nota - Perpanjangan Penumpukan Stuffing</b></h3>

                    <div class="row justify-content-end">

                        <div class="col-md-2">
                            <label for="from" class="text-end">Tanggal Request</label>
                            <input type="date" class="form-control" id="from" name="from">
                        </div>

                        <div class="col-md-2">
                            <label for="from" class="text-end">Sampai Dengan</label>
                            <input type="date" class="form-control" id="to" name="to">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="datatables-service table " id="service-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NO. REQUEST</th>
                                    <th>TGL. REQUEST</th>
                                    <th>EMKL</th>
                                    <th>VESSEL / VOYAGE JML</th>
                                    <th>CONT</th>
                                    <th class="text-center">ACTION</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.billing.nota_ext_pnkn_stuffing.datatable') !!}',
                    type: 'GET',
                    data: function(d) {
                        d.from = $('#from').val();
                        d.to = $('#to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex', // Use the special 'DT_RowIndex' property provided by DataTables
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_request',
                        name: 'no_request',
                        render: function(data, type, row, meta) {
                            // Mengambil nilai no_request dan no_nota dari objek 'row'
                            var noRequest = row.no_request !== null ?
                                '<strong>NO REQUEST:</strong> ' + row.no_request : '';
                            var noNota = row.no_nota !== null ? '<strong>NO NOTA:</strong> ' + row
                                .no_nota : '';

                            // Menggabungkan nilai no_request dan no_nota dengan pemisah koma dan spasi
                            var result = noRequest + (noRequest && noNota ? '<br>' : '') + noNota;

                            return result;
                        },
                        width: '230px',
                        className: 'text-center'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },

                    {
                        data: 'nama_emkl',
                        name: 'nama_emkl',
                        width: '170px',
                    },

                    {
                        data: 'nama_vessel',
                        name: 'nama_vessel',
                        render: function(data, type, row, meta) {
                            // Check if data or row.voyage is null, and handle accordingly
                            var vessel = data !== null ? data : '';
                            var voyage = row.voyage !== null ? row.voyage : '';

                            // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                            return vessel + ' , ' + voyage;
                        },
                        className: 'text-center',
                        width: '230px',
                    },

                    {
                        data: 'jml_cont',
                        name: 'jml_cont',
                        render: function(data, type, row, meta) {
                            // Check if data or row.box is null, and handle accordingly
                            var jml_cont = data !== null ? data : '';
                            var box = 'BOX';

                            // Render the combined value of 'nm_kapal' and 'box' with space and comma
                            return jml_cont + ' ' + box;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        render: function(data, type, row) {
                            return data; // Mengembalikan data apa adanya karena data sudah dalam format HTML yang sesuai
                        },
                        orderable: false,
                        searchable: false,
                    },
                ],
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
                initComplete: function() {
                    // Initialize date range filter inputs
                    $('#from, #to').on('change', function() {
                        // Check if both 'from' and 'to' are filled before triggering the DataTable redraw
                        if ($('#from').val() !== '' && $('#to').val() !== '') {

                            $('#service-table').DataTable().ajax
                                .reload();
                        }
                    });
                }
            });
        });
    </script>
@endpush
