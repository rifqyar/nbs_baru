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
                    <h3><b>Detail Master Tarif {{$kategori_->kategori_tarif}}</b></h3>


                    <div class="table-responsive">
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="service-table">
                            <thead>

                                <tr>
                                    <td>No </td>
                                    <td>ISO</td>
                                    <td>KATEGORI TARIF/td>
                                    <td>TYPE</td>
                                    <td>STATUS</td>
                                    <td>TARIF</td>
                                    <td>ACTION</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($row_list as $item)
                                    <tr>
                                        <td>{{ $loop->index + 1 }} </td>
                                        <td>
                                            <b>{{ $item->id_iso }}</b>
                                        </td>
                                        <td>{{ $item->kategori_tarif }}</td>
                                        <td>{{ $item->type_ }}</td>
                                        <td>{{ $item->status }}</td>
                                        <td>{{ $item->tarif }}</td>
                                        <td>
                                            <a href="{{ route('uster.maintenance.master_tarif.detail') }}?id_group_tarif={{  $id_group_tarif }}&id_iso={{ $item->id_iso }}"
                                                class="btn btn-primary">
                                                <i class="fas fa-info-circle"></i> Detail
                                            </a>
                                        </td>
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
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
            });
        });
    </script>
@endpush
