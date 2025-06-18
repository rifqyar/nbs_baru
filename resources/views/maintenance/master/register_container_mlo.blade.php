@extends('layouts.app')

@section('title')
    Master Tarif
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Maintenance</a></li>
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active">Master Tarif</li>
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
                    <h3><b>Master Tarif</b></h3>


                    <div class="table-responsive">
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="service-table">
                            <thead>
                                <tr>
                                    <th>No </th>
                                    <th>No Container</th>
                                    <th>SIZE / TYPE</th>
                                    <th>ACTION</th>
                                </tr>
                                <fill src="komp_nota" var="rows">
                                    @foreach ($containers as $container)
                                        <tr>
                                            <td>{{ $loop->index+1 }} </td>
                                            <td>
                                                <b>{{ $container->no_container }}</b>
                                            </td>
                                            <td>{{ $container->size_ }}  /  {{$container->type_}}</td>
                                            <td>
                                                <a href="/asd" class="btn btn-primary">
                                                    <i class="fas fa-registered"></i> Register
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
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
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
            });
        });
    </script>
@endpush
