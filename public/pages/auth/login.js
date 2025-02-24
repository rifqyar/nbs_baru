$( function(){
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

    // $("input,select,textarea").not("[type=submit]").jqBootstrapValidation()

    $('#loginform').on('submit', function (e) {
        if (this.checkValidity()) {
            e.preventDefault();
            // console.log('masuk')
            login_process()
        }

        $(this).addClass('was-validated');

    });
})

function login_process(){
    var form = $('#loginform').serialize()
    ajaxPostJson('login-process', form, 'success_login', 'error_login')
}

function success_login(data) {
    if (data.status != 200) {
        if(data.message == 'CSRF token mismatch'){
            $.toast({
                heading: 'Login Gagal!',
                text: data.message + ', Halaman akan reload otomatis',
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'error',
                hideAfter: 5000,
                afterHidden: function () {
                    window.location.reload()
                }
            });
        } else {
            $.toast({
                heading: 'Login Gagal!',
                text: data.message,
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'error',
                hideAfter: 5000,
            });
        }
        return
    }

    Swal.fire({
        title: 'Berhasil!',
        text: 'Berhasil login!',
        type: 'success',
        showConfirmButton: false,
    });

    location.href = '/';
}

function error_login(err) {
    console.log(err);
}
