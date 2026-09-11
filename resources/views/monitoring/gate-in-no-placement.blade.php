@extends('layouts.app')

@section('title')
    Gate In No Placement
@endsection

@section('pages-css')
<style>
    #tbl-noplacement thead tr th {
        background-color: #1a6ab1 !important;
        color: #fff !important;
        border-color: #155a9a !important;
        font-size: 14px;
        white-space: nowrap;
        vertical-align: middle;
    }
    #tbl-noplacement tbody tr td {
        font-size: 14px;
        vertical-align: middle;
    }
    #tbl-noplacement tbody tr:hover {
        background-color: #eef4fc !important;
    }
</style>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Monitoring</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Monitoring</li>
            <li class="breadcrumb-item active">Gate In No Placement</li>
        </ol>
    </div>
    <div class="col-md-7 col-4 align-self-center">
        <div class="d-flex m-t-10 justify-content-end">
            <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p></h6>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                {{-- Header --}}
                <div class="mb-3">
                    <h4 class="card-title mb-1" style="color:#1a6ab1; font-weight:700;">
                        <i class="mdi mdi-truck-delivery mr-1"></i> Gate In No Placement
                    </h4>
                </div>

                <hr>

                {{-- Toolbar --}}
                <div class="mb-2">
                    <button class="btn btn-sm btn-primary" id="btn-refresh" onclick="refreshTable()">
                        <i class="mdi mdi-refresh"></i> Refresh
                    </button>
                    <a hidden href="{{ route('uster.monitoring.gatein_noplacement.export') }}"
                       class="btn btn-sm btn-success ml-1" target="_blank">
                        <i class="mdi mdi-file-excel"></i> Export CSV
                    </a>
                </div>

                {{-- DataTable --}}
                <div class="table-responsive">
                    <table id="tbl-noplacement"
                           class="table table-bordered table-striped table-hover"
                           style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:40px">No</th>
                                <th>No Container</th>
                                <th>No Request</th>
                                <th class="text-center">Status</th>
                                <th>Trucking</th>
                                <th class="text-center">Tgl Gate In</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('pages-js')
<script>
var table;

$(document).ready(function () {
    loadTable();
});

function loadTable() {
    if ($.fn.DataTable.isDataTable('#tbl-noplacement')) {
        $('#tbl-noplacement').DataTable().destroy();
    }

    table = $('#tbl-noplacement').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{{ route("uster.monitoring.gatein_noplacement.data") }}',
            type: 'GET',
            error: function () {
                alert('Gagal mengambil data dari USTER database.');
                $('#badge-total').removeClass('badge-warning').addClass('badge-danger')
                    .html('<i class="mdi mdi-alert-circle"></i> Error');
            }
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'no_container', name: 'no_container', defaultContent: '-' },
            { data: 'no_request',   name: 'no_request',   defaultContent: '-' },
            {
                data: 'status',
                name: 'status',
                className: 'text-center',
                defaultContent: '-',
                render: function (data) {
                    if (!data) return '<span class="text-muted">-</span>';
                    return '<span class="badge badge-info" style="font-size:11px">' + data + '</span>';
                }
            },
            { data: 'trucking',   name: 'trucking',   defaultContent: '-' },
            { data: 'tgl_in',     name: 'tgl_in',     defaultContent: '-', className: 'text-center' },
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        language: {
            processing: '<i class="mdi mdi-loading mdi-spin text-primary"></i> Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ s/d _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang sesuai',
            paginate: { first: '««', last: '»»', next: '›', previous: '‹' }
        }
    });
}

function refreshTable() {
    var btn = $('#btn-refresh');
    btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Loading...');
    table.ajax.reload(function () {
        btn.prop('disabled', false).html('<i class="mdi mdi-refresh"></i> Refresh');
    }, false);
}
</script>
@endsection
