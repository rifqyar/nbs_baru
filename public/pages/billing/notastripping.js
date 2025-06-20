var table, s_no_req, s_tgl_awal, s_tgl_akhir;
$(function () {
    if ($("#data-section").length > 0 && $(".alert").css("display") == "none") {
        $("html, body").animate(
            {
                scrollTop: $("#data-section").offset().top,
            },
            1000
        );
    }

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
    $("#start_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $("#end_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    // $('#list-cont-table').DataTable()
    getData();
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
            )}billing/nota-stripping/data`,
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
                responsivePriority: -1,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                width: "200px",
                responsivePriority: -1,
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
                data: "do_bl",
                name: "do_bl",
                className: "text-center text-wrap",
                responsivePriority: 1,
            },
            {
                data: "penumpukan_oleh",
                name: "penumpukan_oleh",
                responsivePriority: 1,
            },
            {
                data: "jml",
                name: "jml",
                className: "text-center text-wrap",
                responsivePriority: 1,
            },
        ],
        // fnDrawCallback: () => {
        //     const tooltipTriggerList = document.querySelectorAll('[data-toggle="tooltip"]')
        //     const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        // },
    });
}

function resetSearch() {
    $("#search-data").find("input.form-control").val("").trigger("blur");
    $("#search-data").find("input.form-control").removeClass("was-validated");
    $('input[name="search"]').val("false");
    $("html, body").animate(
        {
            scrollTop: $("#data-section").offset().top,
        },
        1250
    );
    table.ajax.reload();
}

function recalc(noReq, noNota) {
    Swal.fire({
        title: "Apakah anda yakin ingin menghitung ulang nota stripping?",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hitung ulang",
    }).then(async (conf) => {
        if (conf.value == true) {
            var data = new FormData();
            data.append("no_req", noReq);
            data.append("no_nota", noNota);
            data.append("_token", $('meta[name="csrf-token"]').attr("content"));

            await ajaxPostFile(
                "/billing/nota-stripping/recalculate",
                data,
                "input_success"
            );
            table.ajax.reload();
        } else {
            return false;
        }
    });
}

function recalc_relok(noReq, noNota) {
    Swal.fire({
        title: "Apakah anda yakin ingin menghitung ulang nota relokasi?",
        type: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hitung ulang",
    }).then(async (conf) => {
        if (conf.value == true) {
            var data = new FormData();
            data.append("no_req", noReq);
            data.append("_token", $('meta[name="csrf-token"]').attr("content"));

            await ajaxPostFile(
                "/billing/nota-stripping/recalculate-pnk",
                data,
                "input_success"
            );
            table.ajax.reload();
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

    swal.close();
    $.toast({
        heading: "Berhasil!",
        text: res.message,
        position: "top-right",
        icon: "success",
        hideAfter: 2500,
    });
}

function input_error(err) {
    console.log(err);
    swal.close();
    $.toast({
        heading: "Gagal memproses data!",
        text: err.message,
        position: "top-right",
        icon: "error",
        hideAfter: 5000,
    });
}
