@extends('layouts.app')

@section('title')
    Payment Cash
@endsection

@section('pages-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Billing</a></li>
                <li class="breadcrumb-item active">Payment Cash</li>
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
                    <h3><b>Payment Cash</b></h3>
                    <!-- <div class="row justify-content-end">

                                            <div class="col-md-2">
                                                <label for="from" class="text-end">Tanggal Request</label>
                                                <input type="date" class="form-control" id="from" name="from">
                                            </div>

                                            <div class="col-md-2">
                                                <label for="from" class="text-end">Sampai Dengan</label>
                                                <input type="date" class="form-control" id="to" name="to">
                                            </div>
                                        </div> -->
                    <div class="table-responsive">
                        <table class="datatables-service table table-striped" id="service-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NO. NOTA</th>
                                    <th>No. Proforma</th>
                                    <th>No. Faktur SAP</th>
                                    <th>No. Request</th>
                                    <th>EMKL</th>
                                    <th>Modul</th>
                                    <th>Tanggal Kegiatan</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-item" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="edit-item-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit-item-label">
                        Payment
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="edit-data-list-item">
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
                    url: '{!! route('uster.billing.paymentcash.datatable') !!}',
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
                        data: 'no_nota',
                        name: 'no_nota',
                    },
                    {
                        data: 'no_nota_mti',
                        name: 'no_nota_mti',
                    },
                    {
                        data: 'no_faktur_mti',
                        name: 'no_faktur_mti'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request',
                    },
                    {
                        data: 'emkl',
                        name: 'emkl',
                    },
                    {
                        data: 'kegiatan',
                        name: 'kegiatan',
                    },
                    {
                        data: 'tgl_nota_1',
                        name: 'tgl_nota_1',
                    },
                    {
                        data: 'total_tagihan',
                        name: 'total_tagihan',
                        render: function(data, type, row, meta) {
                            const formatter = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                            });

                            return formatter.format(data);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            const SAP_URL = "{{ env('SAP_URL') }}";
                            if (data['cek'] == '0') {
                                // if ( `{{ Session::get('ID_GROUP') }}` == 'L' || `{{ Session::get('ID_GROUP') }}` == 'J' || `{{ Session::get('ID_GROUP') }}` == 'P' || `{{ Session::get('ID_GROUP') }}` == 'K') {
                                //     act = `<a href="#" onclick='pay("${data['no_nota']}", "${data['no_request']}", "${data['kegiatan']}", "${data['total_tagihan']}", "${data['kd_emkl']}", "${data['status']}", "${data['no_nota_mti']}", "${data['tgl_nota_1']}")'><i class="fas fa-file-alt text-danger"></i></a>`;
                                // } else {
                                //     act = "<font color='red'><i>not yet paid</i></font>";
                                // }

                                if (row['payment_code'] != null) {
                                    let req = row['no_request'];
                                    let kegiatan = row['kegiatan'];

                                    // Generate the button for printing the SAP payment code with FontAwesome icon
                                    act = `<a href='javascript:void(0)' onclick="return printPaymentCode('${data['no_request']}', '${data['kegiatan']}')" return false;" title='Cetak Kode Bayar SAP'>
                                        <i class="fa-solid fa-print" style="font-size: 20px;"></i>
                                    </a>`;
                                } else {
                                    // Display "not yet paid" message in red
                                    act = "<font color='red'><i>not yet paid</i></font>";
                                }

                            } else {
                                // If 'cek' is not 0, proceed to create the nota print action
                                let no_nota = row['no_nota'];
                                let url = SAP_URL + `PrintNota/CetakNota?ze=${no_nota}&ck=6200`;

                                // Create the first print button with FontAwesome icon for Excel export
                                act = `<a href="#" onclick="return print('${data['no_request']}', '${data['kegiatan']}', '${data['tgl_nota_1']}')" title="Cetak Nota">
                                    <i class="fas fa-file-excel text-megna" style="font-size: 20px;"></i>
                                </a>`;

                                // If payment code exists, add another button with FontAwesome icon for printing the SAP nota
                                if (row['payment_code'] != null) {
                                    act += `<a href='javascript:void(0)' onclick="window.open('${url}'); return false;" title='Cetak Nota SAP'>
                                <i class="fa-solid fa-receipt"></i>
                                </a>`;
                                }
                            }
                            return act;
                        }
                    },
                ],
                lengthMenu: [ 20, 50, 100], // Set the default page lengths
                pageLength: 20, // Set the initial page length
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

        function print(a, b, c) {

            var url;
            var url2;
            url = '{!! route('uster.billing.paymentcash.print') !!}' + '?no_req=' + a + '&jn=' + b + '&tgl=' + c;


            window.open(url, '_blank');


            return false;
        }

        function printPaymentCode(a, b, c) {

            var url;
            var url2;
            url = '{!! route('uster.billing.paymentcash.print_kode_bayar') !!}' + '?no_req=' + a + '&kegiatan=' + b ;


            window.open(url, '_blank');


            return false;
        }

        function pay(a, b, c, total, emkl, koreksi, mti, tgl) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            });

            var url = '{!! route('uster.billing.paymentcash.pay') !!}' + '?idn=' + a + '&req=' + encodeURIComponent(b) + '&ket=' + c + '&total=' +
                total + '&emkl=' + emkl + '&koreksi=' + koreksi + '&mti=' + mti + '&tgl=' + tgl;
            $.ajax({
                url: url,
                type: "GET",
                processData: false,
                contentType: false,
                success: function(data) {
                    data = JSON.parse(data);
                    if (data["status"] == "success") {
                        $("#edit-data-list-item").html(data["output"]);
                        $("#edit-item").modal("toggle");
                    } else {
                        Toast.fire({
                            icon: "error",
                            title: data["message"],
                        });
                    }
                },
                error: function(reject) {
                    Toast.fire({
                        icon: "error",
                        title: "Something went wrong",
                    });
                },
            });
        }
    </script>
@endpush
