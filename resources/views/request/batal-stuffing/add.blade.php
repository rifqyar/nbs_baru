@extends('layouts.app')

@section('title')
    Batal Container Stuffing
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Koreksi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('uster.new_request.batal_stuffing') }}">Batal Container Stuffing</a></li>
                
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
                        <form action="javascript:void(0)" id="form-add" novalidate>
                            @csrf
                            <div class="card-body">
                                <fieldset class="border p-4 mb-4">
                                    <legend class="w-auto px-2" style="font-size:16px; font-weight:bold; color:#999;">
                                        <i class="fas fa-file-alt mr-2"></i>Batal Container Stuffing
                                    </legend>

                                    <!-- Hidden Inputs dari Script Lama -->
                                    <input type="hidden" id="ASAL_CONT" name="asal_cont">
                                    <input type="hidden" id="STUFFING_DARI" name="stuffing_mode">
                                    <input type="hidden" id="NO_REQ_DEL" name="no_req_del">
                                    <input type="hidden" id="NO_REQ_ICT" name="no_req_ict">
                                    <input type="hidden" id="KD_KOMODITI" name="kd_komoditi">
                                    <input type="hidden" id="NO_BOOKING" name="no_booking">
                                    <input type="hidden" id="NO_UKK" name="no_ukk">
                                    <input type="hidden" id="NO_SEAL" name="no_seal">
                                    <input type="hidden" id="BERAT" name="berat">
                                    <input type="hidden" id="KETERANGAN" name="keterangan">

                                    <div class="row align-items-center mb-4">
                                        <div class="col-md-3">
                                            <h5 class="mb-0 font-weight-bold">No. Container <small class="text-danger">*</small></h5>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" name="no_cont" id="NO_CONT" required 
                                                placeholder="Ketik Nomor Container..." 
                                                style="font-size:20px; font-weight:bold; text-transform:uppercase" tabindex="1">
                                            <div class="invalid-feedback">Harap Masukan Nomor Container</div>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row mt-4">
                                        <div class="col-12 col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Tgl. Mulai</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="TGL_MULAI" id="TGL_MULAI" class="form-control" placeholder="Tgl Mulai (Opsional)">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Size</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="SIZE" id="SIZE" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Komoditi</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="KOMODITI" id="KOMODITI" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Jenis Stuffing</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="TYPE" id="TYPE" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Tgl. Request Stuffing</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="TGL_REQUEST" id="TGL_REQUEST" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label"></label>
                                                <div class="col-sm-8">
                                                    <!-- Spacing -->
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Via</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="VIA" id="VIA" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">HZ</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="HZ" id="HZ" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">No. Request Stuffing</label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="id_req" id="NO_REQ_STUFF" class="form-control" readonly placeholder="Auto Fill">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <div class="d-flex flex-row-reverse mt-3">
                                    <button type="submit" class="btn btn-info"><i class="fas fa-save mr-2"></i>Simpan Batal Stuffing</button>
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

            $('#TGL_MULAI').bootstrapMaterialDatePicker({
                weekStart: 0,
                time: false,
                format: 'YYYY-MM-DD'
            });

            $('#NO_CONT').autocomplete({
                minLength: 3,
                source: function(request, response) {
                    $.ajax({
                        url: `{{ url('request/batal-stuffing/data-container') }}`,
                        type: 'GET',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function(data) {
                            response(data.map(function(value) {
                                // Menyesuaikan map property dari object yang di-return oleh BatalStuffingService->getContainer
                                return {
                                    label: value.no_container + " | " + value.size_ + " " + value.type_,
                                    NO_CONTAINER: value.no_container,
                                    ASAL_CONT: value.asal_cont,
                                    NO_REQ_STUFF: value.no_req_stuff,
                                    NO_REQ_DEL: value.no_req_del || '', // Antisipasi jika query tidak me-return ini
                                    NO_REQ_ICT: value.no_req_ict || '', // Antisipasi jika query tidak me-return ini
                                    VIA: value.via,
                                    KOMODITI: value.komoditi,
                                    KD_KOMODITI: value.kd_komoditi || '',
                                    HZ: value.hz,
                                    SIZE_: value.size_,
                                    TYPE_: value.type_,
                                    TGL_REQUEST: value.tgl_request,
                                    STUFFING_DARI: value.stuffing_dari,
                                    NO_BOOKING: value.no_booking || '',
                                    NO_UKK: value.no_ukk || '',
                                    NO_SEAL: value.no_seal,
                                    BERAT: value.berat,
                                    KETERANGAN: value.keterangan
                                };
                            }));
                        }
                    });
                },
                select: function(event, ui) {
                    $("#NO_CONT").val(ui.item.NO_CONTAINER);
                    $("#ASAL_CONT").val(ui.item.ASAL_CONT);
                    $("#STUFFING_DARI").val(ui.item.STUFFING_DARI);
                    $("#NO_REQ_STUFF").val(ui.item.NO_REQ_STUFF); 
                    $("#NO_REQ_DEL").val(ui.item.NO_REQ_DEL);
                    $("#NO_REQ_ICT").val(ui.item.NO_REQ_ICT); 
                    $("#VIA").val(ui.item.VIA);
                    $("#KOMODITI").val(ui.item.KOMODITI);
                    $("#KD_KOMODITI").val(ui.item.KD_KOMODITI); 
                    $("#HZ").val(ui.item.HZ);
                    $("#SIZE").val(ui.item.SIZE_);
                    $("#TYPE").val(ui.item.TYPE_);			
                    $("#TGL_REQUEST").val(ui.item.TGL_REQUEST);
                    $("#NO_BOOKING").val(ui.item.NO_BOOKING);
                    $("#NO_UKK").val(ui.item.NO_UKK);
                    $("#NO_SEAL").val(ui.item.NO_SEAL);
                    $("#BERAT").val(ui.item.BERAT);
                    $("#KETERANGAN").val(ui.item.KETERANGAN);
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li style='border-bottom: 1px solid #f3f3f3; padding: 6px 10px; cursor: pointer; white-space: nowrap;'></li>")
                    .data("item.autocomplete", item)
                    .append("<div style='font-size: 14px; font-weight: 500; color: #333;'>" + item.NO_CONTAINER + " <span style='color: #888;'>|</span> " + item.SIZE_ + " " + item.TYPE_ + "</div>")
                    .appendTo(ul);
            };
        })

        function saveData(formId) {
            const form = $(formId).serialize()
            const url = "{{ url('request/batal-stuffing/store') }}"
            ajaxPostJson(url, form, 'input_success')
        }

        function input_success(res) {
            if (res.status != 200) {
                input_error(res)
                return false
            }

            // Kosongkan form seketika
            $('#form-add')[0].reset();
            // Kosongkan nilai di hidden input juga yang mungkin tersisa
            $('#form-add input[type="hidden"]').val('');

            $.toast({
                heading: 'Berhasil!',
                text: res.message || 'Data Berhasil Disimpan',
                position: 'top-right',
                icon: 'success',
                hideAfter: 2500
            });
        }

        function input_error(err) {
            console.log(err)
            $.toast({
                heading: 'Gagal memproses data!',
                text: err.message || err.responseJSON?.message || 'Something Went Wrong',
                position: 'top-right',
                icon: 'error',
                hideAfter: 5000,
            });
        }
    </script>
@endpush
