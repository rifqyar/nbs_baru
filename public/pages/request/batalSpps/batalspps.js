var table
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

    $('#form-add').on('submit', function (e) {
        if(this.checkValidity()){
            e.preventDefault();
            saveData('#form-add')
        }
        $(this).addClass('was-validated')

    });

    $('#start_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });
    $('#end_date').bootstrapMaterialDatePicker({ weekStart: 0, time: false });

    getData()
})

function getData(){
    table = $(".data-table").DataTable({
        responsive: true,
        scrollX: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${$('meta[name="baseurl"]').attr(
                "content"
            )}request/batal-spps/data`,
            method: "POST",
            data: function (data) {
                data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
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
            },
            {
                data: "no_ba",
                name: "no_ba",
                className: "text-center",
            },
            {
                data: "id_req_spps",
                name: "id_req_spps",
            },
            {
                data: "no_container",
                name: "no_container",
            },
            {
                data: "tgl_batal",
                name: "tgl_batal",
            },
            {
                data: "vessel",
                name: "vessel",
            },
            {
                data: "voyage_in",
                name: "voyage_in",
            },
        ],
        // fnDrawCallback: () => {
        //     const tooltipTriggerList = document.querySelectorAll(
        //         '[data-bs-toggle="tooltip"]'
        //     );
        //     const tooltipList = [...tooltipTriggerList].map(
        //         (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
        //     );
        // },
    });
}

$('#no_cont').autocomplete({
    source: function( request, response ) {
        $.ajax({
          url: `${$('meta[name="baseurl"]').attr("content")}request/batal-spps/data-container`,
          type: 'GET',
          dataType: "json",
          data: {
             search: request.term
          },
          success: function( data ) {
            response(data.map(function(value){
                return {
                    label: `${value.no_container} | \n
                            ${value.size_} | \n
                            ${value.type_} | \n
                            ${value.no_request}`,
                    no_cont: value.no_container,
                    SIZE_: value.size_,
                    TYPE_: value.type_,
                    STATUS_CONT: '',
                    STATUS: value.status,
                    VESSEL: value.vessel,
                    NO_REQUEST: value.no_request,
                    ID_UREQ: value.id_ureq,
                    NO_UKK: '',
                    LOCATION: value.location,
                };
            }));
          }
        });
    },
    select: function (event, ui) {
        $('#nc').val(ui.item.no_cont);
        $( "#sc" ).val( ui.item.SIZE_);
        $( "#tc" ).val( ui.item.TYPE_);
        $( "#stc" ).val( ui.item.STATUS_CONT);
        $( "#status" ).val( ui.item.STATUS);
        $( "#vessel" ).val( ui.item.VESSEL);
        $( "#voyage_in" ).val( ui.item.VOYAGE_IN);
        $( "#id_req" ).val( ui.item.NO_REQUEST);
        $( "#id_ureq" ).val( ui.item.ID_UREQ);
        $( "#no_ukk" ).val( ui.item.NO_UKK);
        $( "#cont_location" ).val( ui.item.LOCATION);
        return false;
    }
})

function saveData(formId)
{
    const form = $(formId).serialize()
    ajaxPostJson('/request/batal-spps/store', form, 'input_success')
}

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
                    html: "<h5>Berhasil input Request Batal SPPS,<br> Mengembalikan Anda ke halaman sebelumnya...</h5>",
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
