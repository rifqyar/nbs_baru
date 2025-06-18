@extends('layouts.app')

@section('title')
Export Copy yard
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
            <li class="breadcrumb-item">Container</a></li>
            <li class="breadcrumb-item active">Export Copy yard</li>
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
                <h3><b>Export Copy yard</b></h3>
                <div class="border rounded my-3">
                    <form id="export_copy_yard_form">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Upload: </label>
                                </div>
                                <div class="col-md-4">
                                    <input class="form-control" type="file" name="excel" id="excel">
                                </div>
                            </div>
                        </div>
                    </form>
                    <button onclick="readExcel()" class="btn btn-danger mr-2"><i class="fas fa-file-alt pr-2"></i> Upload</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('pages-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    function readExcel() {
        var formData = new FormData($("#export_copy_yard_form")[0]);
        $.ajax({
            url: '{!! route("uster.report.exportcopyyard.readexcel") !!}',
            type: 'POST',
            data: formData,
            dataType: "JSON",
            processData: false,
            contentType: false,
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(data) {
                Swal.close();
                if (data.status['code'] == 200) {

                    sAlert('Berhasil', 'Data Telah Diproses', 'success');

                } else {
                    sAlert('Gagal!', data.status['msg'], 'danger');
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
            },
            complete: function() {

            }
        });
    }
</script>
@endsection