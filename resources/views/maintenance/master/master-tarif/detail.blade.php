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



                    <div class="table-responsive">
                        <form enctype="multipart/form-data" action="" method="post">
                            <table class="form-input" style="margin: 30px 30px 30px 30px;">
                                <tr>
                                    <td class="form-field-caption"> ID ISO </td>
                                    <td> : </td>
                                    <td> <input class="form-control" type="text" name="id_iso" id="id_iso"
                                            value="{{ $row->id_iso }}" />
                                        <input class="form-control" type="hidden" name="id_group_tarif" id="id_group_tarif"
                                            value="{{ $row->id_group_tarif }}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="form-field-caption"> Kategori Tarif</td>
                                    <td> : </td>
                                    <td> <input class="form-control" type="text" name="kategori_tarif"
                                            id="kategori_tarif" value="{{ $row->kategori_tarif }}" />
                                    </td>
                                </tr>
                                <tr>
                                    <td class="form-field-caption"> Tarif </td>
                                    <td> : </td>
                                    <td> <input class="form-control" type="text" name="tarif" id="tarif"
                                            value="{{ $row->tarif }}" />
                                    </td>
                                </tr>
                                <tr align="center">
                                    <td colspan="7"><input class="btn btn-primary" type="submit" value=" Simpan " />
                                    </td>
                                </tr>
                            </table>
                        </form>
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
