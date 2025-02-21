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
                    <h3><b>Rename Container</b></h3>

                    <form action="javascript:void" id="requestGateIn" novalidate>
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Search</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 row">
                                    <label class="col-3 col-form-label required">Container No</label>
                                    <div class="col">
                                        <select class="form-control" id="noContainer">
                                            <option></option>
                                        </select>

                                    </div>

                                </div>

                                <div class="mb-3 row">
                                    <label class="col-3 col-form-label required">Size - Type - Owner</label>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">
                                                <input name="CONTAINER_SIZE" type="text" class="form-control"
                                                    disabled="">
                                            </div>
                                            <div class="col">
                                                <input name="CONTAINER_TYPE" type="text" class="form-control"
                                                    disabled="">
                                            </div>
                                            <div class="col">
                                                <input name="OWNER_NAME" type="text" class="form-control" disabled="">
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="mb-3 row">
                                    <label class="col-3 col-form-label required">No Request - No Nota</label>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col">
                                                <input name="NO_CONT" type="hidden" class="form-control" id="NO_CONT" >
                                                <input name="NO_REQ" type="text" class="form-control" id="NO_REQ" readonly="readonly">
                                            </div>
                                            <div class="col">
                                                <input name="NO_NOTA" type="text" class="form-control" id="NO_NOTA" readonly>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                        <br>


                        <button type="submit" class="btn btn-info" onclick="save()">Simpan Data</button>
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
        const $noContainer = $('#noContainer')

        function save() {

            if ($("#NO_CONT").val() == '') {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'No.Container Lama / Baru Harus Diisi',
                });
                return false;
            } else {
                var NO_CONT = $("#NO_CONT").val();
                var NO_REQ = $("#NO_REQ").val();

                Swal.fire({
                    title: 'Konfirmasi',
                    text: "Yakin untuk menyimpan ini? Pastikan Inputan Anda Sudah Benar",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.preventDefault();
                        var url = "{{ route('uster.maintenance.disable_container.store') }}";
                        Swal.fire({
                            title: 'Merubah Data...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.post(url, {
                            NO_CONT: NO_CONT,
                            NO_REQ: NO_REQ,
                        }, function(data) {
                            Swal.close();
                            if (data == "OK") {
                                resetValues();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Proses',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                            
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    showConfirmButton: false,
                                    timer: 1000
                                });
                            }

                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'You pressed Cancel!',
                        });
                    }
                });

            }
        }

        function resetValues() {
            $('input[name="CONTAINER_SIZE"]').val("");
            $('input[name="NO_CONTAINER_OLD"]').val("");
            $('input[name="NO_CONTAINER_NEW"]').val("");
            $('input[name="CONTAINER_TYPE"]').val("");
            $('input[name="OWNER_NAME"]').val("");


            $('#noContainer').empty();


            $("input[name='CONTAINER_NO']").prop('disabled', true);

            // Enable the first select dropdown with id "TypeSelectSize"
            $("#TypeSelect").prop('disabled', true);

            // Enable the second select dropdown with id "TypeSelectType"
            $("#SizeSelect").prop('disabled', true);
        }

        function onChoose(container) {
            $('input[name="CONTAINER_SIZE"]').val(container.size_)
            $('input[name="NO_CONTAINER_OLD"]').val(container.no_container)
            $('input[name="NO_CONTAINER_NEW"]').val(container.no_container)
            $('input[name="CONTAINER_TYPE"]').val(container.type_)
            $('input[name="STATUS"]').val(container.status)
            $('input[name="NO_REQ"]').val(container.no_request)
            $('input[name="NO_NOTA"]').val(container.no_nota)
            $('input[name="NO_CONT"]').val(container.no_container)

            $("input[name='CONTAINER_NO']").prop('disabled', false);

            // Enable the first select dropdown with id "TypeSelectSize"
            $("#TypeSelect").prop('disabled', false);

            // Enable the second select dropdown with id "TypeSelectType"
            $("#SizeSelect").prop('disabled', false);
        }

        function initNoContainer() {

            $noContainer.select2({
                ajax: {
                    delay: 250,
                    url: "{{ route('uster.maintenance.disable_container.getcontainer') }}",
                    data: function(params) {
                        var query = {
                            search: params.term,
                        }
                        // Query parameters will be ?search=[term]&type=public
                        return query;
                    },
                    processResults: function(data) {
                        datas = data;
                        // Transforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: datas.map((arr, i) => ({
                                id: arr.no_container,
                                text: arr.no_container,
                                ...arr
                            }))
                        };
                    }
                }
            })

            $('#secondNoContainer').select2({
                width: '100%',
                placeholder: 'Container no (Autocomplete)',
            })

            $noContainer.on('select2:select', function(e) {
                var data = e.params.data;
                onChoose(data)
            })

            $noContainer.on('select2:clear', function(e) {
                $form[0].reset()
            })
        }

        function getNoContainer() {
            Swal.fire({
                title: 'Mendapatkan Data...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('uster.maintenance.disable_container.getcontainer') }}",
            }).then((res) => {
                containers = res;
                initNoContainer();
                Swal.close();
            }).fail((jqXHR, textStatus, errorThrown) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengambil Data',
                    text: 'Terjadi kesalahan saat mengambil data. Silakan coba lagi nanti.',
                });
            });
        }





        (function() {


            getNoContainer()
        })()
    </script>
@endsection
