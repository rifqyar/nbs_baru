$(function () {
    if ($.fn.bootstrapMaterialDatePicker) {
        $("#start_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false, format: 'YYYY-MM-DD' });
        $("#end_date").bootstrapMaterialDatePicker({ weekStart: 0, time: false, format: 'YYYY-MM-DD' });
    }

    // Set default dates (today)
    var today = moment().format('YYYY-MM-DD');
    if (!$("#start_date").val()) $("#start_date").val(today);
    if (!$("#end_date").val()) $("#end_date").val(today);

    // Sorting controls
    $("#sort_asc").on("click", function () {
        $("#id_menu1 option:selected").each(function () {
            var val = $(this).val();
            var txt = $(this).text();
            var opt = '<option value="' + val + ' ASC" selected>' + txt + ' ASC</option>';
            $("#id_menu2").append(opt);
            $(this).remove();
        });
    });

    $("#sort_desc").on("click", function () {
        $("#id_menu1 option:selected").each(function () {
            var val = $(this).val();
            var txt = $(this).text();
            var opt = '<option value="' + val + ' DESC" selected>' + txt + ' DESC</option>';
            $("#id_menu2").append(opt);
            $(this).remove();
        });
    });

    $("#clear_data").on("click", function () {
        $("#id_menu1").html(
            '<option value="NO_REQUEST">NO. Request</option>' +
            '<option value="TANGGAL">Tanggal</option>' +
            '<option value="NO_NOTA">No. Nota</option>' +
            '<option value="KEGIATAN">Kegiatan</option>' +
            '<option value="JUMLAH_PASS">Jumlah Pass</option>' +
            '<option value="BIAYA">Biaya</option>'
        );
        $("#id_menu2").html("");
    });

    // Generate Report Button Click
    $("#btn_generate").on("click", function (e) {
        e.preventDefault();
        generateReport();
    });

    // Generate Excel Button Click
    $("#btn_export").on("click", function (e) {
        e.preventDefault();
        exportExcel();
    });
});

function generateReport() {
    var tglAwal = $("#start_date").val();
    var tglAkhir = $("#end_date").val();

    if (!tglAwal || !tglAkhir) {
        swal.fire("Peringatan", "Harap isi Periode Tanggal Awal dan Tanggal Akhir!", "warning");
        return;
    }

    // Select all options in #id_menu2 so serialize includes them
    $("#id_menu2 option").prop("selected", true);

    var formData = $("#form_pass_truck").serialize();

    $.ajax({
        url: '/report/pass-truck/generate-report?' + formData,
        type: 'GET',
        dataType: 'json',
        beforeSend: function () {
            Swal.fire({
                title: 'Memuat Data...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function (res) {
            Swal.close();
            if (res.status && res.status.code === 200) {
                $("#data-body").html(res.blade);
                $("#lbl_total_pass").text(res.total_pass || "0");
                $("#lbl_total_biaya").text(res.total_biaya || "0");
                $("#data-section").slideDown();

                if ($.fn.DataTable) {
                    if ($.fn.DataTable.isDataTable("#table_passtruck")) {
                        $("#table_passtruck").DataTable().destroy();
                    }
                    $("#table_passtruck").DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        pageLength: 25
                    });
                }
            } else {
                Swal.fire("Gagal", res.message || "Gagal mengambil data laporan.", "error");
            }
        },
        error: function (xhr) {
            Swal.close();
            var msg = "Terjadi kesalahan sistem saat mengambil data.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Swal.fire("Error", msg, "error");
        }
    });
}

function exportExcel() {
    var tglAwal = $("#start_date").val();
    var tglAkhir = $("#end_date").val();

    if (!tglAwal || !tglAkhir) {
        Swal.fire("Peringatan", "Harap isi Periode Tanggal Awal dan Tanggal Akhir!", "warning");
        return;
    }

    $("#id_menu2 option").prop("selected", true);

    var formData = $("#form_pass_truck").serialize();
    window.location.href = '/report/pass-truck/generate-excel?' + formData;
}

function resetSearch() {
    var today = moment().format('YYYY-MM-DD');
    $("#start_date").val(today);
    $("#end_date").val(today);
    $("#option_kegiatan").val("ALL");
    $("#clear_data").trigger("click");
    $("#data-body").html("");
    $("#lbl_total_pass").text("0");
    $("#lbl_total_biaya").text("0");
    $("#data-section").slideUp();
}
