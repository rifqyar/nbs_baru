@extends('layouts.app')

@section('title')
Request Delivery - SP2 ke LUAR DEPO
@endsection


@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Request</a></li>
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item active">TPK / REPO MUAT - SP2</li>
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
                <h3><b>Request Delivery Ke TPK / REPO MUAT - SP2</b></h3>
                <div class="row py-2">
                    <div class="col-md-12">
                        <!-- Button on the right -->
                        <a href="{{route('uster.new_request.delivery.delivery_ke_luar_tpk.add')}}" class="btn btn-primary float-right">Tambah</a>
                        <!-- OR -->
                        <!-- <button class="btn btn-primary ml-auto">Your Button</button> -->
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
                    <table class="datatables-service table table-striped" id="service-table">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NO REQUEST</th>
                                <th>VESSEL/VOY</th>
                                <th>TGL REQUEST</th>
                                <th>EMKL/PEMILIK BARANG</th>
                                <th>REPO</th>
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
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.new_request.delivery.delivery_ke_luar_tpk.datatable") !!}',
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
                    data: 'nama_vessel',
                    name: 'nama_vessel',
                },
                {
                    data: 'tgl_request',
                    name: 'tgl_request',
                },
                {
                    data: 'nama_emkl',
                    name: 'nama_emkl',
                },
                {
                    data: 'jn_repo',
                    name: 'jn_repo',
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        if (data['lunas'] == 'YES' && data['status'] != 'BATAL') {
                            return '<a class="btn btn-success w-100" href="' + "{{route('uster.new_request.delivery.delivery_ke_luar_tpk.view')}}" + "?no_req=" + data['no_request'] + "&no_req2=" + data['no_req_ict'] + '" target="_blank"> Preview </a> ';
                        } else if (data['lunas'] == 'PIUTANG') {
                            return '<a class="btn btn-success w-100" href="' + "{{route('uster.new_request.delivery.delivery_ke_luar_tpk.view')}}" + "?no_req=" + data['no_request'] + "&no_req2=" + data['no_req_ict'] + '" target="_blank"> Preview </a> ';
                        } else if (data['lunas'] == 'YES' && data['status'] == 'BATAL') {
                            return '<a class="btn btn-warning w-100" href="' + "{{route('uster.new_request.delivery.delivery_ke_luar_tpk.edit')}}" + "?no_req=" + data['no_request'] + "&no_req2=" + data['no_req_ict'] + '" target="_blank"> Refisi </a> ';
                        } else {
                            return '<a class="btn btn-warning w-100" href="' + "{{route('uster.new_request.delivery.delivery_ke_luar_tpk.edit')}}" + "?no_req=" + data['no_request'] + "&no_req2=" + data['no_req_ict'] + '" target="_blank"> Refisi </a> ';
                        }
                    }
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