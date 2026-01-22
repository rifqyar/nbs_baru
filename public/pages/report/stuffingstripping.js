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
            false,
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
        `/report/approval-stuffing-stripping/generate-nota?${data}`,
        "renderNotaData",
        "get_error",
    );
}

function renderNotaData(res) {
    // load template table
    $("#data-body").html(res.blade);
    $("#data-section").slideDown();

    const data = res.data;
    let html = "";

    if (!data || data.length === 0) {
        html = `
            <tr>
                <td colspan="15">
                    <h6 class="text-center text-danger">Tidak Ada Data</h6>
                </td>
            </tr>
        `;
    } else {
        $.each(data, function (i, dt) {
            html += `
            <tr>
                <td>${i + 1}</td>
                <td>${dt.no_request}</td>

                <td>
                    <span class="badge bg-info rounded-pill p-2 text-white">
                        <i class="mdi mdi-calendar"></i> ${dt.tgl_request}
                    </span>
                </td>

                <td>${dt.no_container}</td>
                <td>${dt.pin_number}</td>
                <td>${dt.size_} / ${dt.type_}</td>
                <td>${dt.kegiatan}</td>
                <td>${dt.lokasi_tpk}</td>
                <td>${dt.loc_uster}</td>

                <td>
                    <span class="badge bg-info rounded-pill p-2 text-white">
                        <i class="mdi mdi-calendar"></i> ${dt.tgl_approve}
                    </span>
                </td>

                <td>
                    <span class="badge bg-warning rounded-pill p-2 text-white">
                        <i class="mdi mdi-calendar"></i> ${dt.active_to}
                    </span>
                </td>

                <td>
                    <span class="badge bg-primary rounded-pill p-2 text-white">
                        <i class="mdi mdi-calendar"></i> ${dt.tgl_realisasi}
                    </span>
                </td>

                <td>${dt.nm_pbm}</td>
                <td>${dt.commodity}</td>
                <td>${dt.nm_kapal} / ${dt.voyage}</td>
            </tr>
            `;
        });
    }

    $("#nota-body").html(html);

    // destroy & reinit datatable
    if ($.fn.DataTable.isDataTable("#data-list")) {
        $("#data-list").DataTable().destroy();
    }

    $("#data-list").DataTable({
        pageLength: 15,
        deferRender: true,
        scrollY: 500,
        scrollCollapse: true,
        scroller: true,
        responsive: true,
    });
}

function exportToExcel() {
    const data = $("#generate_nota").serialize();
    ajaxGetJson(
        `/report/approval-stuffing-stripping/export-nota?${data}`,
        "success_export",
        "get_error",
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
    $("#data-body").html("");
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
