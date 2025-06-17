@extends('layouts.app')

@section('title')
    Gate Administrator (Gate In Container dari TPK)
@endsection

@push('after-style')
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
                                        <input type="text" class="form-control" id="NO_TRUCK" name="NO_TRUCK" required
                                            style="text-transform:uppercase">
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
                                        <input type="text" class="form-control" id="NM_PBM" name="NM_PBM" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="tgl_gati" class="form-label">Tgl. Gate In</label>
                                        <input type="text" class="form-control" id="tgl_gati" name="tgl_gati">
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
                    success: function(data) {
                        console.log(data);
                        if (data.length > 0) {
                            response(
                                data.map(function(value) {
                                    console.log(value);
                                    return {
                                        label: `${value.containerNo} | ${value.voyageIn}/${value.voyageOut}`,
                                        vesselName: value.vesselName,
                                        voyageIn: value.voyageIn,
                                        containerNo: value.containerNo,
                                        containerSize: value.containerSize,
                                        containerStatus: value.containerStatus,
                                        containerType: value.containerType,
                                        vesselConfirm: value.vesselConfirm,
                                        dischargeDate: value.dischargeDate,
                                        ydBlock: value.ydBlock,
                                        ydSlot: value.ydSlot,
                                        ydRow: value.ydRow,
                                        ydTier: value.ydTier,
                                    };
                                })
                            );
                        }

                        $("#CONT_NO").removeClass("ui-autocomplete-loading");
                    },
                });
            },
            select: function(event, ui) {
                $("#CONT_NO").val(ui.item.containerNo);
                $("#SIZE").val(ui.item.containerSize);
                $("#STATUS").val(ui.item.containerStatus);
                $("#TYPE").val(ui.item.containerType);
                $("#VOYAGE").val(ui.item.voyageIn);
                $("#VESSEL").val(ui.item.vesselName);
                $("#tgl_mulai").val(ui.item.vesselConfirm);
                $("#TGL_BONGKAR").val(ui.item.vesselConfirm);
                $("#TGL_STACK").val(ui.item.dischargeDate);
                $("#NO_UKK").val($("#IDVSB").val());
                $("#NM_AGEN").val(ui.item.NM_AGEN);
                $("#BP_ID").val("BP" + $("#VESCODE").val() + "" + $("#IDVSB").val());
                $("#ASAL_CONT").val("TPK");
                $("#NO_BOOKING").val(
                    "BP" + $("#VESCODE").val() + "" + $("#IDVSB").val()
                );
                $("#BLOK").val(ui.item.ydBlock);
                $("#SLOT").val(ui.item.ydSlot);
                $("#ROW").val(ui.item.ydRow);
                $("#TIER").val(ui.item.ydTier);

                //start  updated by clara ilcs 27 November 2023
                if (ui.item && ui.item.dischargeDate) {
                    var containerDischDateStr = ui.item.dischargeDate; // Format: YYYYMMDDHHmmss

                    // Parsing the date components correctly
                    var year = parseInt(containerDischDateStr.substring(0, 4));
                    var month = parseInt(containerDischDateStr.substring(4, 6)) -
                        1; // JavaScript months are 0-based
                    var day = parseInt(containerDischDateStr.substring(6, 8));
                    var hours = parseInt(containerDischDateStr.substring(8, 10));
                    var minutes = parseInt(containerDischDateStr.substring(10, 12));
                    var seconds = parseInt(containerDischDateStr.substring(12, 14));

                    // Create the date object
                    var containerDischDate = new Date(year, month, day);

                    // Check if date creation is valid
                    if (isNaN(containerDischDate.getTime())) {
                        console.error("Invalid date format");
                    } else {
                        // Add 4 days
                        containerDischDate.setDate(containerDischDate.getDate() + 4);

                        // Format the date as YYYY-MM-DD HH:mm:ss
                        var formattedDate =
                            containerDischDate.getFullYear() +
                            "-" +
                            ("0" + (containerDischDate.getMonth() + 1)).slice(-2) +
                            "-" +
                            ("0" + containerDischDate.getDate()).slice(-2);
                    }

                    $("#TGL_SELESAI").val(formattedDate);

                    return false;
                }
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
                    alert('YUHUUU')
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
