$(function () {
    var forms = document.querySelectorAll("form");
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            },
            false
        );
    });

    $("#dataCont").on("submit", function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            batal("#dataCont");
        }
        $(this).addClass("was-validated");
    });
    $("#TGL_MULAI").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
});

$("#NO_CONT").autocomplete({
    source: function (request, response) {
        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}koreksi/batal-stuffing/data-cont`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                console.log(data);
                response(
                    data.map(function (value) {
                        return {
                            label: `${value.no_container} | ${value.size_} ${value.type_}`,
                            NO_CONTAINER: value.no_container,
                            ASAL_CONT: value.asal_cont,
                            NO_REQ_STUFF: value.no_req_stuff,
                            NO_REQ_DEL: value.no_req_del,
                            NO_REQ_ICT: value.no_req_ict,
                            KOMODITI: value.komoditi,
                            KD_KOMODITI: value.kd_komoditi,
                            HZ: value.hz,
                            SIZE_: value.size_,
                            TYPE_: value.type_,
                            VIA: value.via,
                            NO_REQUEST: value.no_request,
                            TGL_REQUEST: value.tgl_request,
                            STUFFING_DARI: value.stuffing_dari,
                            NO_BOOKING: value.no_booking,
                            NO_UKK: value.no_ukk,
                            NO_SEAL: value.no_seal,
                            BERAT: value.berat,
                            KETERANGAN: value.keterangan,
                        };
                    })
                );
            },
            error: function () {
                $.toast({
                    heading: "Gagal Mengambil Data",
                    position: "top-right",
                    loaderBg: "#ff6849",
                    icon: "error",
                    hideAfter: 3500,
                });
            },
        });
    },
    select: function (event, ui) {
        $("#NO_CONT").val(ui.item.NO_CONTAINER);
        $("#ASAL_CONT").val(ui.item.ASAL_CONT);
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
        $("#STUFFING_DARI").val(ui.item.STUFFING_DARI);
        $("#NO_BOOKING").val(ui.item.NO_BOOKING);
        $("#NO_UKK").val(ui.item.NO_UKK);
        $("#NO_SEAL").val(ui.item.NO_SEAL);
        $("#BERAT").val(ui.item.BERAT);
        $("#KETERANGAN").val(ui.item.KETERANGAN);

        return false;
    },
});

function batal() {
    Swal.fire({
        title: "Apakah anda yakin ingin melakukan batal stuffing pada Container ini?",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, batal container",
    }).then(async (conf) => {
        if (conf.value == true) {
            const formData = $("#dataCont").serialize();
            await ajaxPostJson(
                `/koreksi/batal-stuffing/batal-container`,
                formData,
                "input_success"
            );
        } else {
            return false;
        }
    });
}

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
        beforeHide: function () {
            Swal.fire({
                html: "<h5>Berhasil Batal Container Stuffing</h5>",
                showConfirmButton: false,
                allowOutsideClick: false,
            });

            Swal.showLoading();
        },
        afterHidden: function () {
            if (res.redirect.need) {
                console.log(res.redirect.to);
                window.location.href = res.redirect.to;
            } else {
                window.location.reload();
            }
        },
    });
}

function input_error(err) {
    console.log(err);
    $.toast({
        heading: "Gagal memproses data!",
        text: err.message,
        position: "top-right",
        icon: "error",
        hideAfter: 5000,
    });
}
