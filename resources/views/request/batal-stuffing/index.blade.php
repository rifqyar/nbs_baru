@extends('layouts.app')

@section('title')
    Batal Stuffing
@endsection



@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item active">Batal Stuffing</li>
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
                    <h3><b>Batal Stuffing</b></h3>
                    <div class="row py-2">
                        <div class="col-md-12">



                            <!-- Button on the right -->
                            <a href="{{ route('uster.koreksi.batal_muat.add') }}"
                                class="btn btn-primary float-right"><i class="fas fa-plus"></i> Batal Stuffing</a>

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
                         <table class="display nowrap table data-table" cellspacing="0" width="100%" id="service-table">
                            <thead>
                                <tr>
                                    <th class="text-center">NO</th>
                                    <th class="text-center">No. BA</th>
                                    <th class="text-center">No. Req SPPS</th>
                                    <th class="text-center">No. Container</th>
                                    <th class="text-center">Tgl. Batal SPPS</th>
                                    <th class="text-center">Vessel</th>
                                    <th class="text-center">Voyage In</th>
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
                    url: '{!! route('uster.new_request.batal_spps.data') !!}',
                    type: 'POST',
                    data: function(d) {
                        d.from = $('#from').val();
                        d.to = $('#to').val();
                        d._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                    }
                },
                columns: [{
                        data: "DT_RowIndex",
                        name: "DT_RowIndex",
                        className: "text-center",
                        width: "20px",
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: "no_ba",
                        name: "no_ba",
                        className: "text-center",
                    },
                    {
                        data: "id_req_spps",
                        name: "id_req_spps",
                    },
                    {
                        data: "no_container",
                        name: "no_container",
                    },
                    {
                        data: "tgl_batal",
                        name: "tgl_batal",
                    },
                    {
                        data: "vessel",
                        name: "vessel",
                    },
                    {
                        data: "voyage_in",
                        name: "voyage_in",
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
