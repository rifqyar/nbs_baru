@extends('layouts.app')

@section('title')
TID & Container Association
@endsection

@section('pages-css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="row page-titles">
    <div class="col-md-5 col-8 align-self-center">
        <h3 class="text-themecolor">Dashboard</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">TCA</a></li>
            <li class="breadcrumb-item active">TID & Container Association</li>
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
                <h3><b>TID & Container Association</b></h3>
                <div class="border rounded my-3">
                    <center>
                        <form id="dataCont">
                            @csrf
                            <div class="p-3">
                                <div class="">
                                    <div class="col-md-4 py-2">
                                        <label for="tb-fname">No. Invoice / No. Request : </label>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="trx_number" id="trx_number" class="form-control"></select>
                                        <input type="hidden" name="no_req" id="no_req">
                                    </div>
                                </div>
                            </div>
                        </form>
                        <button onclick="generate()" class="btn btn-danger mr-2 m-3"><i class="fas fa-file-alt pr-2"></i> Generate</button>
                    </center>
                </div>
                <div class="card p-2">
                    <div class="table-responsive">
                        <table class="datatables-service table " id="container-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NO CONTAINER</th>
                                    <th>SIZE</th>
                                    <th>TRUCK ID</th>
                                    <th>POLICE NUMBER</th>
                                    <th>REMARKS</th>
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
        $('#trx_number').select2({
            minimumInputLength: 3, // Set the minimum input length
            ajax: {
                // Implement your AJAX settings for data retrieval here
                url: '{!! route("uster.tca.tidcontainer.invoicenumberpraya") !!}',
                dataType: 'json',
                processResults: function(data) {
                    const arrs = data;
                    return {
                        results: arrs.map((arr, i) => ({
                            id: arr.no_nota,
                            text: arr.no_nota + " - " + arr.no_request + " - " + arr.emkl,
                            ...arr
                        }))
                    };
                }
            }
        });

        $('#trx_number').on('select2:select', function(e) {
            var data = e.params.data;
            $("#trx_number").val(data.no_nota);
            $("#no_req").val(data.no_request);

        })

    });

    function generate() {
        if ($.fn.DataTable.isDataTable('#container-table')) {
            var table = $('#container-table').DataTable();
            table.destroy();
        }

        var csrfToken = '{{ csrf_token() }}';

        $('#container-table').DataTable({
            // responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route("uster.tca.tidcontainer.datatable") !!}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    NO_REQ: $("#no_req").val()
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
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return data['containerNo'];
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return '<input type="text" id="size_' + rowIdx + '" class="hidden-input" value="' + data['containerSize'] + '">';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return '<input type="text" name="truckid[]" id="truckid_' + rowIdx + '" class="hidden-input">';

                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return '<input type="text" name="trucknum_' + rowIdx + '" id="trucknum_' + rowIdx + '" class="hidden-input" readonly' +
                            '<input type="hidden" name="total" id="total" class="hidden-input" value="' + data['jum'] + '">' +
                            '<input type="hidden" name="id_req" id="id_req" class="hidden-input" value="' + data['id_req'] + '">' +
                            '<input type="hidden" name="trucktype_' + rowIdx + '" id="trucktype_' + rowIdx + '" class="hidden-input">' +
                            '<input type="hidden" name="isocode_' + rowIdx + '" id="isocode_' + rowIdx + '" class="hidden-input" value="' + data['isoCode'] + '">' +
                            '<input type="hidden" name="pod_' + rowIdx + '" id="pod_' + rowIdx + '" class="hidden-input" value="' + data['pod'] + '">' +
                            '<input type="hidden" name="weight_' + rowIdx + '" id="weight_' + rowIdx + '" class="hidden-input" value="' + data['weight'] + '">';

                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return '<div id="message_' + rowIdx + '"</div>';

                    }
                },
            ],
            rowCallback: function(row, data, rowIdx) {
                // Add hidden input field to each row
                $(row).append('<input type="hidden" name="nocont[]" id="nocont_' + rowIdx + '" class="hidden-input" value="' + data.containerNo + '">');
                $(row).append('<input type="hidden" id="trailertype_' + rowIdx + '" class="hidden-input" value="40FT4A">');
            },
            lengthMenu: [10, 20, 50, 100], // Set the default page lengths
            pageLength: 10, // Set the initial page length
            initComplete: function() {
                var table = $('#container-table').DataTable();
                var totalRows = table.rows().count();
                $('#button_save').html('<button type="button" onclick="save_associate()" value="Save" class="btn btn-info">Save</button>');

                for (var rowid = 0; rowid < totalRows; rowid++) {
                    $("#truckid_" + rowid).select2({
                        minimumInputLength: 3, // Set the minimum input length
                        ajax: {
                            // Implement your AJAX settings for data retrieval here
                            url: '{!! route("uster.tca.tidcontainer.gettrucklist") !!}',
                            dataType: 'json',
                            processResults: function(data) {
                                const arrs = data;
                                return {
                                    results: arrs.map((arr, i) => ({
                                        id: arr.tid,
                                        text: arr.tid + " - " + arr.truckNumber + " - " + arr.company,
                                        ...arr
                                    }))
                                };
                            }
                        }
                    });

                    $("#truckid_" + rowid).on('select2:select', function(e) {
                        var data = e.params.data;
                        $("#truckid_" + rowid).val(data.tid);
                        $("#trucknum_" + rowid).val(data.truckNumber);
                        $("#trucktype_" + rowid).val(data.truckType);
                    })
                }
            }
        });
    }

    function isset(variable) {
        return variable !== null && variable !== undefined;
    }

    function save_associate() {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to recalculate ' + req + ' . This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, recalculate',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: 'Menambahkan Data Container...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                var url = '{!! route("uster.tca.tidcontainer.saveassociatepraya") !!}';
                var request_number = $('#id_req').val();
                var idx = 0;
                var tids = [];
                var created_by = "Admin Uster";
                var action_code = "R";
                var datas = [];
                var id_req = $('#id_req').val();
                $.each($('[id^=nocont_]'), function(idx, item) {
                    var cont_no = $(this).val();
                    var iso_code = $("#isocode_" + idx).val();
                    var pod = $("#pod_" + idx).val();
                    var weight = $("#weight_" + idx).val();
                    var size = $("#size_" + idx).val();
                    var truck_number = $("#trucknum_" + idx).val();
                    var truck_id = $("#truckid_" + idx).val();
                    tids[idx] = truck_id;
                    var truck_type = $("#trucktype_" + idx).val();
                    var axle = $("#trailertype_" + idx).val();

                    datas.push({
                        index: idx,
                        no_container: cont_no,
                        iso_code,
                        pod,
                        weight,
                        size,
                        truck_number,
                        tid: truck_id,
                        truck_type,
                        axle
                    })

                    idx++;
                });

                var unique_tids = tids.filter(function(element, index, self) {
                    return element && index === self.indexOf(element);
                });
                var multiple = "N";
                if (!unique_tids.length) {
                    alert("BELUM ADA TRUCK ID YANG DI INPUT");
                    return;
                }
                if (unique_tids.length > 1) {
                    multiple = "Y";
                }

                var err = false;
                for (var i = 0; i < unique_tids.length; i++) {
                    var count = 0;
                    var axles = [];
                    var sizes = [];
                    for (var j = 0; j < datas.length; j++) {
                        if (datas[j].tid && datas[j].tid == unique_tids[i]) {
                            count++;
                            axles.push(datas[j].axle);
                            sizes.push(datas[j].size);
                        }
                    }
                    if (count > 2) {
                        alert("TRUCK ID DOUBLE SILAHKAN CHECK TRUCK ID");
                        err = true;
                        break;
                    }
                    if (count == 2 && (axles[0].substr(0, 2) == 20 || axles[1].substr(0, 2) == 20)) {
                        alert("TIDAK BISA MELAKUKAN TCA COMBO SILAHKAN CHECK TRAILER TYPE");
                        err = true;
                        break;
                    }
                    // combo hanya bisa 2 container size 20 
                    if (count == 2 && (sizes[0] == 40 || sizes[1] == 40)) {
                        alert("TIDAK BISA MELAKUKAN TCA COMBO SILAHKAN CHECK TRAILER TYPE");
                        err = true;
                        break;
                    }
                    if (count == 2 && axles[0] != axles[1]) {
                        alert("TIDAK BISA MELAKUKAN TCA COMBO TRAILER TYPE TIDAK SESUAI SILAHKAN CHECK TRAILER TYPE");
                        err = true;
                        break;
                    }
                }
                if (err) return;

                var payload;
                var csrfToken = '{{ csrf_token() }}';
                if (multiple == "N") {
                    var detail = [];
                    var truckType;
                    var truckNumber;
                    var axle;
                    datas.forEach(e => {
                        if (unique_tids[0] == e.tid) {
                            detail.push({
                                requestNumber: id_req,
                                containerNo: e.no_container,
                                pod: e.pod,
                                weight: e.weight,
                                isoCode: e.iso_code
                            })
                            truckType = e.truck_type
                            truckNumber = e.truck_number
                            axle = e.axle
                        }
                    });
                    payload = {
                        _token: csrfToken,
                        truckType,
                        truckNumber,
                        createdBy: created_by,
                        tid: unique_tids[0],
                        axle,
                        type: "OEC",
                        actionCode: action_code,
                        detail
                    };
                } else {
                    var arr = [];
                    unique_tids.forEach((value, index) => {
                        var data = {
                            createdBy: created_by,
                            tid: value,
                            type: "OEC",
                            actionCode: action_code,
                            detail: []
                        };
                        for (var i = 0; i < datas.length; i++) {
                            if (datas[i].tid == value) {
                                data['truckType'] = datas[i]['truck_type'];
                                data['truckNumber'] = datas[i]['truck_number'];
                                data['axle'] = datas[i]['axle'];
                                data['detail'].push({
                                    requestNumber: request_number,
                                    containerNo: datas[i]['no_container'],
                                    pod: datas[i]['pod'],
                                    weight: datas[i]['weight'] || 0,
                                    isoCode: datas[i]['iso_code'],
                                })
                            }

                        }
                        arr.push(data);
                    });

                    payload = {
                        _token: csrfToken,
                        multiple: "Y",
                        data: arr
                    };
                }

                $('#button_save').html('<button type="button" onclick="save_associate()" value="Save" class="btn btn-info">Save</button>');

                // console.log(payload);
                // return

                $.post(url, payload, function(data) {
                    xdata = JSON.parse(data);
                    // console.log(xdata);
                    // General Error & Tidak ke hit
                    if (xdata.code == 0 && xdata.msg && !xdata.dataRec) {
                        alert(xdata.msg);
                        $('#button_save').html('<button type="button" onclick="save_associate()" value="Save" class="btn btn-info">Save</button>');
                        return;
                    }
                    // Jika Masuk
                    if (multiple == 'N') {
                        if (xdata.code == 0) {
                            alert(xdata.msg);
                        }
                        if (xdata.hasOwnProperty("dataRec")) {
                            var affectedIdx = [];
                            datas.forEach((e) => {
                                if (payload.tid == e.tid) {
                                    affectedIdx.push(e.index);
                                }
                            });
                            affectedIdx.forEach((e) => {
                                if (xdata.code == 0) {
                                    $("#message_" + e).html("<font>" + xdata.msg + "<font/>");
                                } else {
                                    $("#message_" + e).html("<font>" + xdata.msg + "<font/>");

                                }
                            })
                        }
                    } else {
                        if (xdata.code == 0) {
                            var err = [];
                            xdata.dataRec?.failedTid.forEach(element => {
                                err.push(element.tid + " : " + element.message)
                            });
                            var errs = err.join("\n");
                            alert(errs);
                            var successIdx = [];
                            var failedIdx = [];
                            datas.forEach((e) => {
                                xdata.dataRec?.successTid.forEach(success => {
                                    if (success.tid == e.tid) {
                                        successIdx.push({
                                            index: e.index,
                                            msg: success.message
                                        });
                                    }
                                });
                                xdata.dataRec?.failedTid.forEach(failed => {
                                    if (failed.tid == e.tid) {
                                        failedIdx.push({
                                            index: e.index,
                                            msg: failed.message
                                        });
                                    }
                                });
                            });
                            successIdx.forEach((e) => {
                                $("#message_" + e.index).html("<font>" + e.msg + "<font/>");
                            })
                            failedIdx.forEach((e) => {
                                $("#message_" + e.index).html("<font>" + e.msg + "<font/>");
                            })
                        } else {
                            alert("Success");
                            var affectedIdx = [];
                            datas.forEach((e) => {
                                xdata.dataRec?.successTid.forEach(success => {
                                    if (success.tid == e.tid) {
                                        affectedIdx.push({
                                            index: e.index,
                                            msg: "Success"
                                        });
                                    }
                                });
                            });
                            affectedIdx.forEach((e) => {
                                $("#message_" + e.index).html("<font>" + e.msg + "<font/>");
                            })
                        }
                    }

                    $('#button_save').html('<button type="button" onclick="save_associate()" value="Save" class="btn btn-info">Save</button>');
                });

            } else {
                return;
            }
        })
    };
</script>
@endsection