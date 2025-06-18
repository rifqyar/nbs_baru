
$(function (){
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

    $('#dataCont').on('submit', function(e){
        if (this.checkValidity()) {
            e.preventDefault();
            batal('#dataCont')
        }
        $(this).addClass('was-validated');
    })
})


$( "#NO_CONT" ).autocomplete({
    source: function( request, response ) {
        $.ajax({
          url: `${$('meta[name="baseurl"]').attr("content")}koreksi/batal-stripping/data-cont`,
          type: 'GET',
          dataType: "json",
          data: {
             search: request.term
          },
          success: function( data ) {
            response(data.map(function(value){
                return {
                    label: `${value.no_container} | ${value.size_} ${value.type_}`,
                    NO_CONTAINER: value.no_container,
                    SIZE_: value.size_,
                    TYPE_: value.type_,
                    VIA: value.via,
                    NO_REQUEST: value.no_request,
                    TGL_REQUEST: value.tgl_request,
                    STRIPPING_DARI: value.stripping_dari,
                    LUNAS: value.lunas,
                    NO_REQUEST_RECEIVING: value.no_request_receiving,
                };
            }));
          },error: function(){
            $.toast({
                heading: "Gagal Mengambil Data",
                position: "top-right",
                loaderBg: "#ff6849",
                icon: "error",
                hideAfter: 3500,
            });
          }
        });
    },
    select: function (event, ui) {
        $( "#NO_CONT" ).val( ui.item.NO_CONTAINER );
        $( "#SIZE" ).val( ui.item.SIZE_);
        $( "#TYPE" ).val( ui.item.TYPE_);
        $( "#VIA" ).val( ui.item.VIA);
        $( "#NO_REQ" ).val( ui.item.NO_REQUEST );
        $( "#TGL_REQ" ).val( ui.item.TGL_REQUEST );
        $( "#STRIPPING_DARI" ).val(ui.item.STRIPPING_DARI);
        $( "#LUNAS" ).val(ui.item.LUNAS);
        $( "#NO_REQUEST_RECEIVING" ).val(ui.item.NO_REQUEST_RECEIVING);

        return false;
    }
});

function batal(){
    Swal.fire({
        title: 'Apakah anda yakin ingin melakukan batal stripping pada Container ini?',
        type: 'question',
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, batal container"
    }).then(async (conf) => {
        if(conf.value == true){
            const formData = $('#dataCont').serialize()
            await ajaxPostJson(`/koreksi/batal-stripping/batal-container`, formData, 'input_success')
        } else {
            return false;
        }
    });
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
            Swal.fire({
                html: "<h5>Berhasil Batal Container Stripping</h5>",
                showConfirmButton: false,
                allowOutsideClick: false,
            });

            Swal.showLoading();
        },
        afterHidden: function () {
            if(res.redirect.need){
                console.log(res.redirect.to)
                window.location.href = res.redirect.to
            } else {
                window.location.reload()
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
