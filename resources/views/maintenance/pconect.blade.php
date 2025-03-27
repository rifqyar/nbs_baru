@extends('layouts.app')

@section('title')
    Pconect
@endsection

@section('content')
    @if (Session::has('notifCekPaid'))
        <div class="alert alert-danger fade show" role="alert">
            <h4 class="mb-0 text-center">
                <span class="text-size-5">
                    {!! Session::get('notifCekPaid') !!}
                </span>
                <i class="fas fa-exclamation-circle"></i>
            </h4>
        </div>
    @endif

    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Pconect</h3>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p>
                </h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="card card-secondary">
                        <div class="card-body">
                            <div class="card-title">
                                <h4>Form Pencarian</h4>
                            </div>
                            <form action="javascript:void(0)" id="search-data" class="m-t-40">
                                @csrf
                                <input type="hidden" name="search" value="false">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="col-12 col-md-12">
                                            <div class="form-group m-b-40">
                                                <label for="no_npwp">No. NPWP</label>
                                                <input type="text" class="form-control" name="no_npwp"
                                                    style="text-transform:uppercase" id="no_npwp">
                                                <span class="bar"></span>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-success btn-rounded mr-3" onclick="resetSearch()">
                                        Reset Pencarian
                                        <i class="mdi mdi-refresh"></i>
                                    </button>
                                    <button type="submit" class="btn btn-rounded btn-info">
                                        Cari Data
                                        <i class="mdi mdi-magnify"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card" id="data-section">
                        <div class="card-body">


                            <div class="table-responsive px-3">
                                <table class="display nowrap table data-table" cellspacing="0" width="100%"
                                    id="example">
                                    <thead>
                                        <tr>
                                            <th class="text-left">#</th>
                                            <th class="text-left">Nama</th>
                                            <th class="text-left">NPWP</th>
                                            <th class="text-left">Status</th>
                                            <th class="text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pages-js')
    <script>
        var table, no_npwp;
        var cachePBM = {};
        $(document).bind("keydown", function(e) {
            if (e.keyCode == 113) addContainer();
            return true;
        });

        $(function() {
            if ($("#data-section").length > 0 && $(".alert").css("display") == "none") {
                $("html, body").animate({
                        scrollTop: $("#data-section").offset().top,
                    },
                    1000
                );
            }

            setTimeout(() => {
                if (
                    $(".alert").css("display") != "none" &&
                    $("#data-section").length > 0
                ) {
                    $(".alert").alert("close");
                    $("html, body").animate({
                            scrollTop: $("#data-section").offset().top,
                        },
                        1250
                    );
                }
            }, 2500);


            var forms = document.querySelectorAll("form");
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener(
                    "submit",
                    function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                    },
                    false
                );
            });

            $("#search-data").on("submit", function(e) {
                if (this.checkValidity()) {
                    e.preventDefault();
                    $('input[name="search"]').val("true");
                    if ($("#search-data").find("input.form-control").val() == "") {
                        $('input[name="search"]').val("false");
                    }

                    $("html, body").animate({
                            scrollTop: $("#data-section").offset().top,
                        },
                        1250
                    );
                    table.ajax.reload(function() {
                        let data = table.ajax.json();
                        if (data.data && data.data.length > 0) {
                            input_success({
                                status: 200,
                                message: "Data ditemukan!"
                            });
                        } else {
                            input_error({
                                message: "Nomor NPWP belum terdaftar di pconect"
                            });
                        }
                    });
                }

                $(this).addClass("was-validated");
            });

            $("#request-header").on("submit", function(e) {
                if (this.checkValidity()) {
                    e.preventDefault();
                    saveEditData("#request-header");
                }
                $(this).addClass("was-validated");
            });

            getData();
        });

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
            )}maintenance/pconnect/data`,
                    method: "POST",
                    data: function(data) {
                        data._token = `${$('meta[name="csrf-token"]').attr("content")}`;
                        data.cari = $('input[name="search"]').val();
                        data.no_npwp = $('input[name="no_npwp"]').val();

                    },
                },
                columns: [{
                        data: "DT_RowIndex",
                        name: "DT_RowIndex",
                        className: "text-left",
                        width: "20px",
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: "nama",
                        name: "nama",
                    },
                    {
                        data: "npwp",
                        name: "npwp",
                    },
                    {
                        data: "status",
                        name: "status",
                    },
                    {
                        data: "action",
                        name: "action",
                        orderable: false,
                        searchable: false,
                        className: "text-left",
                        width: "200px",
                    },

                ],
            });
        }


        function resetSearch() {
            $("#search-data").find("input.form-control").val("").trigger("blur");
            $("#search-data").find("input.form-control").removeClass("was-validated");
            $('input[name="search"]').val("false");
            table.ajax.reload();
        }

        function insertData(no_npwp) {
            Swal.fire({
                title: "Insert Data",
                text: "Apakah Anda yakin ingin insert data pelanggan baru?",
                type: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Insert",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.value) {
                    var formData = new FormData();
                    formData.append("_token", $('input[name="_token"]').val());
                    formData.append("no_npwp", no_npwp);

                    ajaxPostFile(
                        "/maintenance/pconnect/insert",
                        formData,
                        "input_success"
                    );
                } else {
                    return false;
                }
            });
        }

        function updateData(no_npwp) {
            Swal.fire({
                title: "Update Data",
                text: "Apakah Anda yakin ingin update data pelanggan?",
                type: "question",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Update",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.value) {
                    var formData = new FormData();
                    formData.append("_token", $('input[name="_token"]').val());
                    formData.append("no_npwp", no_npwp);

                    ajaxPostFile(
                        "/maintenance/pconnect/update",
                        formData,
                        "input_success"
                    );
                } else {
                    return false;
                }
            });
        }
        /** End Of Other Function Section */
        /** ============================= */

        /** Pra Save Notif Section */
        /** ====================== */
        function input_success(res) {
            if (res.status != 200) {
                input_error(res);
                return false;
            }

            $.toast({
                heading: "Berhasil!",
                text: res.message,
                position: "top-right",
                icon: "success",
                hideAfter: 2500,
                beforeHide: function() {
                    if (res.redirect?.need) {
                        Swal.fire({
                            html: "<h5>Berhasil Memproses Data <br> Mengembalikan Anda ke halaman sebelumnya...</h5>",
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });

                        Swal.showLoading();
                    } else {
                        return false;
                    }
                },
                afterHidden: function() {
                    if (res.redirect?.need) {
                        window.location.href = res.redirect.to;
                    } else {
                        return false;
                    }
                },

            });
        }

        function input_error(err) {
            $.toast({
                heading: "Gagal memproses data!",
                text: err.message,
                position: "top-right",
                icon: "error",
                hideAfter: 5000,
            });

            Swal.close()
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
        /** End Of Pra Save Notif Section */
        /** ============================= */
    </script>
@endsection
