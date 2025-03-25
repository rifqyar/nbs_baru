var table, s_no_request, s_tgl_awal, s_tgl_akhir, table_cont;
var cachePBM = {};
$(document).bind("keydown", function (e) {
    if (e.keyCode == 113) addContainer();
    return true;
});

$(function () {
    addContainer();
    if ($("#data-section").length > 0 && $(".alert").css("display") == "none") {
        $("html, body").animate(
            {
                scrollTop: $("#data-section").offset().top,
            },
            1000
        );
    }

    setTimeout(() => {
        if (
            $(".alert").css("display") != "none" &&
            $("#data-section").length > 0
        ) {
            $(".alert").alert("close");
            $("html, body").animate(
                {
                    scrollTop: $("#data-section").offset().top,
                },
                1250
            );
        }
    }, 2500);

    $("#no_cont").on("keydown", function (e) {
        return e.which !== 32;
    });

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

    $("#search-data").on("submit", function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            $('input[name="search"]').val("true");
            if ($("#search-data").find("input.form-control").val() == "") {
                $('input[name="search"]').val("false");
            }

            $("html, body").animate(
                {
                    scrollTop: $("#data-section").offset().top,
                },
                1250
            );
            table.ajax.reload();
        }

        $(this).addClass("was-validated");
    });

    $("#request-header").on("submit", function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            saveEditData("#request-header");
        }
        $(this).addClass("was-validated");
    });

    $("#form-add-container").on("submit", function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            saveContainerData("#form-add-container");
        }
        $(this).addClass("was-validated");
    });

    $("#form-add").on("submit", function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            saveEditData("#form-add");
        }
        $(this).addClass("was-validated");
    });

    $("#start_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#end_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    getData();
    getDataCont();
});

/** GET DATA & Auto Complete Section */
/** ================================ */
function getData() {
    table = $(".data-table").DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data`,
            method: "POST",
            data: function (data) {
                data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                data.cari = $('input[name="search"]').val();
                data.no_request = $('input[name="no_request"]').val();
                data.tgl_awal = $('input[name="tgl_awal"]').val();
                data.tgl_akhir = $('input[name="tgl_akhir"]').val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                className: "text-center",
                width: "20px",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
                width: "200px",
            },
            {
                data: "no_request",
                name: "no_request",
            },
            {
                data: "tgl_request",
                name: "tgl_request",
            },
            {
                data: "nama_emkl",
                name: "nama_emkl",
            },
            {
                data: "receiving_dari",
                name: "receiving_dari",
            },
        ],
        // fnDrawCallback: () => {
        //     const tooltipTriggerList = document.querySelectorAll(
        //         '[data-bs-toggle="tooltip"]'
        //     );
        //     const tooltipList = [...tooltipTriggerList].map(
        //         (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
        //     );
        // },
    });
}

function getDataCont() {
    table_cont = $('#table-contlist').DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: false,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/datatable-cont`,
            method: "POST",
            data: function (data) {
                data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                data.no_request = $('input[name="no_req"]').val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                className: "text-center",
                width: "20px",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
                width: "200px",
            },
            {
                data: "no_container",
                name: "no_container",
            },
            {
                data: "status",
                name: "status",
            },
            {
                data: "size_",
                name: "size_",
            },
            {
                data: "type_",
                name: "type_",
            },
            {
                data: "hz",
                name: "hz",
            },
            {
                data: "nama_yard",
                name: "nama_yard",
            },
            {
                data: "kd_owner",
                name: "kd_owner",
            },
        ],
    })
}

$("#consignee").autocomplete({
    source: function (request, response) {
        var term = request.term;
        if (term in cachePBM) {
            response(cachePBM[term]);
            return;
        }

        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data-pbm`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: value.nm_pbm,
                            kd_pbm: value.kd_pbm,
                            almt_pbm: value.almt_pbm,
                            npwp: value.no_npwp_pbm,
                            no_acc_pbm: value.no_account_pbm,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#consignee").val(ui.item.label);
        $("#kd_consignee").val(ui.item.kd_pbm);
        $("#acc_consignee").val(ui.item.no_acc_pbm);
        $("#almt_consignee").val(ui.item.almt_pbm);
        $("#npwp").val(ui.item.npwp);
        return false;
    },
});

$("#NM_KAPAL").autocomplete({
    source: function (request, response) {
        var term = request.term;
        if (term in cachePBM) {
            response(cachePBM[term]);
            return;
        }

        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data-kapal`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: value.nm_pbm,
                            kd_pbm: value.kd_pbm,
                            almt_pbm: value.almt_pbm,
                            npwp: value.no_npwp_pbm,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#VOYAGE_IN").val(ui.item.VOYAGE_IN);
        $("#VOYAGE_OUT").val(ui.item.VOYAGE_OUT);
        if (ui.item.VESSEL_CODE && ui.item.ID_VSB_VOYAGE) {
            $("#NO_BOOKING").val(
                `BP${ui.item.VESSEL_CODE}${ui.item.ID_VSB_VOYAGE}`
            );
        }
        $("#IDVSB").val(ui.item.ID_VSB_VOYAGE);
        $("#CALLSIGN").val(ui.item.CALL_SIGN);
        $("#VESSEL_CODE").val(ui.item.VESSEL_CODE);
        $("#TANGGAL_JAM_TIBA").val(ui.item.ATA ? ui.item.ATA : ui.item.ETA);
        $("#TANGGAL_JAM_BERANGKAT").val(
            ui.item.ATD ? ui.item.ATD : ui.item.ETD
        );
        $("#OPERATOR_NAME").val(ui.item.OPERATOR_NAME);
        $("#OPERATOR_ID").val(ui.item.OPERATOR_ID);
        $("#POD").val(ui.item.ID_POD);
        $("#POL").val(ui.item.ID_POL);
        $("#VOYAGE").val(ui.item.VOYAGE);
        return false;
    },
});

$("#no_cont").autocomplete({
    source: function (request, response) {
        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data-container`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: value.no_container,
                            no_cont: value.no_container,
                            size: value.size_cont,
                            type: value.type_cont,
                            id_vsb: value.no_ukk,
                            vessel: value.vessel,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#no_cont").val(ui.item.label);
        $("#id_vsb").val(ui.item.id_vsb);
        $("#vessel").val(ui.item.vessel);
        $("#size option").each(function () {
            if ($(this).val() == ui.item.size) {
                $(this).prop("selected", "selected");
                $(this).attr("selected", "selected");
            } else {
                $(this).removeAttr("selected");
                $(this).removeProp("selected");
            }
        });

        $("#type option").each(function () {
            if ($(this).val() == ui.item.type) {
                $(this).prop("selected", "selected");
                $(this).attr("selected", "selected");
            } else {
                $(this).removeAttr("selected");
                $(this).removeProp("selected");
            }
        });
        return false;
    },
});

$("#komoditi").autocomplete({
    source: function (request, response) {
        var term = request.term;
        if (term in cachePBM) {
            response(cachePBM[term]);
            return;
        }

        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data-komoditi`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: value.nm_commodity,
                            kd_komoditi: value.kd_commodity,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#komoditi").val(ui.item.label);
        $("#kd_komoditi").val(ui.item.kd_komoditi);
        return false;
    },
});

$("#owner").autocomplete({
    source: function (request, response) {
        var term = request.term;
        if (term in cachePBM) {
            response(cachePBM[term]);
            return;
        }

        $.ajax({
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/receiving/data-owner`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: value.nm_owner,
                            kd_owner: value.kd_owner,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#owner").val(ui.item.label);
        $("#kd_owner").val(ui.item.kd_owner);
        return false;
    },
});

$("#type").on("change", function () {
    if ($(this).val() == "") {
        $("#type option").each(function () {
            $(this).removeAttr("selected");
            $(this).removeProp("selected");
        });
    }
});

$("#size").on("change", function () {
    if ($(this).val() == "") {
        $("#size option").each(function () {
            $(this).removeAttr("selected");
            $(this).removeProp("selected");
        });
    }
});
/** End Of Get Data & Auto Complete Section */
/** ======================================= */

/** Post Data (Save / Edit / Delete) Section */
/** ======================================== */
async function saveEditData(form) {
    formData = $(form).serialize();
    let formArray = $(form).find("input[required]");
    for (let i = 0; i < formArray.length; i++) {
        const formVal = $(formArray[i]).val();
        const formName = $(formArray[i]).attr("name");
        if (formVal == "") {
            var text = "Masih Ada Data yang kosong";
            if (formName == "id_consignee") {
                text = "Harap masukan Penerima / Consignee dengan benar";
            }
            $.toast({
                heading: "Gagal Input Data",
                text: text,
                position: "top-right",
                loaderBg: "#ff6849",
                icon: "error",
                hideAfter: 3500,
            });

            return false;
        }
    }

    await ajaxPostJson(
        "/request/receiving/add-edit",
        formData,
        "input_success"
    );
}

async function saveContainerData(formId) {
    var form = $(formId).serialize();
    let formArray = $(formId).find("input[required]");
    let canInput = true;
    var text = "";
    for (let i = 0; i < formArray.length; i++) {
        const formVal = $(formArray[i]).val();
        const formName = $(formArray[i]).attr("name");
        if (formVal == "") {
            if (formName == "id_vsb") {
                text = "No. Container";
            }
            if (formName == "kd_komoditi") {
                text += ", Komoditi";
            }
            if (formName == "kd_owner") {
                text += ", Pemilik Container";
            }

            canInput = false;
        }
    }

    if (canInput == false) {
        $.toast({
            heading:
                "Gagal Input Data, masih ada data yang kosong atau data tidak dipilih dengan benar",
            text: text,
            position: "top-right",
            loaderBg: "#ff6849",
            icon: "error",
            hideAfter: 3500,
        });
    } else {
        await ajaxPostJson(
            "/request/receiving/save-container",
            form,
            "input_success"
        );
        table_cont.ajax.reload()

        $("#no_cont").val("");
        $("#id_vsb").val("");
        $("#vessel").val("");
        $("#komoditi").val("");
        $("#kd_komoditi").val("");
    }
}

async function delCont(noCont, noReq) {
    Swal.fire({
        title: "Apakah anda yakin ingin menghapus data Container?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hapus container",
    }).then(async (conf) => {
        if (conf.value == true) {
            await ajaxGetJson(
                `/request/receiving/del-cont/${noCont}/${noReq}`,
                "input_success",
                "input_error"
            );
            noReq = atob(noReq);
            table_cont.ajax.reload()
        } else {
            return false;
        }
    });
}
/** End Of Post Data (Save / Edit / Delete) Section */
/** =============================================== */

/** Other Function Section */
/** ====================== */
function addContainer() {
    if ($("#form-card-container").css("display") == "none") {
        $("#form-card-container").slideDown();
    } else {
        $("#form-card-container").slideUp();
    }
}

function cancelAddCont() {
    if ($("#form-card-container").css("display") != "none") {
        $("#form-card-container").slideUp();
        $("#no_cont").val("");
        $("#id_vsb").val("");
        $("#vessel").val("");
        $("#komoditi").val("");
        $("#kd_komoditi").val("");
        $("#owner").val("");
        $("#kd_owner").val("");
    }
}

function refreshContList(res) {
    var listTable = "";
    var container = res.container;
    var noReq = res.no_req;
    container.map((v, k) => {
        listTable += `
            <tr>
                <td class="text-center">${k + 1}</td>
                <td class="text-center">${v.no_container}</td>
                <td class="text-center">${v.status}</td>
                <td class="text-center">${v.size_}</td>
                <td class="text-center">${v.type_}</td>
                <td class="text-center">${v.hz}</td>
                <td class="text-center">${v.nama_yard}</td>
                <td class="text-center">${v.kd_owner}</td>
                <td class="text-center">
                    <button class="btn btn-rounded btn-danger" onclick="delCont('${btoa(
                        v.no_container
                    )}', '${btoa(noReq)}')">
                        <i class="mdi mdi-delete h5"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    // $("#data-contlist-view").html(listTable);
}

function resetSearch() {
    $("#search-data").find("input.form-control").val("").trigger("blur");
    $("#search-data").find("input.form-control").removeClass("was-validated");
    $('input[name="search"]').val("false");
    table.ajax.reload();
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
        beforeHide: function () {
            let text = "";
            if (res.redirect.need) {
                text =
                    "<h5>Berhasil input Request Receiving,<br> Mengembalikan Anda ke halaman sebelumnya...</h5>";
            } else {
                text = "<h5>Berhasil input Request Receiving</h5>";
            }

            Swal.fire({
                html: text,
                showConfirmButton: false,
                allowOutsideClick: false,
            });

            Swal.showLoading();
        },
        afterHidden: function () {
            if (res.redirect.need) {
                window.location.href = res.redirect.to;
            } else {
                Swal.close();
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
