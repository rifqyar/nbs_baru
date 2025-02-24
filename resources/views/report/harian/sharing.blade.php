@extends('layouts.app')

@section('title')
    Laporan Container Stuffing
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
                <li class="breadcrumb-item active">Laporan Container Stuffing</li>
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
                    <h3><b>Laporan Container Stuffing</b></h3>
                    <div class="border rounded my-3">
                        <form id="dataCont">
                            @csrf
                            <div class="p-3">
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="jenis">Jenis: </label>
                                    </div>
                                    <div class="col-md-4">
                                        <select id="jenis" name="jenis" class="form-control">
                                            <option value="STRIPPING">STRIPPING</option>
                                            <option value="REPO">REPO</option>
                                            <option value="STUFFING">STUFFING</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Periode: </label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input class="form-control" type="date" name="tgl_awal" id="tgl_awal" />
                                            </div>
                                            /
                                            <div class="col-md-4">
                                                <input class="form-control" type="date" name="tgl_akhir"
                                                    id="tgl_akhir" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <button onclick="genReport()" class="btn btn-primary mr-2"><i class="fas fa-file-alt pr-2"></i>
                            Generate Report</button>
                        <button onclick="toExcel()" class="btn excel-button mr-2"><i class="fas fa-file-excel pr-2"></i>
                            Generate Excel</button>
                       
                    </div>
                    <div class="card p-2">
                        <div class="table-responsive">
                            <table class="datatables-service table " id="container-table">
                                <thead>
                                    <tr>
                                        <th>No </th>
                                        <th>No Container</th>
                                        <th>No Spps</th>
                                        <th>No Request Delivery</th>
                                        <th>Tgl Request Delivery</th>
                                        <th>EMKL</th>
                                        <th>Vessel</th>
                                    </tr>
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
        function genReport() {
            if ($.fn.DataTable.isDataTable('#container-table')) {
                var table = $('#container-table').DataTable();
                table.destroy();
            }
            var csrfToken = '{{ csrf_token() }}';
            var tgl_awal = $("#tgl_awal").val();
            var tgl_akhir = $("#tgl_akhir").val();
            var jenis = $("#jenis").val();

            updateTableHeader(jenis);

            $('#container-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{!! route('uster.report.sharing_penumpukan.datatable') !!}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        tgl_awal: tgl_awal,
                        tgl_akhir: tgl_akhir,
                        jenis: jenis
                    }
                },
                columns: getColumns(jenis),
                lengthMenu: [10, 20, 50, 100],
                pageLength: 10
            });
        }

        function updateTableHeader(jenis) {
            var thead = $('#container-table thead');
            thead.empty(); // Clear existing header
            var headerRow = $('<tr></tr>');
            if (jenis == 'REPO') {
                headerRow.append('<th>No</th>');
                headerRow.append('<th>Nota TPK</th>');
                headerRow.append('<th>Nota Uster</th>');
                headerRow.append('<th>Nilai Nota</th>');
                headerRow.append('<th>Tgl. Request</th>');
                headerRow.append('<th>No. Request</th>');
                headerRow.append('<th>Biaya Masa 1.1</th>');
                headerRow.append('<th>Biaya Masa 1.2</th>');
                headerRow.append('<th>Biaya Masa 2</th>');
                headerRow.append('<th>Total Penumpukan</th>');
                headerRow.append('<th>Total Penumpukan + PPN</th>');
                headerRow.append('<th>Biaya Masa 1.2 TPK</th>');
                headerRow.append('<th>Biaya Masa 2 TPK</th>');
                headerRow.append('<th>Penumpukan TPK</th>');
                headerRow.append('<th>PPN Penumpukan TPK</th>');
                headerRow.append('<th>Lift Of TPK</th>');
                headerRow.append('<th>Lift Of TPK + PPN</th>');
                headerRow.append('<th>Total Hak TPK + PPN</th>');
            } else {
                headerRow.append('<th>No</th>');
                headerRow.append('<th>No. Request</th>');
                headerRow.append('<th>Biaya Masa 1.1</th>');
                headerRow.append('<th>Biaya Masa 1.2</th>');
                headerRow.append('<th>Biaya Masa 2</th>');
                headerRow.append('<th>Total Penumpukan</th>');
                headerRow.append('<th>Total Penumpukan + PPN</th>');
                headerRow.append('<th>Biaya Masa 1.2 USTER</th>');
                headerRow.append('<th>Biaya Masa 2 USTER</th>');
                headerRow.append('<th>Penumpukan USTER</th>');
                headerRow.append('<th>PPN Penumpukan USTER 10 Persen</th>');
                headerRow.append('<th>Total Penumpukan USTER + PPN</th>');
                headerRow.append('<th>Hak TPK</th>');
                headerRow.append('<th>Lift On TPK + PPN</th>');
                headerRow.append('<th>Total Hak TPK</th>');
            }
            thead.append(headerRow);
        }

        function getColumns(jenis) {
            if (jenis == 'REPO') {
                return [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'nota_tpk',
                        name: 'nota_tpk'
                    },
                    {
                        data: 'nota_uster',
                        name: 'nota_uster'
                    },
                    {
                        data: 'nilai_nota',
                        name: 'nilai_nota'
                    },
                    {
                        data: 'tgl_request',
                        name: 'tgl_request'
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'biaya_masa11',
                        name: 'biaya_masa11'
                    },
                    {
                        data: 'biaya_masa12',
                        name: 'biaya_masa12'
                    },
                    {
                        data: 'biaya_masa2',
                        name: 'biaya_masa2'
                    },
                    {
                        data: 'total_penumpukan',
                        name: 'total_penumpukan'
                    },
                    {
                        data: 'total_penumpukan_plus_ppn',
                        name: 'total_penumpukan_plus_ppn'
                    }, // Sesuai dengan field di respons
                    {
                        data: 'biaya_masa12_tpk',
                        name: 'biaya_masa12_tpk'
                    },
                    {
                        data: 'biaya_masa2_tpk',
                        name: 'biaya_masa2_tpk'
                    },
                    {
                        data: 'penumpukan_tpk',
                        name: 'penumpukan_tpk'
                    },
                    {
                        data: 'ppn_penumpukan_tpk_10_persen',
                        name: 'ppn_penumpukan_tpk_10_persen'
                    }, // Sesuai dengan field di respons
                    {
                        data: 'lift_off_tpk',
                        name: 'lift_off_tpk'
                    }, // Sesuai dengan field di respons
                    {
                        data: 'lift_off_tpk_ppn',
                        name: 'lift_off_tpk_ppn'
                    }, // Sesuai dengan field di respons
                    {
                        data: 'total_hak_tpk_dan_ppn',
                        name: 'total_hak_tpk_dan_ppn'
                    } // Sesuai dengan field di respons
                ];

            } else {
                return [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_request',
                        name: 'no_request'
                    },
                    {
                        data: 'biaya_masa11',
                        name: 'biaya_masa11'
                    },
                    {
                        data: 'biaya_masa12',
                        name: 'biaya_masa12'
                    },
                    {
                        data: 'biaya_masa2',
                        name: 'biaya_masa2'
                    },
                    {
                        data: 'total_penumpukan',
                        name: 'total_penumpukan'
                    },
                    {
                        data: 'total_penumpukan_plus_ppn',
                        name: 'total_penumpukan_plus_ppn'
                    },
                    {
                        data: 'biaya_masa12_uster',
                        name: 'biaya_masa12_uster'
                    },
                    {
                        data: 'biaya_masa2_uster',
                        name: 'biaya_masa2_uster'
                    },
                    {
                        data: 'penumpukan_uster',
                        name: 'penumpukan_uster'
                    },
                    {
                        data: 'ppn_penumpukan_uster_10_persen',
                        name: 'ppn_penumpukan_uster_10_persen'
                    },
                    {
                        data: 'total_penumpukan_uster_dan_ppn',
                        name: 'total_penumpukan_uster_dan_ppn'
                    },
                    {
                        data: 'hak_tpk',
                        name: 'hak_tpk'
                    },
                    {
                        data: 'lift_on_tpk_ppn',
                        name: 'lift_on_tpk_ppn'
                    },
                    {
                        data: 'total_hak_tpk',
                        name: 'total_hak_tpk'
                    }
                ];

            }
        }

        function toExcel() {
            var tgl_awal_ = $("#tgl_awal").val();
            var tgl_akhir_ = $("#tgl_akhir").val();
            var jenis_ = $("#jenis").val();

            var url = '{!! route('uster.report.sharing_penumpukan.report') !!}?tgl_awal=' + tgl_awal_ + '&tgl_akhir=' + tgl_akhir_ + '&jenis=' + jenis_;

            window.location.href = url;
        }

        
    </script>
@endsection
