@extends('layouts.app')

@section('title')
    NBS | Add Batal Request
@endsection

@push('after-style')
    <style>
        .ui-autocomplete-loading {
            background: white url("/assets/images/animated_loading.gif");
            background-repeat: no-repeat;
            background-position: center right calc(.375em + .1875rem);
            padding-right: calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Request Batal Muat</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item"><a href="{{ route('uster.koreksi.batal_muat') }}">Batal Muat</a></li>
                <li class="breadcrumb-item active">Add Request Batal Muat</li>
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
                    <div class="card" id="data-section">
                        <form action="javascript:void" id="form-add" novalidate>
                            @csrf
                            <div class="card-body">
                                <div class="row align-items-start">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="no_ba">No. Berita Acara <small
                                                    class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="no_ba" required>
                                            <div class="invalid-feedback">Harap Masukan Nomor Berita Acara</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="no_cont">No. Container <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="no_cont" id="no_cont"
                                                required>
                                            <div class="invalid-feedback">Harap Masukan Container</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="sc">Size Container</label>
                                            <input type="text" name="sc" id="sc" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="tc">Type Container</label>
                                            <input type="text" name="tc" id="tc" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="stc">Status Container</label>
                                            <input type="text" name="stc" id="stc" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="vessel">Vessel</label>
                                            <input type="text" name="vessel" id="vessel" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="voyage_in">Voyage In</label>
                                            <input type="text" name="voyage_in" id="voyage_in" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <input type="text" name="status" id="status" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="id_req">No Req SPPS</label>
                                            <input type="text" name="id_req" id="id_req" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="id_ureq">&nbsp;</label>
                                            <input type="text" name="id_ureq" id="id_ureq" class="form-control"
                                                readonly>
                                            <input type="hidden" name="no_ukk" id="no_ukk" class="form-control"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="cont_location">Lokasi Container</label>
                                            <input type="text" name="cont_location" id="cont_location"
                                                class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row-reverse">
                                    <button type="submit" class="btn btn-info">Simpan Data</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}">
    </script>
    <script>
        var table
        $(function() {
            if ($('#data-section').length > 0 && $('.alert').css('display') == 'none') {
                $('html, body').animate({
                    scrollTop: $("#data-section").offset().top
                }, 1000);
            }

            var forms = document.querySelectorAll('form')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                    }, false)
                })

            $('#form-add').on('submit', function(e) {
                if (this.checkValidity()) {
                    e.preventDefault();
                    saveData('#form-add')
                }
                $(this).addClass('was-validated')

            });

            $('#start_date').bootstrapMaterialDatePicker({
                weekStart: 0,
                time: false
            });
            $('#end_date').bootstrapMaterialDatePicker({
                weekStart: 0,
                time: false
            });

           
        })



        $('#no_cont').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: `{{ route('uster.new_request.batal_stuffing.getContData') }}`,
                    type: 'GET',
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    success: function(data) {
                        response(data.map(function(value) {
                            return {
                                label: `${value.no_container} | \n
                            ${value.size_} | \n
                            ${value.type_} | \n
                            ${value.no_request}`,
                                no_cont: value.no_container,
                                SIZE_: value.size_,
                                TYPE_: value.type_,
                                STATUS_CONT: '',
                                STATUS: value.status,
                                VESSEL: value.vessel,
                                NO_REQUEST: value.no_request,
                                ID_UREQ: value.id_ureq,
                                NO_UKK: '',
                                LOCATION: value.location,
                                VOYAGE_IN: value.voyage_in,
                            };
                        }));
                    }
                });
            },
            select: function(event, ui) {
                $('#nc').val(ui.item.no_cont);
                $("#sc").val(ui.item.SIZE_);
                $("#tc").val(ui.item.TYPE_);
                $("#stc").val(ui.item.LOCATION);
                $("#status").val(ui.item.STATUS);
                $("#vessel").val(ui.item.VESSEL);
                $("#voyage_in").val(ui.item.VOYAGE_IN);
                $("#id_req").val(ui.item.NO_REQUEST);
                $("#id_ureq").val(ui.item.ID_UREQ);
                $("#no_ukk").val(ui.item.NO_UKK);
                $("#cont_location").val(ui.item.LOCATION);
                return false;
            }
        })

        function saveData(formId) {
            const form = $(formId).serialize()
            const url = "{{ route('uster.new_request.batal_stuffing.store') }}"
            ajaxPostJson(url, form, 'input_success')
        }

        function input_success(res) {
            if (res.status != 200) {
                input_error(res)
                return false
            }

            $.toast({
                heading: 'Berhasil!',
                text: res.message,
                position: 'top-right',
                icon: 'success',
                hideAfter: 2500,
                beforeHide: function() {
                    if (res.redirect.need) {
                        Swal.fire({
                            html: "<h5>Berhasil input Request Batal SPPS,<br> Mengembalikan Anda ke halaman sebelumnya...</h5>",
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });

                        Swal.showLoading();
                    } else {
                        return false;
                    }
                },
                afterHidden: function() {
                    if (res.redirect.need) {
                        window.location.href = res.redirect.to
                    } else {
                        return false;
                    }
                },
            });
        }

        function input_error(err) {
            console.log(err)
            $.toast({
                heading: 'Gagal memproses data!',
                text: err.message,
                position: 'top-right',
                icon: 'error',
                hideAfter: 5000,
            });
        }

        function get_error(err) {
            console.log(err)
            $.toast({
                heading: 'Gagal mengambil data!',
                text: err.message,
                position: 'top-right',
                icon: 'error',
                hideAfter: 5000,
            });
        }
    </script>
@endpush
