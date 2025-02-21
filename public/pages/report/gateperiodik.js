var table, s_no_request, s_tgl_awal, s_tgl_akhir;
var cachePBM = {};
$(document).bind("keydown", function (e) {
    if (e.keyCode == 113) generateNota();
    return true;
});

$(function () {
    $("#start_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#end_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#NM_KAPAL").attr("readonly", true);

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

function change_visible() {
    if ($("#option_kegiatan").val() == "GATO" && $("#lokasi").val() == "06") {
        $("#NM_KAPAL").val("");
        $("#NO_UKK").val("");
        $("#VOYAGE").val("");
        $("#NO_BOOKING").val("");
        $("#NM_KAPAL").removeAttr("readonly");
    } else {
        $("#NM_KAPAL").val("");
        $("#NO_UKK").val("");
        $("#VOYAGE").val("");
        $("#NO_BOOKING").val("");
        $("#NM_KAPAL").attr("readonly", true);
    }
}

function generateNota(formId) {
    const data = $(formId).serialize();
    ajaxGetJson(
        `/report/gate-periodik/generate-nota?${data}`,
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
        `/report/gate-periodik/export-nota?${data}`,
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
    console.log(err)
    $.toast({
        heading: "Gagal mengambil data!",
        text: err.message,
        position: "top-right",
        icon: "error",
        hideAfter: 5000,
    });
}

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
            )}report/gate-periodik/master-vessel`,
            type: "GET",
            dataType: "json",
            data: {
                search: request.term,
                year: $('input[name="tgl_awal"]').val(),
            },
            success: function (data) {
                response(
                    data.map(function (value) {
                        return {
                            label: `${value.nm_kapal} [ ${value.voyage_in} ]`,
                            no_ukk: value.no_ukk,
                            no_booking: value.no_booking,
                            voyage_in: value.voyage_in,
                        };
                    })
                );
            },
        });
    },
    select: function (event, ui) {
        $("#NM_KAPAL").val(ui.item.label + " - " + ui.item.no_booking);
        $("#NO_UKK").val(ui.item.no_ukk);
        $("#VOYAGE").val(ui.item.voyage_in);
        $("#NO_BOOKING").val(ui.item.no_booking);
        return false;
    },
});
