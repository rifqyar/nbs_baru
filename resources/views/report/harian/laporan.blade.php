@extends('layouts.app')

@section('title')
    Laporan Harian
@endsection

@section('pages-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Report</a></li>
                <li class="breadcrumb-item active">Laporan Harian</li>
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
                    <h3><b>Laporan Harian</b></h3>
                    <div class="border rounded my-3">
                        <form id="dataCont">
                            @csrf
                            <div class="p-3">
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="id_req">ID Req: </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="id_req" name="id_req" class="form-control" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="id_time">ID Time: </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="date" id="id_time" name="id_time" class="form-control" />
                                    </div>
                                </div>
                            </div>
                        </form>
                        <button onclick="genNosp()" class="btn btn-primary mr-2"><i class="fas fa-file-alt pr-2"></i>
                            Generate Report</button>
                        <button onclick="toExcel()" class="btn excel-button mr-2"><i class="fas fa-file-excel pr-2"></i>
                            Generate Excel</button>

                    </div>
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table class="datatables-service table " id="container-table">
                                <thead>
                                    <!-- Thead will be populated dynamically -->
                                </thead>

                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('pages-js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Call genReport on page load
        $(document).ready(function() {
            genReport();
        });

        function genReport() {
            if ($.fn.DataTable.isDataTable('#container-table')) {
                $('#container-table').DataTable().destroy();
                $('#container-table').empty(); // Clear the table
            }

            var csrfToken = '{{ csrf_token() }}';
            var id_req = $("#id_req").val();
            var id_time = $("#id_time").val();

            updateTableHeader();

            $('#container-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{!! route('uster.report.laporan_harian.datatable') !!}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        id_req: id_req,
                        id_time: id_time
                    }
                },
                columns: getColumns(),
                lengthMenu: [10, 20, 50, 100],
                pageLength: 10
            });
        }

        function updateTableHeader() {
            var thead = $('#container-table thead');
            thead.empty(); // Clear existing header
            var headerRow = $('<tr></tr>');
            headerRow.append('<th>No</th>');
            headerRow.append('<th>No. Request</th>');
            headerRow.append('<th>No. Nota</th>');
            headerRow.append('<th>No. Faktur</th>');
            headerRow.append('<th>Total</th>');
            headerRow.append('<th>Customer</th>');
            headerRow.append('<th>No. SP</th>');
            thead.append(headerRow);
        }

        function decodeHtml(html) {
            var txt = document.createElement('textarea');
            txt.innerHTML = html;
            return txt.value;
        }

        function getColumns() {
            return [{
                    data: null,
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'no_request',
                    name: 'no_request'
                },
                {
                    data: 'no_nota_mti',
                    name: 'no_nota_mti',
                    render: function(data, type, row) {
                        return decodeHtml(data);
                    }
                },
                {
                    data: 'no_faktur_mti',
                    name: 'no_faktur_mti',
                    render: function(data, type, row) {
                        return decodeHtml(data);
                    }
                },
                {
                    data: 'kredit',
                    name: 'kredit',
                    render: $.fn.dataTable.render.number(',', '.', 0, 'Rp. ')
                },
                {
                    data: 'nm_pbm',
                    name: 'nm_pbm',
                    render: function(data, type, row) {
                        return decodeHtml(data);
                    }
                },
                {
                    data: 'sp_mti',
                    name: 'sp_mti'
                }
            ];
        }

        function genNosp() {
            var id_req = $("#id_req").val();
            var id_time = $("#id_time").val();

            // Cek jika ID Time kosong
            if (!id_time) {
                input_error({ message: "Harap pilih ID Time sebelum melanjutkan." });
                return; // Hentikan eksekusi
            }


            var csrfToken = '{{ csrf_token() }}';
            
            $.ajax({
                url: '{{ route("uster.report.laporan_harian.generatenosp") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    id_req: id_req,
                    id_time: id_time
                },
                dataType: 'json',
                success: function(response) {
                    input_success(response); // Jika sukses, panggil input_success
                    genReport(); // Panggil ulang DataTable untuk memperbarui data
                },
                error: function(xhr, status, error) {
                    input_error(xhr.responseJSON || { message: "Terjadi kesalahan." }); 
                    genReport(); // Panggil ulang DataTable untuk memperbarui data
                    // Jika error, panggil input_error
                },
                complete: function() {
                    $.unblockUI();
                }
            });

            $("#id_time").val("");
        }


        function input_success(res) {
            if (res.status != 200) {
                input_error(res);
                return false;
            }

            $.toast({
                heading: "Berhasil!",
                text: res.message,
                position: "top-right",
                icon: "success",
                hideAfter: 2500,
            
            });
        }

        function input_error(err) {
            console.log(err);
            $.toast({
                heading: "Gagal memproses data!",
                text: err.message,
                position: "top-right",
                icon: "error",
                hideAfter: 5000,
            });
        }

        

        function toExcel() {
            var id_req_ = $("#id_req").val();
            var id_time_ = $("#id_time").val();

            var url = '{!! route('uster.report.laporan_harian.report') !!}?id_req=' + id_req_ + '&id_time=' + id_time_;

            window.location.href = url;
        }
    </script>
@endsection
