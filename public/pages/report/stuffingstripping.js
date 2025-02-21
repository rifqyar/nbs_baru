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
            if(actionForm == 'generate'){
                generateNota("#generate_nota");
            } else {
                exportToExcel()
            }
        }
        $(this).addClass("was-validated");
    });
});

function generateNota(formId){
    const data = $(formId).serialize();
    ajaxGetJson(
        `/report/approval-stuffing-stripping/generate-nota?${data}`,
        "renderNotaData",
        "get_error"
    );
}

function renderNotaData(data){
    $("#data-body").html(data.blade);
    $("#data-section").slideDown();

    if ($.fn.DataTable.isDataTable('#data-list')) {
        $('#data-list').DataTable().destroy()
    }
    $('#data-body').find('#data-list').DataTable()
}

function exportToExcel() {
    const data = $("#generate_nota").serialize();
    ajaxGetJson(
        `/report/approval-stuffing-stripping/export-nota?${data}`,
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
    if ($.fn.DataTable.isDataTable('#data-list')) {
        $('#data-list').DataTable().destroy()
    }
    $('#data-body').html('')
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

