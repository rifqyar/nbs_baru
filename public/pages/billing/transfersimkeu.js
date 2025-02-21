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
                let formEl = $(form)
                let tglAwal = $(formEl).find('#start_date').val()
                let tglAkhir = $(formEl).find('#end_date').val()
                let noNota = $(formEl).find('#no_nota').val()

                $(formEl).find('input').removeClass('is-invalid')
                if((tglAwal != '' || tglAkhir != '') && noNota != '') {
                    $.toast({
                        heading: 'Tidak Bisa Memproses Data!!',
                        text: 'Periode Kegiatan atau nota harus diisi salah satu saja',
                        position: 'top-right',
                        icon: 'warning',
                        hideAfter: 10000,
                    });
                    $(formEl).find('#start_date').addClass('is-invalid')
                    $(formEl).find('#end_date').addClass('is-invalid')
                    $(formEl).find('#no_nota').addClass('is-invalid')

                    return false;
                } else if ((tglAkhir == '' || tglAwal == '') && noNota == ''){
                    $.toast({
                        heading: 'Tidak Bisa Memproses Data!!',
                        text: 'Periode Kegiatan atau nota harus diisi',
                        position: 'top-right',
                        icon: 'warning',
                        hideAfter: 10000,
                    });
                    $(formEl).find('#start_date').addClass('is-invalid')
                    $(formEl).find('#end_date').addClass('is-invalid')
                    $(formEl).find('#no_nota').addClass('is-invalid')

                    return false;
                }
            }, false)
        })

    $('#transfer-simkeu').on('submit', function (e) {
        if (this.checkValidity()) {
            e.preventDefault();

            $('html, body').animate({
                scrollTop: $("#data-section").offset().top
            }, 1250);
            transferSIMKEU()
        }
    });

    $('#start_date').bootstrapMaterialDatePicker({
        weekStart: 0,
        time: false,
        clearButton: true,
    });
    $('#end_date').bootstrapMaterialDatePicker({
        weekStart: 0,
        time: false,
        clearButton: true,
    });
})

function generateNota() {
    let formEl = $('#transfer-simkeu')
    let tglAwal = $(formEl).find('#start_date').val()
    let tglAkhir = $(formEl).find('#end_date').val()
    let noNota = $(formEl).find('#no_nota').val()

    $(formEl).find('input').removeClass('is-invalid')
    if((tglAwal != '' || tglAkhir != '') && noNota != '') {
        $.toast({
            heading: 'Tidak Bisa Memproses Data!!',
            text: 'Periode Kegiatan atau nota harus diisi salah satu saja',
            position: 'top-right',
            icon: 'warning',
            hideAfter: 10000,
        });
        $(formEl).find('#start_date').addClass('is-invalid')
        $(formEl).find('#end_date').addClass('is-invalid')
        $(formEl).find('#no_nota').addClass('is-invalid')

        return false;
    } else if ((tglAkhir == '' || tglAwal == '') && noNota == ''){
        $.toast({
            heading: 'Tidak Bisa Memproses Data!!',
            text: 'Periode Kegiatan atau nota harus diisi',
            position: 'top-right',
            icon: 'warning',
            hideAfter: 10000,
        });
        $(formEl).find('#start_date').addClass('is-invalid')
        $(formEl).find('#end_date').addClass('is-invalid')
        $(formEl).find('#no_nota').addClass('is-invalid')

        return false;
    }

    ajaxGetJson(`/billing/transfer-simkeu/get-data?${$(formEl).serialize()}`, 'renderListData', 'get_error')
}

function renderListData(data)
{
    $("#data-body").html(data.view)
    $('#table-nota-list').DataTable()
    $('#data-section').slideDown()
}

function transferSIMKEU(){
    let form = $('#transfer-simkeu').serialize()
    ajaxPostJson('/billing/transfer-simkeu/transfer', form, 'input_success')
}

function input_success(res){
    if (res.status != 200) {
        input_error(res)
        return false
    }

    swal.close()
    $.toast({
        heading: 'Berhasil!',
        text: res.message,
        position: 'top-right',
        icon: 'success',
        hideAfter: 2500
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
