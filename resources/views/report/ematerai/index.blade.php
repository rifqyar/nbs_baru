@extends('layouts.app')

@section('title')
    Master Container
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Maintenance</a></li>
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active">Master Container</li>
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
                    <h3><b>Master Container</b></h3>
                    <form method="GET" action="{{route('uster.report.report_materai')}}" class="mb-4">
                        <div class="form-row">
                            <div class="col">
                                <input type="text" name="id_req" class="form-control" placeholder="Search by NO_PERATURAN" value="{{ request()->id_req }}">
                            </div>
                            <div class="col">
                                <input type="date" name="id_time" class="form-control" placeholder="Search by Start Date" value="{{ request()->id_time }}">
                            </div>
                            <div class="col">
                                <input type="date" name="id_time2" class="form-control" placeholder="Search by End Date" value="{{ request()->id_time2 }}">
                            </div>
                            <div class="col">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="mb-3">
                        <button id="export-button" class="btn btn-success">Export</button>
                    </div>
            
                    <!-- Hidden form to handle export -->
                    <form id="export-form" method="GET" action="{{ route('uster.report.report_materai.report') }}" target="_blank">
                        <input type="hidden" name="id_req" value="{{ request()->id_req }}">
                        <input type="hidden" name="id_time" value="{{ request()->id_time }}">
                        <input type="hidden" name="id_time2" value="{{ request()->id_time2 }}">
                    </form>

                    <div class="table-responsive">
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="service-table">
                            <thead>
                                <tr>
                                    <th>NO PERATURAN</th>
                                    <th>NOMINAL</th>
                                    <th>TGL PERATURAN</th>
                                    <th>TERPAKAI</th>
                                    <th>SALDO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($results as $row)
                                <tr>
                                    <td>{{ $row->no_peraturan }}</td>
                                    <td>{{ number_format($row->nominal) }}</td>
                                    <td>{{ $row->tgl_peraturan }}</td>
                                    <td>{{ number_format($row->terpakai) }}</td>
                                    <td>{{ number_format($row->saldo) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

   

    
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('after-script')
    <script>
        $(document).ready(function() {
            $('#export-button').click(function() {
                // Update hidden form values
                $('#export-form input[name="id_req"]').val($('input[name="id_req"]').val());
                $('#export-form input[name="id_time"]').val($('input[name="id_time"]').val());
                $('#export-form input[name="id_time2"]').val($('input[name="id_time2"]').val());
                
                // Submit the hidden form
                $('#export-form').submit();
            });
        });   
    </script>
@endpush
