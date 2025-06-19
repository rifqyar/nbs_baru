var table, s_no_req, s_tgl_awal, s_tgl_akhir
$( function(){
    if($('#data-section').length > 0 && $('.alert').css('display') == 'none'){
        $('html, body').animate({
            scrollTop: $("#data-section").offset().top
        }, 1000);
    }

    var forms = document.querySelectorAll('form')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
            }, false)
        })

    $('#search-data').on('submit', function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            $('input[name="search"]').val('true')
            if($('#search-data').find('input.form-control').val() == ''){
                $('input[name="search"]').val('false')
            }

            $('html, body').animate({
                scrollTop: $("#data-section").offset().top
            }, 1250);
            table.ajax.reload()
        }

        $(this).addClass('was-validated');

    });
    $('#start_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $('#end_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $('#list-cont-table').DataTable()
    getData()
})

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
            )}request/stripping/perpanjangan/data`,
            method: "POST",
            data: function (data) {
                data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                data.cari = $('input[name="search"]').val();
                data.no_request = $('input[name="no_req"]').val();
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
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center",
                width: "200px",
                responsivePriority: -1
            },
            {
                data: "no_request",
                name: "no_request",
                responsivePriority: 0,
            },
            {
                data: "ex_req",
                name: "ex_req",
                responsivePriority: 0,
            },
            {
                data: "no_do_bl",
                name: "no_do_bl",
                responsivePriority: 1,
            },
            {
                data: "nama_consignee",
                name: "nama_consignee",
                responsivePriority: 0,
            },
            {
                data: "total_box",
                name: "total_box",
                responsivePriority: 1,
            },
        ],
    });
}
/** End Of Get Data & Auto Complete Section */
/** ======================================= */

/** Post Data (Save / Edit / Delete) Section */
/** ======================================== */
function simpanarea(){
    const formHeader = $('#form-request').serializeArray()
    const formContainer = $('#form-container').serializeArray()

    var form = new FormData()
    form.append('_token', $('meta[name="csrf-token"]').attr('content'))
    for (let i = 0; i < formHeader.length; i++) {
        form.append(formHeader[i].name, formHeader[i].value)
    }

    for (let i = 0; i < formContainer.length; i++) {
        form.append(formContainer[i].name, formContainer[i].value)
    }

    ajaxPostFile('/request/stripping/perpanjangan/add', form, 'input_success')
}

function saveEdit(){
    const formHeader = $('#form-request').serializeArray()
    const formContainer = $('#form-container').serializeArray()

    var form = new FormData()
    form.append('_token', $('meta[name="csrf-token"]').attr('content'))
    for (let i = 0; i < formHeader.length; i++) {
        form.append(formHeader[i].name, formHeader[i].value)
    }

    for (let i = 0; i < formContainer.length; i++) {
        form.append(formContainer[i].name, formContainer[i].value)
    }

    ajaxPostFile('/request/stripping/perpanjangan/update', form, 'input_success')
}

function delCont(noCont, noReq)
{
    Swal.fire({
        title: `Apakah anda yakin ingin menghapus data Container (${noCont})?`,
        text: `Data yang sudah dihapus tidak dapat dikembalikan`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText:'Batal',
        confirmButtonText: 'Ya, Hapus Data'
    }).then(async (result) => {
        if (result.value == true) {
            await ajaxGetJson(`/request/stripping/perpanjangan/delete-cont/${noCont}/${noReq}`,'input_success', 'get_error')
        } else {
            return false
        }
    })
}

function approve(noReq, perpDari)
{
    Swal.fire({
        title: `Apakah anda yakin ingin Approve Request Perpanjangan Stripping (${noReq})?`,
        text: `Data yang sudah diapprove tidak dapat diedit lagi`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText:'Batal',
        confirmButtonText: 'Ya, Approve Request'
    }).then(async (result) => {
        if (result.value == true) {
            let form = new FormData()
            form.append('_token', $('meta[name="csrf-token"]').attr('content'))
            form.append('no_req', noReq)
            form.append('perp_dari', perpDari)
            await ajaxPostFile(`/request/stripping/perpanjangan/approve`, form, 'input_success')
        } else {
            return false
        }
    })
}
/** End Of Post Data (Save / Edit / Delete) Section */
/** =============================================== */

/** Other Function Section */
/** ====================== */
function resetSearch(){
    $('#search-data').find('input.form-control').val('').trigger('blur')
    $('#search-data').find('input.form-control').removeClass('was-validated')
    $('input[name="search"]').val('false')
    table.ajax.reload()
}
/** End Of Other Function Section */
/** ============================= */

/** Pra Save Notif Section */
/** ====================== */
function input_success(res){
    if (res.status != 200) {
        input_error(res)
        return false
    }

    $.toast({
        heading: 'Berhasil!',
        text: res.message,
        position: 'top-right',
        icon: 'success',
        hideAfter: 2500,
        beforeHide: function(){
            if(res.redirect.need){
                Swal.fire({
                    html: "<h5>Berhasil input Perpanjangan Stripping,<br> Mengallihkan Anda ke halaman lain...</h5>",
                    showConfirmButton: false,
                    allowOutsideClick: false,
                });

                Swal.showLoading();
            } else {
                return false;
            }
        },
        afterHidden: function () {
            if(res.redirect.need){
                window.location.href = res.redirect.to
            } else {
                return false;
            }
        },
    });
}

function input_error(err){
    console.log(err)
    $.toast({
        heading: 'Gagal memproses data!',
        text: err.message,
        position: 'top-right',
        icon: 'error',
        hideAfter: 5000,
    });
}

function get_error(err){
    console.log(err)
    $.toast({
        heading: 'Gagal mengambil data!',
        text: err.message,
        position: 'top-right',
        icon: 'error',
        hideAfter: 5000,
    });
}
/** End Of Pra Save Notif Section */
/** ============================= */
