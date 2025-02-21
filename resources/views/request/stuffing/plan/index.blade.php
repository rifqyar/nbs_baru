@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item active">Stuffing Plan</li>
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
                    <h3><b>Perencanaan Kegiatan Stuffing</b></h3>
                    <div class="row py-2">
                        <div class="col-md-12">
                            <div class="alert alert-danger" role="alert">
                                <h4 class="mb-0 text-center">
                                    <span class="text-size-5">Terdapat {{ $jumlah }} request yang belum di
                                        approve</span>
                                    <i class="fas fa-exclamation-circle"></i>
                                </h4>
                            </div>

                            <div class="alert alert-primary" role="alert">
                                <h4 class="mb-0 text-center">
                                    Total request yang telah di approve tanggal <?= date('d F Y') ?> = {{ $total }}
                                    request ({{ $total_co }} box)
                                    <i class="fas fa-exclamation-circle"></i>
                                </h4>
                            </div>


                            <!-- Button on the right -->
                            <a href="{{ route('uster.new_request.stuffing.stuffing_plan.add') }}"
                                class="btn btn-primary float-right"><i class="fas fa-plus"></i> Tambah Perencanaan
                                Stuffing</a>
                           
                        </div>
                    </div>
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
                                    <th>NO REQUEST PLANNING</th>
                                    <th>NO REQUEST APP</th>
                                    <th>TANGGAL REQUEST</th>
                                    <th>PEB | NPE</th>
                                    <th>PEMILIK</th>
                                    <th>PEMILIK VESSEL/VOY</th>
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
                    url: '{!! route('uster.new_request.stuffing.stuffing_plan.datatable') !!}',
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
                        data: 'no_request_app',
                        name: 'no_request_app'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request',
                        render: function(data, type, row, meta) {
                            // Assuming 'data' is in the format "YYYY-MM-DD HH:mm:ss"
                            const originalDate = new Date(data);

                            // Define months for conversion
                            const months = [
                                "JAN", "FEB", "MAR", "APR", "MAY", "JUN",
                                "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"
                            ];

                            // Extract day, month, and year
                            const day = originalDate.getDate();
                            const month = months[originalDate.getMonth()];
                            const year = originalDate.getFullYear().toString().slice(-2);

                            // Create the new formatted date string
                            const formattedDateStr = `${day}-${month}-${year}`;

                            return formattedDateStr;
                        }
                    },
                    {
                        data: 'no_peb',
                        name: 'no_peb',
                        render: function(data, type, row, meta) {
                            // Check if data or row.no_npe is null, and handle accordingly
                            var nmKapal = data !== null ? data : '';
                            var noNpe = row.no_npe !== null ? row.no_npe : '';

                            // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                            return nmKapal + ' , ' + noNpe;
                        },
                        className: 'text-center'
                    },
                    {
                        data: 'nama_pemilik',
                        name: 'nama_pemilik'
                    },
                    {
                        data: 'nm_kapal',
                        name: 'nm_kapal',
                        render: function(data, type, row, meta) {
                            // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                            return data + ' , ' + row.voyage;
                        },
                        className: 'text-center'
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
                 
                            $('#service-table').DataTable().ajax
                                .reload();
                        }
                    });
                }
            });
        });
    </script>
@endpush
