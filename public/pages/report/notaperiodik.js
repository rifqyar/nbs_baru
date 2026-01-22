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
        if (this.checkValidity()) {
            e.preventDefault();
            generateNota("#generate_nota");
        }
        $(this).addClass("was-validated");
    });
});

function generateNota(formId) {
    const data = $(formId).serialize();
    console.log(data);
    ajaxGetJson(
        `/report/nota-periodik/generate-nota?${data}`,
        "renderNotaData",
        "get_error"
    );
}

function renderNotaData(res) {
    $("#data-body").html(res.blade);
    $("#data-section").slideDown();

    const data = res.data;
    let html = '';

    console.log(data)
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
            const badgeTransfer = dt.transfer === 'Y'
                ? `<span class="badge bg-info rounded-pill p-2 text-white">Sudah Transfer</span>`
                : `<span class="badge bg-danger rounded-pill p-2 text-white">Belum Transfer</span>`;

            html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${dt.no_nota_mti}</td>
                    <td>${dt.no_faktur_mti}</td>
                    <td>${dt.no_request}</td>
                    <td>${dt.kegiatan}</td>
                    <td>
                        <span class="badge bg-info rounded-pill p-2 text-white">
                            <i class="mdi mdi-calendar"></i> ${dt.tgl_nota}
                        </span>
                    </td>
                    <td data-toggle="tooltip" title="${dt.emkl_full}">
                        ${dt.emkl_short}
                    </td>
                    <td>${dt.bayar}</td>
                    <td>Rp. ${dt.total_tagihan}</td>
                    <td>${dt.lunas}</td>
                    <td>${dt.status}</td>
                    <td>
                        <span class="badge bg-success rounded-pill p-2 text-white">
                            Ready to Transfer
                        </span>
                    </td>
                    <td>${badgeTransfer}</td>
                    <td class="text-center" style="white-space: pre-wrap">
                        ${dt.receipt_account ?? ''}
                    </td>
                </tr>
            `;
        });
    }

    console.log(html);
    $('#nota-body').html(html);

    // tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // DataTable init ulang
    if ($.fn.DataTable.isDataTable('.data-table')) {
        $('.data-table').DataTable().destroy();
    }

    $('.data-table').DataTable({
        pageLength: 25
    });
}

function exportToExcel() {
    const data = $('#generate_nota').serialize();
    ajaxGetJson(
        `/report/nota-periodik/export-nota?${data}`,
        "success_export",
        "get_error"
    );
}

function success_export(data){
    window.open(data.filePath, '_blank');
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
