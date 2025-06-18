@extends('layouts.app')

@section('title')
    Batal Container Receiving MTY
@endsection

@section('pages-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Koreksi</a></li>
                <li class="breadcrumb-item active">Batal Container Receiving MTY</li>
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
                    <h3><b>Batal Container Receiving MTY</b></h3>
                    <div class="border rounded my-3">
                        <form id="dataCont">
                            @csrf
                            <div class="p-3">
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">No Container: </label>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="NO_CONT" id="NO_CONT" class="form-control">
                                            <!-- <option value=""></option> -->
                                            <!-- <input type="text" name="NO_CONT" id="NO_CONT" class="form-control"> -->
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">No Request Receiving : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="NO_REQ" id="NO_REQ" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Tgl Request Receiving : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="TGL_REQ" id="TGL_REQ" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Size : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="SIZE" id="SIZE" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Type : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="Type" id="Type" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Receiving Dari : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="RECEIVING_DARI" name="RECEIVING_DARI" class="form-control"
                                            readonly>
                                    </div>
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Via : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="VIA" name="VIA" class="form-control" readonly>
                                    </div>
                                </div>
                                <!-- <div class="row">
                                    <div class="col-md-2 py-2">
                                        <label for="tb-fname">Keterangan : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" id="keterangan" name="keterangan" class="form-control">
                                    </div>
                                </div> -->

                            </div>
                        </form>
                        <div class="row text-center mb-3">
                            <div class="col">
                                <button onclick="batal()" class="btn btn-info">Batal</button>
                            </div>
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
        $(document).ready(function() {



            $('#NO_CONT').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.koreksi.batalreceiving.getnocontainer') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const arrs = data;
                        return {
                            results: arrs.map((arr, i) => ({
                                id: arr.no_container,
                                text: arr.no_container + ' | ' + arr.size_ + ' | ' + arr
                                    .type_,
                                ...arr
                            }))
                        };
                    }
                }
            });

            $('#NO_CONT').on('select2:select', function(e) {
                var data = e.params.data;
                $("#NO_CONT").val(data.no_container);
                $("#SIZE").val(data.size_);
                $("#TYPE").val(data.type_);
                $("#NO_REQ").val(data.no_request);
                $("#TGL_REQ").val(data.tgl_request);
                $("#RECEIVING_DARI").val(data.receiving_dari);
                $("#LUNAS").val(data.lunas);
            })
        });


        ///==================================================///


        function batal() {
            var no_cont_ = $("#NO_CONT").val();
            var no_req_ = $("#NO_REQ").val();
            var csrfToken = '{{ csrf_token() }}';

            $.ajax({
                url: '{!! route('uster.koreksi.batalreceiving.batalreceivingcont') !!}',
                type: 'POST',
                data: {
                    _token: csrfToken,
                    NO_CONT: no_cont_,
                    NO_REQ: no_req_,
                },
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
                        sAlert('Berhasil!', data.status['msg'], 'success');
                    } else {
                        sAlert('Gagal!', data.status['msg'], 'error');
                    }

                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
                },
                complete: function() {
                    $("#NO_CONT").val('');
                    $("#SIZE").val('');
                    $("#TYPE").val('');
                    $("#NO_REQ").val('');
                    $("#TGL_REQ").val('');
                    $("#LUNAS").val('');

                    $("#NO_CONT").focus();
                }
            });
        }
    </script>
@endsection
