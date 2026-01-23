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
        if (this.checkValidity()) {
            e.preventDefault();
            generateNota("#generate_nota");
        }
        $(this).addClass("was-validated");
    });
});

function generateNota(formId) {
    const data = $(formId).serialize();
    ajaxGetJson(
        `/report/nota-periodik/generate-nota?${data}`,
        "renderNotaData",
        "get_error",
    );
}

function renderNotaData(res) {
    $("#data-body").html(res.blade);
    $("#data-section").slideDown();

    // destroy jika sudah ada
    if ($.fn.DataTable.isDataTable(".data-table")) {
        $(".data-table").DataTable().destroy();
    }

    $(".data-table").DataTable({
        processing: true,
        serverSide: true,
        pageLength: 15,
        responsive: true,
        ajax: {
            url: "/report/nota-periodik/generate-nota",
            type: "GET",
            data: function (d) {
                // kirim filter form
                const formData = $("#generate_nota").serializeArray();
                formData.forEach((x) => (d[x.name] = x.value));
            },
            dataSrc: function (json) {
                return json.data; // penting!
            },
        },

        columns: [
            {
                data: null,
                render: (d, t, r, m) => m.row + 1,
            },
            { data: "no_nota_mti" },
            { data: "no_faktur_mti" },
            { data: "no_request" },
            { data: "kegiatan" },
            {
                data: "tgl_nota",
                render: (d) => `<span class="badge bg-info rounded-pill p-2 text-white"><i class="mdi mdi-calendar"></i>${d}</span>`,
            },
            {
                data: "emkl_short",
                render: (d, t, r) =>
                    `<span data-toggle="tooltip" data-placement="top" title="${r.emkl_full}">${d}</span>`,
            },
            { data: "bayar" },
            {
                data: "total_tagihan",
                render: (d) => `Rp. ${d}`,
            },
            { data: "lunas" },
            { data: "status" },
            {
                data: null,
                render: () =>
                    `<span class="badge bg-success rounded-pill p-2 text-white">Ready to Transfer</span>`,
            },
            {
                data: "transfer",
                render: (d) =>
                    d === "Y"
                        ? `<span class="badge bg-info rounded-pill p-2 text-white">Sudah Transfer</span>`
                        : `<span class="badge bg-danger rounded-pill p-2 text-white">Belum Transfer</span>`,
            },
            {
                data: "receipt_account",
                defaultContent: "",
            },
        ],

        drawCallback: function () {
            $('[data-toggle="tooltip"]').tooltip();
        },
    });
}

function exportToExcel() {
    const data = $("#generate_nota").serialize();
    ajaxGetJson(
        `/report/nota-periodik/export-nota?${data}`,
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
