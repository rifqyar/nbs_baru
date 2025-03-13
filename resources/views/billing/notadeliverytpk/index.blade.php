@extends('layouts.app')

@section('title')
Nota Delivery - SP2 TPK
@endsection


@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Billing</a></li>
            <li class="breadcrumb-item">Nota Delivery</li>
            <li class="breadcrumb-item active">SP2 TPK</li>
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
                <h3><b>Nota Delivery TPK</b></h3>
                <div class="row py-2">
                    <div class="col-md-12">
                        <!-- Button on the right -->
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
                                <th>TGL REQUEST</th>
                                <th>EMKL</th>
                                <th>TGL KELUAR</th>
                                <th>JML CONT</th>
                                <th>DELIVERY KE</th>
                                <th>JENIS REPO</th>
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
                url: '{!! route("uster.billing.notadeliverytpk.datatable") !!}',
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
                    name: 'tgl_request'
                },
                {
                    data: 'nama_emkl',
                    name: 'nama_emkl'
                },
                {
                    data: 'tgl_request_delivery',
                    name: 'tgl_request_delivery'
                },
                {
                    data: 'jumlah',
                    name: 'jumlah',
                    render: function(data, type, row, meta) {
                        // Render the combined value of 'nm_kapal' && 'voyage' with space && comma
                        return '<b>' + data + '</b> BOX';
                    }
                },
                {
                    data: 'delivery_ke',
                    name: 'delivery_ke'
                },
                {
                    data: 'jn_repo',
                    name: 'jn_repo'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        if ((data["nota"] != 'Y') && (data["koreksi"] != 'Y')) {
                            act = '<a class="btn btn-info w-100 mb-1" href="' + "{{route('uster.billing.notadeliverytpk.printnota')}}" + "?no_req=" + data['no_request'] + '"&koreksi="N" target="_blank"> Preview Proforma </a>';
                        } else if ((data["nota"] == 'Y') && (data["koreksi"] != 'Y')) {
                            act = '<a class="btn btn-info w-100 mb-1" href="' + "{{route('uster.billing.notadeliverytpk.printproforma')}}" + "?no_req=" + data['no_request'] + '"&koreksi="N" target="_blank"> Cetak Proforma </a>' + '<br/>' + '<a onclick="recalc(\'' + data["no_request"] + '\', \'' + data['no_nota'] + '\')" title="recalculate gerak" class="btn btn-success w-100 mb-1">Recalculate</a>' ;
                        } else if ((data["nota"] == 'Y') && (data["koreksi"] == 'Y')) {
                            act = '<a class="btn btn-info w-100 mb-1" href="' + "{{route('uster.billing.notadeliverytpk.printproforma')}}" + "?no_req=" + data['no_request'] + '"&koreksi="N" target="_blank"> Cetak Proforma </a>' + '<br/>' + '<a onclick="recalc(\'' + data["no_request"] + '\', \'' + data['no_nota'] + '\')" title="recalculate gerak" class="btn btn-success w-100 mb-1">Recalculate</a>' ;
                        } else if ((data["nota"] != 'Y') && (data["koreksi"] == 'Y')) {
                            act = '<a class="btn btn-info w-100 mb-1" href="' + "{{route('uster.billing.notadeliverytpk.printnota')}}" + "?no_req=" + data['no_request'] + '"&koreksi="N" target="_blank"> Preview Proforma </a>';
                        }

                        return act;
                    }
                },
            ],
            lengthMenu: [10, 20, 50, 100], // Set the default page lengths
            pageLength: 10, // Set the initial page length
            initComplete: function() {
                // Initialize date range filter inputs
                $('#from, #to').on('change', function() {
                    // Check if both 'from' && 'to' are filled before triggering the DataTable redraw
                    if ($('#from').val() !== '' && $('#to').val() !== '') {
                        console.log('sd');
                        $('#service-table').DataTable().ajax
                            .reload();
                    }
                });
            }
        });
    });

    function recalc(req, nota) {

        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to recalculate ' + req + ' . This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, recalculate',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: 'Menambahkan Data Container...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                var url = "{{ route('uster.billing.notadeliverytpk.recalc') }}";
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post(url, {
                    REQ: req,
                    NOTA: nota
                }, function(data) {
                    if (data == 'OK') {
                        Swal.fire({
                            icon: 'success',
                            text: 'Recalculation Success',
                            title: 'Success',
                        });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Recalculation Failed',
                            text: data,
                        });
                    }
                });
            }
        });

    }
</script>
@endpush
