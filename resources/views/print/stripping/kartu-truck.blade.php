@extends('layouts.app')

@section('title')
    Cetak Kartu
@endsection



@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Print</a></li>
                <li class="breadcrumb-item">Stripping</li>
                <li class="breadcrumb-item active">Perpanjangan Stripping</li>
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
                    <h3><b>Cetak Kartu - Surat Perintah Pelaksanaan Stripping </b></h3>

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
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="service-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No Request</th>
                                    <th>Tanggal Request</th>
                                    <th>EMKL</th>
                                    <th>PEMILIK</th>
                                    <th>Action</th>
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
                    url: '{!! route('uster.print.stripping.GetTruckStripping') !!}',
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
                        name: 'no_request'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request',
                        className: 'text-center'
                    },
                    {
                        data: 'nama_consignee',
                        name: 'nama_consignee'
                    },

                    {
                        data: 'nama_penumpuk',
                        name: 'nama_penumpuk'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
                initComplete: function() {
                    // Initialize date range filter inputs
                    $('#from, #to').on('change', function() {
                        // Check if both 'from' and 'to' are filled before triggering the DataTable redraw
                        if ($('#from').val() !== '' && $('#to').val() !== '') {
                            console.log('sd');
                            $('#service-table').DataTable().ajax
                                .reload();
                        }
                    });
                }
            });
        });
    </script>
@endpush
