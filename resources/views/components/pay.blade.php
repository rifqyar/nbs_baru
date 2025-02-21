<form action="{{ $route }}" enctype="multipart/form-data" id="{{$formId}}">
    @csrf
    <input type="hidden" name="upt_id" class="form-control" id="idS1-2">
    <input name="_method" type="hidden" value="{{$formMethod}}">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="titleS1">No. Proforma</label>
                <input type="text" name="title" class="form-control" value="{{$dt_item['mti_nota']}}" readonly>
                <!-- <small id="title_error" class="title_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">No. Request</label>
                <input type="text" name="title" class="form-control" value="{{$dt_item['req']}}" readonly>
                <!-- <small id="description_error" class="description_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">Jenis Nota</label>
                <input type="text" name="title" class="form-control" value="{{$dt_item['jenis']}}" readonly>
                <!-- <small id="description_en_error" class="description_en_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">Bayar Melalui</label>
                <select name="" id="kd_pelunasan" class="form-control" onchange="get_paid_via()">
                    <option value="BANK">BANK</option>
                    <!--<option value="CASH">CASH</option> -->
                    <!-- <option value="AUTODB">AUTODB</option>
                    <option value="DEPOSIT">DEPOSIT</option> -->
                </select>
                <!-- <small id="description_en_error" class="description_en_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">Paid Via</label>
                <div id="paid_via"></div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">No. Peraturan</label>
                <input type="text" name="title" class="form-control" value="{{$dt_item['no_mat']}}" readonly>
                <!-- <small id="description_en_error" class="description_en_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="descriptionS1">Total</label>
                <input type="text" name="title" class="form-control" value="Rp. {{$dt_item['total']}}" readonly>
                <!-- <small id="description_en_error" class="description_en_error input-group text-sm mt-2 text-danger error"></small> -->
            </div>
        </div>
        <button type="button" onclick="save_payment()" class="btn btn-primary m-2"> Paid </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        get_paid_via();
    });

    function get_paid_via() {
        var kd_pelunasan = $("#kd_pelunasan").val();
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
        });

        var url = '{!! route("uster.billing.paymentcash.paidview") !!}' + '?idn=' + '{{!!$dt_item["nota"]!!}}' + '&tgl=' + '{{!!$dt_item["tgl"]!!}}';
        $.ajax({
            url: url,
            type: "GET",
            processData: false,
            contentType: false,
            success: function(data) {
                data = JSON.parse(data);
                if (data["status"] == "success") {
                    $("#paid_via").html(data["output"]);
                } else {
                    Toast.fire({
                        icon: "error",
                        title: data["message"],
                    });
                }
            },
            error: function(reject) {
                Toast.fire({
                    icon: "error",
                    title: "Something went wrong",
                });
            },
        });
    }

    function save_payment() {
        var csrfToken = '{{ csrf_token() }}';
        var bankid = $('#via').val();
        var kd_pelunasan = $("#kd_pelunasan").val();
        var url = '{!! route("uster.billing.paymentcash.savepaymentpraya") !!}'
        if (kd_pelunasan == 0) {
            alert('Please Choose Payment Method');
            return false;
        } else {
            question = confirm("data akan ditransfer, cek apakah data sudah benar?")
            if (question != "0") {
                $.ajax({
                    url: url, // Ganti dengan URL yang sesuai
                    type: 'POST', // Ganti dengan metode HTTP yang sesuai (GET/POST)
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        IDN: '{{!!$dt_item["nota"]!!}}',
                        IDR: '{{!!$dt_item["req"]!!}}',
                        JENIS: '{{!!$dt_item["jenis"]!!}}',
                        BANK_ID: bankid,
                        VIA: kd_pelunasan,
                        EMKL: '{{!!$dt_item["emkl"]!!}}',
                        KOREKSI: '{{!!$dt_item["koreksi"]!!}}',
                        JUM: '{{!!$dt_item["jum"]!!}}',
                        MTI: '{{!!$dt_item["mti_nota"]!!}}',
                        NO_PERATURAN: '{{!!$dt_item["no_mat"]!!}}'
                    },
                    beforeSend: function() {
                        // Tampilkan pesan SweetAlert sebelum permintaan dikirim
                        Swal.fire({
                            title: 'Loading...',
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        // Sembunyikan pesan SweetAlert setelah permintaan berhasil
                        Swal.close();
                        // Tampilkan pesan sukses menggunakan SweetAlert
                        if (data.status['code'] == 200) {
                            sAlert('Berhasil!', data.status['msg'], 'success');
                        } else {
                            sAlert('Gagal!', data.status['msg'], 'danger');
                        }
                        // Lakukan tindakan tambahan sesuai kebutuhan, misalnya memperbarui tampilan
                    },
                    error: function(xhr, status, error) {
                        // Sembunyikan pesan SweetAlert setelah permintaan gagal
                        Swal.close();
                        // Tampilkan pesan error menggunakan SweetAlert
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Ada masalah saat menyimpan data',
                        });
                        // Lakukan penanganan kesalahan tambahan sesuai kebutuhan
                    }
                });
            }
        }
    }
</script>