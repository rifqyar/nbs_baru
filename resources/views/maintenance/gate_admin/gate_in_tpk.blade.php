@extends('layouts.app')

@section('title')
    Gate Administrator (Gate In Container dari TPK)
@endsection

@push('after-style')
    <link href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css') }}"
        rel="stylesheet">
    <style>
        .ui-autocomplete-loading {
            background: white url("/assets/images/animated_loading.gif") right center no-repeat;
            background-repeat: no-repeat;
            background-position: center right calc(.375em + .1875rem);
            padding-right: calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    @if (Session::has('notifCekPaid'))
        <div class="alert alert-danger fade show" role="alert">
            <h4 class="mb-0 text-center">
                <span class="text-size-5">
                    {!! Session::get('notifCekPaid') !!}
                </span>
                <i class="fas fa-exclamation-circle"></i>
            </h4>
        </div>
    @endif

    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Gate Administrator (Gate In Container dari TPK)</h3>
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
                    <div class="card card-secondary">
                        <div class="card-body">
                            <div class="card-title">
                                <h4>Form Pencarian</h4>
                            </div>
                            <form action="javascript:void(0)" id="form-data" class="m-t-40">
                                @csrf
                                <input type="hidden" name="KD_TRUCK" id="KD_TRUCK" />
                                <input type="hidden" name="ID_YARD" id="ID_YARD" />
                                <input type="hidden" name="BP_ID" id="BP_ID" />
                                <input type="hidden" name="NO_REQ_TPK" id="NO_REQ_TPK" />
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="CONT_NO" class="form-label">No. Container</label>
                                        <input type="text" class="form-control" id="CONT_NO" name="CONT_NO" required
                                            style="text-transform:uppercase">
                                        <div class="invalid-feedback">
                                            No. Container wajib diisi.
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="NO_TRUCK" class="form-label">No. Truck</label>
                                        <input type="text" class="form-control" id="NO_TRUCK" name="NO_TRUCK"
                                            style="text-transform:uppercase" required>
                                        <div class="invalid-feedback">
                                            No. Truck wajib diisi.
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="REQ_REC" class="form-label">No. Request Receiving</label>
                                        <input type="text" class="form-control" id="REQ_REC" name="REQ_REC" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="NO_SEAL" class="form-label">No. Seal</label>
                                        <input type="text" class="form-control" id="NO_SEAL" name="NO_SEAL">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="" class="form-label">Ukuran/ Type / Status</label>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" id="SIZE" name="SIZE"
                                                    readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" id="TYPE" name="TYPE"
                                                    readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" id="STATUS" name="STATUS"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="KETERANGAN" class="form-label">Keterangan</label>
                                        <input type="text" class="form-control" id="KETERANGAN" name="KETERANGAN">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="NM_PBM" class="form-label">Penerima / Consignee</label>
                                        <input type="text" class="form-control" id="NM_PBM" name="NM_PBM"
                                            readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tgl_gati" class="form-label">Tgl. Gate In</label>
                                        <input type="text" class="form-control" id="tgl_gati" name="tgl_gati"
                                            required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pages-js')
    <script>
        var table, no_npwp;
        var cachePBM = {};
        $("#tgl_gati").bootstrapMaterialDatePicker({
            weekStart: 0,
            time: false
        });

        $(document).bind("keydown", function(e) {
            if (e.keyCode == 113) gateIn();
            return true;
        });

        $("#CONT_NO").autocomplete({
            minLength: 3,
            source: function(request, response) {
                $.ajax({
                    url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}maintenance/gate_admin/gate-in-tpk/data-cont`,
                    type: "POST",
                    dataType: "json",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        search: request.term,
                    },
                    error: function(err) {
                        get_error(err.responseJSON);
                    },
                    success: function(resp) {
                        const data = resp.data || [];
                        if (data.length > 0) {
                            response(
                                data.map(function(value) {
                                    console.log(value);
                                    return {
                                        label: `${value.NO_CONTAINER}`,
                                        NO_CONTAINER: value.NO_CONTAINER,
                                        NO_REQUEST: value.NO_REQUEST,
                                        NM_PBM: value.NM_PBM,
                                        SIZE_: value.SIZE_,
                                        TYPE_: value.TYPE_,
                                        STATUS: value.STATUS,
                                        ID_YARD: value.ID_YARD,
                                        BP_ID: value.BP_ID,
                                        NO_REQ_TPK: value.NO_REQ_TPK,
                                        NOPOL: value.NOPOL,
                                    };
                                })
                            );
                        }

                        $("#CONT_NO").removeClass("ui-autocomplete-loading");
                    },
                });
            },
            select: function(event, ui) {
                $("#CONT_NO").val(ui.item.NO_CONTAINER);
                $("#REQ_REC").val(ui.item.NO_REQUEST);
                $("#NM_PBM").val(ui.item.NM_PBM);
                $("#SIZE").val(ui.item.SIZE_);
                $("#TYPE").val(ui.item.TYPE_);
                $("#STATUS").val(ui.item.STATUS);
                $("#ID_YARD").val(ui.item.ID_YARD);
                $("#BP_ID").val(ui.item.BP_ID);
                $("#NO_REQ_TPK").val(ui.item.NO_REQ_TPK);
                $("#NO_TRUCK").val(ui.item.NOPOL);
            },
        });

        $(function() {
            var forms = document.querySelectorAll("form");
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener(
                    "submit",
                    function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                    },
                    false
                );
            });

            $("#form-data").on("submit", function(e) {
                if (this.checkValidity()) {
                    e.preventDefault();
                    gateIn();
                }

                $(this).addClass("was-validated");
            });
        });

        function gateIn() {
            Swal.fire({
                title: "Insert Data",
                text: "Apakah Anda yakin ingin insert data gate in dari TPK?",
                type: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Insert",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.value) {
                    if ($("#tgl_gati").val() == "" || $("#CONT_NO").val() == "") {
                        Swal.fire({
                            icon: "warning",
                            title: "Peringatan",
                            text: "Silahkan isi tanggal gate in dan no container",
                        });
                    } else {
                        var no_cont_ = $("#CONT_NO").val();
                        var no_req_ = $("#REQ_REC").val();
                        var no_truck_ = $("#NO_TRUCK").val();
                        var no_seal_ = $("#NO_SEAL").val();
                        var no_nota_ = $("#NO_NOTA").val();
                        var status_ = $("#STATUS").val();
                        var masa_berlaku_ = $("#MASA_BERLAKU").val();
                        var keterangan_ = $("#KETERANGAN").val();
                        var tgl_gati = $("#tgl_gati").val();
                        var id_yard_ = $("#ID_YARD").val();
                        var bp_id_ = $("#BP_ID").val();
                        var no_req_tpk_ = $("#NO_REQ_TPK").val();

                        Swal.fire({
                            title: "Memproses...",
                            text: "Mohon tunggu",
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: `${$('meta[name="baseurl"]').attr("content")}maintenance/gate_admin/gate-in-tpk/add-gatein`,
                            type: "POST",
                            data: {
                                _token: $('meta[name="csrf-token"]').attr("content"),
                                NO_CONT: no_cont_,
                                NO_REQ: no_req_,
                                NO_TRUCK: no_truck_,
                                NO_NOTA: no_nota_,
                                NO_SEAL: no_seal_,
                                STATUS: status_,
                                MASA_BERLAKU: masa_berlaku_,
                                KETERANGAN: keterangan_,
                                ID_YARD: id_yard_,
                                BP_ID: bp_id_,
                                NO_REQ_TPK: no_req_tpk_,
                                tgl_gati: tgl_gati,
                            },
                            dataType: "json",
                            success: function(data) {
                                console.error(data);
                                Swal.close();
                                if (data == "TRUCK") {
                                    Swal.fire("Peringatan", "No Truck Harus Diisi", "warning");
                                } else if (data == "EXIST" || data == "EXIST_GATI") {
                                    Swal.fire("Peringatan", "Container Sudah Gate in", "warning");
                                } else if (data.message == "OK" || data.status == 200) {
                                    Swal.fire("Berhasil", "Gate IN Container Berhasil", "success");
                                    $("#CONT_NO").val("");
                                    $("#REQ_REC").val("");
                                    $("#NO_SEAL").val("");
                                    $("#SIZE").val("");
                                    $("#TYPE").val("");
                                    $("#KETERANGAN").val("");
                                    $("#NM_PBM").val("");
                                    $("#NO_NOTA").val("");
                                    $("#MASA_BERLAKU").val("");
                                    $("#NO_TRUCK").val("");
                                    $("#CONT_NO").focus();
                                } else {
                                    Swal.fire("Gagal", data.message || "Terjadi kesalahan", "error");
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                console.error(xhr);
                                let msg = "Terjadi kesalahan pada server";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire("Gagal", msg, "error");
                            }
                        });
                    }
                } else {
                    return false;
                }
            });
        }

        /** End Of Other Function Section */
        /** ============================= */

        /** Pra Save Notif Section */
        /** ====================== */
        function input_success(res) {
            if (res.status != 200) {
                input_error(res);
                return false;
            }

            $.toast({
                heading: "Berhasil!",
                text: res.message,
                position: "top-right",
                icon: "success",
                hideAfter: 2500,
                beforeHide: function() {
                    if (res.redirect?.need) {
                        Swal.fire({
                            html: "<h5>Berhasil Memproses Data <br> Mengembalikan Anda ke halaman sebelumnya...</h5>",
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });

                        Swal.showLoading();
                    } else {
                        return false;
                    }
                },
                afterHidden: function() {
                    if (res.redirect?.need) {
                        window.location.href = res.redirect.to;
                    } else {
                        return false;
                    }
                },

            });
        }

        function input_error(err) {
            $.toast({
                heading: "Gagal memproses data!",
                text: err.message,
                position: "top-right",
                icon: "error",
                hideAfter: 5000,
            });

            Swal.close()
        }

        function get_error(err) {
            console.log(err);
            $.toast({
                heading: "Gagal mengambil data!",
                text: err.message,
                position: "top-right",
                icon: "error",
                hideAfter: 5000,
            });
        }
        /** End Of Pra Save Notif Section */
        /** ============================= */
    </script>
@endsection

@push('after-script')
    <script src="{{ asset('assets/plugins/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js') }}">
    </script>
@endpush
