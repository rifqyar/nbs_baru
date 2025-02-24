var table, s_no_request, s_tgl_awal, s_tgl_akhir;
var cachePBM = {};
$(document).bind("keydown", function (e) {
    if (e.keyCode == 113) generateNota();
    return true;
});

$(function () {
    $("#start_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#end_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });

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

    $("#generate_nota").on("submit", function (e) {
        var btn = $(this).find("button[type=submit]:focus");
        actionForm = $(btn).data("action");
        if (this.checkValidity()) {
            e.preventDefault();
            if (actionForm == "generate") {
                generateNota("#generate_nota");
            } else {
                exportToExcel();
            }
        }
        $(this).addClass("was-validated");
    });
});

function generateNota(formId) {
    const data = $(formId).serialize();
    ajaxGetJson(
        `/report/realisasi/generate-nota?${data}`,
        "renderNotaData",
        "get_error"
    );
}

function renderNotaData(data) {
    $("#data-body").html(data.blade);
    $("#data-section").slideDown();

    if ($.fn.DataTable.isDataTable("#data-list")) {
        $("#data-list").DataTable().destroy();
    }
    $("#data-body").find("#data-list").DataTable();
}

function exportToExcel() {
    const data = $("#generate_nota").serialize();
    ajaxGetJson(
        `/report/realisasi/export-nota?${data}`,
        "success_export",
        "get_error"
    );
}

function success_export(data) {
    window.open(data.filePath, "_blank");
}

function resetSearch() {
    $("#search-data").find("input.form-control").val("").trigger("blur");
    $("#search-data").find("input.form-control").removeClass("was-validated");
    $('input[name="search"]').val("false");
    if ($.fn.DataTable.isDataTable("#data-list")) {
        $("#data-list").DataTable().destroy();
    }
    $('#clear_data').trigger('click')
    $("#data-body").html("");
}

$("#sort_asc").on("click", function () {
    var str = "";
    $("#id_menu1 option:selected").each(function (i) {
        str =
            '<option value="' +
            $(this).val() +
            ' ASC " selected>' +
            $(this).text() +
            "  ASC </option>";
        $("#id_menu2").append(str);
        $(this).remove();
    });
});

$("#sort_desc").on("click", function () {
    var str = "";
    $("#id_menu1 option:selected").each(function (i) {
        str =
            '<option value="' +
            $(this).val() +
            ' DESC " selected>' +
            $(this).text() +
            "  DESC </option>";
        $("#id_menu2").append(str);
        $(this).remove();
    });
});

$("#clear_data").on("click", function () {
    $("#id_menu1").html(
        "<option value='NO_CONTAINER'>No. Container</option><option value='NO_REQUEST'>No. Request</option><option value='TGL_REQUEST'>Tgl. Request</option> <option value='TGL_REALISASI'>Tgl. Realisasi</option> <option value='SIZE_'>Size</option> <option value='TYPE_'>Type</option> <option value='STATUS'>Status</option><option value='NM_PBM'>Nama Consignee</option>"
    );
    $("#id_menu2").html("");
});

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
