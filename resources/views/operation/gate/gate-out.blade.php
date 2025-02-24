@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
@endsection

@section('pages-css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Operation</a></li>
                <li class="breadcrumb-item">Gate</li>
                <li class="breadcrumb-item"><a href="{{ route('uster.operation.gate.gate_out') }}">Gate Out</a>
                </li>
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
                    <h3><b>Gate Out Delivery - SP2</b></h3>

                    <form action="javascript:void" id="requestGateOut" novalidate>
                        @csrf


                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Container No : </label>
                                </div>
                                <div class="col-md-4">
                                    <select id="CONT_NO" name="CONT_NO" class="form-control"> </select>
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No TRUCK : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="NO_TRUCK" name="NO_TRUCK" class="form-control" type="text">

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No Request Delivery : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="REQ_DEV" name="REQ_DEV" class="form-control" type="text" readonly>
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">NO_SEAL : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="NO_SEAL" name="NO_SEAL" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Ukuran/ Type / Status : </label>
                                </div>
                                <div class="col">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <input id="SIZE" name="SIZE" class="form-control" type="text"
                                                readonly>
                                        </div>

                                        <div class="col-md-4">
                                            <input id="TYPE" name="TYPE" class="form-control" type="text"
                                                readonly>
                                        </div>

                                        <div class="col-md-4">
                                            <input id="STATUS" name="STATUS" class="form-control" type="text"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">KETERANGAN : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="KETERANGAN" name="KETERANGAN" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">Penerima /Consignee: </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="NM_PBM" name="NM_PBM" class="form-control" type="text" readonly>
                                </div>
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">No Nota : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="NO_NOTA" name="NO_NOTA" class="form-control" type="text" readonly>
                                </div>

                            </div>
                            <div class="row">

                            </div>
                            <div class="row">
                                <div class="col-md-2 py-3">
                                    <label for="tb-fname">>Masa Berlaku : </label>
                                </div>
                                <div class="col-md-10">
                                    <input id="MASA_BERLAKU" name="MASA_BERLAKU" class="form-control" type="date"
                                        readonly>
                                </div>
                            </div>

                        </div>
                        <button type="submit" class="btn btn-info">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush


@section('pages-js')
    <script>
        $(document).ready(function() {
            $('#CONT_NO').select2({
                minimumInputLength: 3, // Set the minimum input length
                ajax: {
                    // Implement your AJAX settings for data retrieval here
                    url: '{!! route('uster.operation.gate.gate_out.getContainer') !!}',
                    dataType: 'json',
                    processResults: function(data) {
                        const dataArray = Array.isArray(data) ? data : [data];

                        return {
                            results: dataArray.map((item) => ({
                                id: item.no_container,
                                text: item.no_container,
                                ...item
                            }))
                        };
                    }
                }
            });

            $('#CONT_NO').on('select2:select', function(e) {
                var data = e.params.data;
                $("#CONT_NO").val(data.no_container);
                $("#REQ_DEV").val(data.no_request);
                $("#NM_PBM").val(data.nm_pbm);
                $("#SIZE").val(data.size_);
                $("#TYPE").val(data.type_);
                $("#STATUS").val(data.status);
                $("#NO_NOTA").val(data.no_nota);
                // Mengubah format tanggal
                var tanggal = data.tgl_request_delivery.split(" ")[0];

                // Mengatur nilai input tanggal
                $("#MASA_BERLAKU").val(tanggal);
                return false;
            })
            $('#CONT_NO').on('select2:clear', function(e) {
                $form[0].reset()
            })
        });

        $('#requestGateOut').on('submit', function(e) {
            if (this.checkValidity()) {
                e.preventDefault();
                saveEditData()
                console.log('34');
            }
            $(this).addClass('was-validated');
            console.log('3sad4');
        });

        function saveEditData() {
            var form = $('#requestGateOut').serialize()
            ajaxPostJson('{{ route('uster.operation.gate.Storegate_in') }}', form, 'input_success')
        }
    </script>
@endsection
