@extends('layouts.app')

@section('title')
    Batal Muat
@endsection



@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item active">Batal Muat</li>
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
                    <h3><b>Batal Muat</b></h3>
                    <div class="row py-2">
                        <div class="col-md-12">
                            


                            <!-- Button on the right -->
                            <a href="{{ route('uster.koreksi.batal_muat.add') }}"
                                class="btn btn-primary float-right"><i class="fas fa-plus"></i> Batal Muat</a>
                           
                        </div>
                    </div>
                    <div class="row justify-content-end">

                        {{-- <div class="col-md-2">
                            <label for="from" class="text-end">Tanggal Request</label>
                            <input type="date" class="form-control" id="from" name="from">
                        </div>

                        <div class="col-md-2">
                            <label for="from" class="text-end">Sampai Dengan</label>
                            <input type="date" class="form-control" id="to" name="to">
                        </div> --}}
                    </div>
                    <div class="table-responsive">
                        <table class="datatables-service table " id="service-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NO. REQUEST</th>
                                    <th>TGL. REQUEST</th>
                                    <th>EMKL</th>
                                    <th>JENIS BM</th>
                                    <th>KEGIATAN</th>
                                    <th>ACTION</th>
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
                    url: '{!! route('uster.koreksi.batal_muat.datatable') !!}',
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
                        data: 'nm_pbm',
                        name: 'nm_pbm',
                        render: function(data, type, row, meta) {
                            var nm_pbm = row.nm_pbm !== null ?
                                '<strong>' + row.nm_pbm : '</strong>';
                            var box = row.no_nota !== null ? '<strong>Box : </strong> ' + row
                                .box : '';
                            var result = nm_pbm + (nm_pbm && box ? '<br>' : '') + box;

                            return result;
                        },
                        width: '230px',
                        className: 'text-center'
                    },

                    {
                        data: 'jenis_bm',
                        name: 'jenis_bm',
                    },
                    {
                        data: 'status_gate',
                        name: 'status_gate',
                        render: function(data, type, row, meta) {
                            var status_gate = row.status_gate !== null ?
                                '<strong>' + row.status_gate : '</strong>';
                            var no_req_baru = row.no_req_baru !== null ? '<strong>no.req baru : </strong> ' + row
                                .no_req_baru : '';
                            var result = status_gate + (status_gate && no_req_baru ? '<br>' : '') + no_req_baru;

                            return result;
                        },
                        width: '230px',
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
