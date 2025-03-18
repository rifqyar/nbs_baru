@extends('layouts.app')

@section('title')
Perencanaan Kegiatan Delivery
@endsection

@section('pages-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">New Request</a></li>
            <li class="breadcrumb-item">Delivery</li>
            <li class="breadcrumb-item"><a href="{{route('uster.new_request.delivery.delivery_luar.index')}}">Delivery Ke Luar</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
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
                <h3><b>Request Delivery - SP2 ke LUAR DEPO</b></h3>
                <div class="border rounded my-3">
                    <form id="dataCont">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No Request: </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="NO_REQ" id="NO_REQ" class="form-control" value="{{ $data['row_request']->no_request}}" readonly>
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Consignee : </label>
                                </div>
                                <div class="col-md-4">
                                    <!-- <input type="text" name="EMKL" id="EMKL" class="form-control" value="{{ $data['row_request']->nama_emkl}}"> -->
                                    <select name="EMKL" id="EMKL" class="form-control w-100">
                                        <option value="{{ $data['row_request']->nm_agen}}" selected> {{ $data['row_request']->nama_emkl}} </option>
                                    </select>
                                    <input type="hidden" name=" " id="BP_ID" class="form-control" value="{{ $data['row_request']->bp_id ?? ''}}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Penumpukan Oleh : </label>
                                </div>
                                <div class="col-md-4">
                                    <!-- <input type="text" id="NM_AGEN" name="NM_AGEN" class="form-control" value="{{ $data['row_request']->nm_agen}}"> -->
                                    <select name="NM_AGEN" id="NM_AGEN" class="form-control w-100">
                                        <option value="{{ $data['row_request']->nm_agen}}" selected> {{ $data['row_request']->nm_agen}} </option>
                                    </select>
                                    <input type="hidden" id="KD_AGEN" name="KD_AGEN" class="form-control" value="{{ $data['row_request']->kd_agen}}">
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Alamat : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="alamat" id="ALAMAT" class="form-control" value="{{ $data['row_request']->alamat}}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Keterangan : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="keterangan" class="form-control" value="{{$data['row_request']->keterangan}}">
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Npwp : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="npwp" id="NPWP" class="form-control" value="{{$data['row_request']->npwp}}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No RO : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" id="NO_RO" name="NO_RO" class="form-control" value="{{$data['row_request']->no_ro}}">
                                </div>
                            </div>

                        </div>
                    </form>
                    <div class="row text-center mb-3">
                        <div class="col">
                            <button onclick="save()" class="btn btn-info">Simpan</button>
                        </div>
                    </div>
                </div>

                <div class="border rounded my-3">
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor Container: </label>
                            </div>
                            <div class="col-md-4">
                                <!-- <input type="text" name="NO_CONT" ID="NO_CONT" class="form-control" placeholder="AUTO COMPLETE"> -->
                                <select name="NO_CONT" id="NO_CONT" class="form-control w-100"></select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Ukuran : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="SIZE" id="SIZE" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Status : </label>
                            </div>
                            <div class="col-md-4">
                                <select id="STATUS" name="STATUS" class="form-control">
                                    <option value='MTY'>MTY</option>
                                    <option value='FCL'>FCL</option>
                                </select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">TIPE : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="TYPE" id="TYPE" readonly="readonly" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">HZ <font color="red">*</font>: </label>
                            </div>
                            <div class="col-md-4">
                                <select name="HZ" id="HZ" class="form-control">
                                    <option value="N">N</option>
                                    <option value="Y">Y</option>
                                </select>

                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Komoditi : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="komoditi" id="komoditi" class="form-control w-100">
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">VIA : </label>
                            </div>
                            <div class="col-md-4">
                                <select name="via" id="via" class="form-control">
                                    <option value="darat">DARAT</option>
                                    <option value="tongkang">TONGKANG</option>
                                    <option value="ship_side">SHIP-SIDE</option>
                                </select>
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Berat : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="berat" ID="berat" class="form-control" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Seal : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="no_seal" ID="no_seal" class="form-control" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Keterangan : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="keterangan" ID="keterangan" class="form-control" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Start Pnkn : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="start_pnkn" ID="start_pnkn" class="form-control" />
                                <input type="hidden" name="asal_cont" ID="asal_cont" class="form-control" />
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">End Pnkn : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="date" name="end_pnkn" ID="end_pnkn" class="form-control" />
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col">
                                <button class="btn btn-info" onclick="add_cont()">Tambahkan Container</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-2">
                    <div class="table-responsive">
                        <table class="datatables-service table " id="container-table">
                            <thead>
                                <tr>
                                    <th>No </th>
                                    <th>No Container</th>
                                    <th>Status </th>
                                    <th>Ukuran</th>
                                    <th>Tipe</th>
                                    <th>Komoditi</th>
                                    <th>No Seal</th>
                                    <th>Berat</th>
                                    <th>Via</th>
                                    <th>Yard</th>
                                    <th>Hz</th>
                                    <th>Tgl Awal Stack</th>
                                    <th>Tgl Delivery</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                    <div id="button_save" class="text-center my-3">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pages-js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#EMKL').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.pbm") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.kd_pbm,
                            text: arr.nm_pbm + ' | ' + arr.almt_pbm + ' | ' + arr.no_npwp_pbm,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#EMKL').on('select2:select', function(e) {
            var data = e.params.data;
            $("#EMKL").val(data.nm_pbm);
            $("#ID_EMKL").val(data.kd_pbm);
            $("#ALAMAT").val(data.almt_pbm);
            $("#NPWP").val(data.no_npwp_pbm);
        });


        $('#NM_AGEN').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.pbm") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.kd_pbm,
                            text: arr.nm_pbm + ' | ' + arr.almt_pbm,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NM_AGEN').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NM_AGEN").val(data.nm_pbm);
            $("#KD_AGEN").val(data.kd_pbm);
        });


        $('#komoditi').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.commodity") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.nm_commodity,
                            text: arr.nm_commodity,
                            ...arr
                        }))
                    };
                }
            }
        });

        var table = $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.new_request.delivery.delivery_luar.editdatatable") !!}',
                type: 'GET',
                data: function(d) {
                    d.noReq = $('#NO_REQ').val();
                }
            },
            columns: [{
                    data: 'DT_RowIndex', // Use the special 'DT_RowIndex' property provided by DataTables
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        // Render the sequence number
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'no_container',
                    name: 'no_container'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'size_',
                    name: 'size_'
                },
                {
                    data: 'type_',
                    name: 'type_'
                },
                {
                    data: 'komoditi',
                    name: 'komoditi'
                },
                {
                    data: 'no_seal',
                    name: 'no_seal'
                },
                {
                    data: 'berat',
                    name: 'berat'
                },
                {
                    data: 'via',
                    name: 'via'
                },
                {
                    data: 'nama_yard',
                    name: 'nama_yard'
                },
                {
                    data: 'hz',
                    name: 'hz'
                },
                {
                    data: 'start_stack',
                    name: 'start_stack'
                },

                {
                    data: 'tgl_delivery',
                    name: 'tgl_delivery',
                    render: function(data, type, row, meta) {
                        // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                        id = 'tgl_delivery_' + (meta.row + 1);
                        return '<input type="date" value=' + data + ' id=' + id + ' name=' + id + ' />';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        noReq = data['no_request'];
                        return '<button value="Hapus" onclick="del_cont(\'' + data['no_container'] + '\',' + data['ex_bp_id'] + ',' + meta.row + ')" class="btn btn-danger"> Hapus </button>';
                    }
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                $(row).append('<input type="hidden" id="NO_CONT_' + (rowIdx + 1) + '" class="hidden-input" value="' + data.no_container + '">');
            },
            lengthMenu: [10, 20, 50, 100, 200], // Set the default page lengths
            pageLength: 200, // Set the initial page length
            initComplete: function() {
                var table = $('#container-table').DataTable();
                var totalRows = table.rows().count();
                $('#button_save').html('<button type="button" onclick="save_tgl_delivery(' + totalRows + ')" value="Save" class="btn btn-info">Save</button>');
            }
        });

        // Update the totalRows value dynamically on each draw event
        table.on('draw', function() {
            var totalRows = table.rows().count();
            $('#button_save').html('<button type="button" onclick="save_tgl_delivery(' + totalRows + ')" value="Save" class="btn btn-info">Save</button>');
        });


        $('#NO_CONT').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.new_request.delivery.delivery_luar.getnocontainer") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_container,
                            text: arr.no_container + ' | ' + arr.status,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#NO_CONT').on('select2:select', function(e) {
            var data = e.params.data;
            $("#NO_CONT").val(data.no_container);
            $("#SIZE").val(data.size_);
            $("#TYPE").val(data.type_);
            $("#STATUS").val(data.status);
            $("#BP_ID").val(data.bp_id);
            $("#start_pnkn").val(data.tgl_bongkar);
            $("#end_pnkn").val(data.tgl_end);
            $("#asal_cont").val(data.asal);
            if ($("#asal_cont").val() == 'UST') {
                var csrfToken = '{{ csrf_token() }}';
                $.ajax({
                    url: '{!! route("uster.new_request.delivery.delivery_luar.gettglstack") !!}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        noCont: data.no_container
                    },
                    success: function(response) {
                        // var datax = $.parseJSON(data);
                        console.log(response.tgl_bongkar);
                        $("#start_pnkn").prop('value', response.tgl_bongkar);
                        $("#end_pnkn").prop('value', response.empty_sd);
                    },
                    error: function(error) {
                        console.error(error);

                    }
                });
            }
        })
    });


    ///==================================================///


    function add_cont() {
        var no_cont_ = $("#NO_CONT").val();
        var hz_ = $("#HZ").val();
        var no_req_ = $("#NO_REQ").val();
        var status_ = $("#STATUS").val();
        var komoditi_ = $("#komoditi").val();
        var keterangan_ = $("#keterangan").val();
        var no_seal_ = $("#no_seal").val();
        var berat_ = $("#berat").val();
        var via_ = $("#via").val();
        var SIZE = $("#SIZE").val();
        var TYPE = $("#TYPE").val();
        var start_pnkn = $("#start_pnkn").val();
        var end_pnkn = $("#end_pnkn").val();
        var bp_id = $("#BP_ID").val();
        var asal_cont = $("#asal_cont").val();

        var url = '{!! route("uster.new_request.delivery.delivery_luar.addcont") !!}';
        var csrfToken = '{{ csrf_token() }}';

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: csrfToken,
                start_pnkn: start_pnkn,
                end_pnkn: end_pnkn,
                KETERANGAN: keterangan_,
                NO_SEAL: no_seal_,
                BERAT: berat_,
                VIA: via_,
                KOMODITI: komoditi_,
                NO_CONT: no_cont_,
                NO_REQ: no_req_,
                STATUS: status_,
                HZ: hz_,
                SIZE: SIZE,
                TYPE: TYPE,
                BP_ID: bp_id,
                ASAL_CONT: asal_cont
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading...',
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(data) {
                Swal.close();
                if (data == "NOT_EXIST") {
                    sAlert('Gagal!', 'Container Belum Terdaftar', 'error');
                } else if (data == "TGL_TUMPUK") {
                    sAlert('Gagal!', 'Tanggal mulai penumpukan masih kosong', 'error');
                } else if (data == "GAGAL") {
                    sAlert('Gagal!', 'Gagal insert no Container', 'error');
                } else if (data == "con_yard") {
                    sAlert('Gagal!', 'Container Belum Placement', 'error');
                } else if (data == "OK") {
                    sAlert('Berhasil!', 'Berhasil menambahkan container', 'success');
                } else if (data == "BLM_PLACEMENT") {
                    sAlert('Gagal!', 'Container Belum Placement', 'error');
                } else if (data == "SDH_REQUEST") {
                    sAlert('Gagal!', 'Container Sudah Mengajukan Request Delivery', 'error');
                }

                if (data.status['code'] == 200) {
                    sAlert('Berhasil!', data.status['msg'], 'success');
                } else {
                    sAlert('Gagal!', data.status['msg'], 'danger');
                }

                $('#container-table').DataTable().ajax.reload();
                window.location.reload()
            },
            error: function(xhr, status, error) {
                Swal.close();
                sAlert('Gagal!', 'Terjadi kesalahan saat mengirim data', 'error');
            },
            complete: function() {
                $("#NO_CONT").val('');
                // $("#HZ").val('');
                $("#STATUS").val('');
                $("#komoditi").val('');
                $("#keterangan").val('');
                $("#no_seal").val('');
                $("#berat").val('');
                // $("#via").val('');
                $("#start_pnkn").val('');
                $("#end_pnkn").val('');
            }
        });
    }

    function del_cont($no_cont, $bp_id = '', rowId) {
        var no_req_ = "{{ $data['row_request']->no_request }}";
        var csrfToken = '{{ csrf_token() }}';
        $.post('{!! route("uster.new_request.delivery.delivery_luar.delcont") !!}', {
            _token: csrfToken,
            NO_CONT: $no_cont,
            NO_REQ: no_req_,
            BP_ID: $bp_id
        }, function(data) {
            if (data.status['code'] == 200) {
                sAlert('Berhasil!', data.status['msg'], 'success');
                var table = $('#container-table').DataTable();
                table.row($('#container-table tbody tr:contains(' + rowId + ')')).remove().draw();
            } else {
                sAlert('Gagal!', data.status['msg'], 'danger');
            }
        });
    }

    function sAlert(title, msg, type) {
        let timerInterval;
        Swal.fire({
            title: title,
            timer: 1500,
            text: msg,
            type: type,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    function save() {

        var formData = $('#dataCont').serialize();

        $.ajax({
            url: '{!! route("uster.new_request.delivery.delivery_luar.savedataedit") !!}', // Ganti dengan URL yang sesuai
            type: 'POST', // Ganti dengan metode HTTP yang sesuai (GET/POST)
            data: formData,
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

    function save_tgl_delivery(total) {
        var url = '{!! route("uster.new_request.delivery.delivery_luar.updatedatadelivery") !!}';
        var no_request = $("#NO_REQ").val();

        for (var i = 1; i <= total; i++) {
            (function(i) {
                var no_cont = $("#NO_CONT_" + i).val();
                var tgl_delivery = $("#tgl_delivery_" + i).val();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        NO_REQ: no_request,
                        TGL_DELIVERY: tgl_delivery,
                        NO_CONT: no_cont,
                        INDEX: i,
                        TOTAL: total,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Loading...',
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(data) {
                        if (data.status['code'] == 200) {
                            sAlert('Berhasil!', data.status['msg'], 'success');
                        } else {
                            sAlert('Gagal!', data.status['msg'], 'danger');
                        }

                        $('#container-table').DataTable().ajax.reload();
                    },
                    error: function(xhr, status, error) {
                        sAlert('Error!', 'Failed to update data', 'error');
                    },
                    complete: function() {
                        if (i === total) {
                            Swal.close();
                            sAlert('Success!', 'Tanggal Delivery Berhasil Disimpan', 'success');
                        }
                    }
                });
            })(i);
        }
    }
</script>
@endsection
