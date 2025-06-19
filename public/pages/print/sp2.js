var table, s_no_req, s_tgl_awal, s_tgl_akhir
$(document).bind("keydown", function (e) {
    if (e.keyCode == 113) searchData();
    return true;
});

$(function () {
    if($('#data-section').length > 0 && $('.alert').css('display') == 'none'){
        $('html, body').animate({
            scrollTop: $("#data-section").offset().top
        }, 1000);
    }

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

    $("#search-data").on("submit", function (e) {
        var btn = $(this).find("button[type=submit]:focus");
        actionForm = $(btn).data("action");
        if (this.checkValidity()) {
            e.preventDefault();
            $('input[name="search"]').val('1')
            if($('#search-data').find('input.form-control').val() == ''){
                $('input[name="search"]').val('0')
            }

            $('html, body').animate({
                scrollTop: $("#data-section").offset().top
            }, 1250);
            table.ajax.reload()
        }
        $(this).addClass("was-validated");
    });

    getData()
});

function getData() {
    table = $(".data-table").DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}print/sp2/data`,
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
                responsivePriority: -1
            },
            {
                data: "no_request",
                name: "no_request",
                responsivePriority: 0,
            },
            {
                data: "tgl_request",
                name: "tgl_request",
                responsivePriority: 0,
            },
            {
                data: "nama_emkl",
                name: "nama_emkl",
                className: "text-center text-wrap",
                responsivePriority: 1,
            },
            {
                data: "jml_cont",
                name: "jml_cont",
                responsivePriority: 1,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center ",
                responsivePriority: -1
            },
        ],
        fnDrawCallback: () => {
            const tooltipTriggerList = document.querySelectorAll('[data-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        },
    });
}
