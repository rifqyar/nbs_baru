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
                    <h3><b>Detail Komponen Nota {{$nama_nota}}</b></h3>
                    <div class="table-responsive">
                        <table class="grid-table" border='0' cellpadding="1" cellspacing="1" width="70%">
                            <tr >
                                <td class="form-field-caption" valign="top" align="right">ID Komponen Nota</td>
                                <td class="form-field-caption" valign="top" align="center">:</td>
                                <td class="form-field-caption" valign="top" align="left">
                                    <input class="form-control" type="input" name="id_komp"
                                        value="{{ $row_list->id_komp_nota }}" readonly="readonly" />
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption" valign="top" align="right">Komponen Nota</td>
                                <td class="form-field-caption" valign="top" align="center">:</td>
                                <td class="form-field-caption" valign="top" align="left">
                                    <input class="form-control" type="input" name="id_komp"
                                        value="{{ $row_list->komponen_nota }}" readonly="readonly" />
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption" valign="top" align="right">Status Aktif</td>
                                <td class="form-field-caption" valign="top" align="center">:</td>
                                <td class="form-field-caption" valign="top" align="left">
                                    <input class="form-control" type="input" name="id_komp"
                                        value="{{ $row_list->status }}" readonly="readonly" />
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption" valign="top" align="right">Edit Status</td>
                                <td class="form-field-caption" valign="top" align="center">:</td>
                                <td class="form-field-caption" valign="top" align="left">
                                    <select id="status" class="form-control">
                                        <option value=""> -- Pilih -- </option>
                                        <option value="AKTIF"> AKTIF </option>
                                        <option value="NON AKTIF"> NON AKTIF </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="5" class="form-field-caption" valign="top" align="center"> &nbsp;&nbsp;
                                    <a class="btn btn-primary" style="margin-top:50px" onclick="edit('{{ $row_list->id_nota }}','{{ $row_list->id_komp_nota }}')">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
@endpush
