@extends('layouts.app')

@section('title')
    Master Container
@endsection

@section('pages-css')
    <style>
        .spinner {
            margin: 0 auto;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, .1);
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Maintenance</li>
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active">Master Container</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Welcome <strong>{{ Session::get('name') }}</strong></h6>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3><b>Master Container</b></h3>
                    <div class="text-center my-4">
                        <div class="form-group">
                            <label style="font-size: 15pt;">No. Request:</label>
                            <input type="text" name="trx_number" id="trx_number"
                                class="form-control d-inline-block w-25 text-center" style="font-size: 20pt;" />
                        </div>
                        <span id="load_error_request"></span>
                        <div class="form-group">
                            <label style="font-size: 15pt;">No. Container:</label>
                            <input type="text" name="cont_number" id="cont_number"
                                class="form-control d-inline-block w-25 text-center" style="font-size: 20pt;" readonly />
                        </div>
                        <div id="add_cont_detail" class="d-none">
                            <form class="add-form" method="post" id="add_form" action="">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Container Number</label>
                                        <input type="text" name="container_number" id="add_container_number"
                                            class="form-control" readonly />
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Request Number</label>
                                        <input type="text" name="request_number" id="add_request_number"
                                            class="form-control" readonly />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Container Size</label>
                                        <input type="text" name="container_size" id="add_container_size"
                                            class="form-control" readonly />
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Truck ID</label>
                                        <input type="text" name="truck_id" id="add_truck_id" class="form-control" />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Truck Number</label>
                                        <input type="text" name="truck_number" id="add_truck_number" class="form-control"
                                            readonly />
                                    </div>
                                    <div class="form-group col-md-6 d-flex align-items-end">
                                        <input type="button" id="add_bt" value="Add"
                                            class="btn btn-primary btn-block" onclick="add_new_cont()" />
                                    </div>
                                </div>
                                <input type="hidden" id="add_trailer_type" value="40FT4A" />
                                <input type="hidden" id="add_truck_type" />
                                <input type="hidden" id="add_isocode" />
                                <input type="hidden" id="add_pod" />
                                <input type="hidden" id="add_weight" />
                            </form>
                        </div>
                        <div id="container_detail" class="w-100 mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-autocomplete/1.0.7/jquery.auto-complete.min.js"
        integrity="sha512-TToQDr91fBeG4RE5RjMl/tqNAo35hSRR4cbIFasiV2AAMQ6yKXXYhdSdEpUcRE6bqsTiB+FPLPls4ZAFMoK5WA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript">
        var container_list = [];
        var container_list_auto = [];
        $(function() {
            $('#trx_number').on('input', function() {
                var trx_number = $('#trx_number').val();
                if (trx_number.length >= 3) {
                    $("#trx_number").autocomplete({
                        minLength: 7,
                        source: function(request, response) {
                            $("#load_error_request").empty();
                            $("#load_error_request").append(
                                "<div class='spinner'></div>");
                            $.getJSON("{{ route('maintenance.tca.invoice_number_praya') }}", {
                                term: $("#trx_number").val()
                            }, function(invoice) {
                                if (invoice[0] && invoice[0].no_request) {
                                    $.getJSON(
                                        "{{ route('maintenance.tca.list_by_cont_praya') }}", {
                                            NO_REQ: invoice[0].no_request
                                        },
                                        function(container) {
                                            if (container && container.length) {
                                                container_list_auto = container.map(
                                                    e => {
                                                        return {
                                                            label: e
                                                                .containerNo,
                                                            value: e
                                                        }
                                                    });
                                                $("#cont_number").removeAttr(
                                                    "readonly").removeClass(
                                                    "grey-bg").addClass(
                                                    "white-bg");
                                                $("#load_error_request").empty();
                                            } else {
                                                $("#load_error_request").empty()
                                                    .append(
                                                        "<span>No data found</span>"
                                                    );
                                            }
                                        });
                                } else {
                                    $("#load_error_request").empty().append(
                                        "<span>No data found</span>");
                                }
                            });
                        }
                    });
                }
            });

            $('#cont_number').on('input', function() {
                var cont_number = $('#cont_number').val();
                $("#cont_number").autocomplete({
                    source: container_list_auto,
                    focus: function(event, ui) {
                        $("#cont_number").val(ui.item.containerNo);
                        return false;
                    },
                    select: function(event, ui) {
                        $("#cont_number").val(ui.item.containerNo);
                        show_selected_cont(ui.item);
                        return false;
                    }
                }).data("ui-autocomplete")._renderItem = function(ul, item) {
                    return $("<li></li>")
                        .data("item.autocomplete", item.value)
                        .append("<a class='dropdown-item'>" + item.value.containerNo + "<br>" + item
                            .value.requestNumber + "</a>")
                        .appendTo(ul);
                };
            });

            $("#add_truck_id").autocomplete({
                minLength: 3,
                source: "{{ route('maintenance.tca.get_truck_list') }}",
                focus: function(event, ui) {
                    $(this).val(ui.item.tid).removeClass('loader');
                    return false;
                },
                select: function(event, ui) {
                    $(this).val(ui.item.tid);
                    $("#add_truck_number").val(ui.item.truckNumber);
                    $("#add_truck_type").val(ui.item.truckType);
                    return false;
                },
                search: function(e, u) {
                    $(this).addClass('loader');
                },
                response: function(e, u) {
                    $(this).removeClass('loader');
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a class='dropdown-item'>" + item.tid + " - " + item.truckNumber + " <br>" + item
                        .company + "</a>")
                    .appendTo(ul);
            };

            $.fn.styleTable = function(options) {
                var defaults = {
                    css: 'styleTable'
                };
                options = $.extend(defaults, options);

                return this.each(function() {
                    input = $(this);
                    input.addClass(options.css);

                    input.find("tr").on('mouseover mouseout', function(event) {
                        if (event.type == 'mouseover') {
                            $(this).children("td").addClass("ui-state-hover");
                        } else {
                            $(this).children("td").removeClass("ui-state-hover");
                        }
                    });

                    input.find("th").addClass("ui-state-default");
                    input.find("td").addClass("ui-widget-content");

                    input.find("tr").each(function() {
                        $(this).children("td:not(:first)").addClass("first");
                        $(this).children("th:not(:first)").addClass("first");
                    });
                });
            };

            $("#Table1").styleTable();
        });

        function show_selected_cont($cont_data) {
            $("#add_cont_detail").removeClass("d-none");
            $("#add_container_number").val($cont_data.value.containerNo);
            $("#add_request_number").val($cont_data.value.requestNumber);
            $("#add_container_size").val($cont_data.value.containerSize);
            $("#add_isocode").val($cont_data.value.isoCode);
            $("#add_pod").val($cont_data.value.pod);
            $("#add_weight").val($cont_data.value.weight);
            $("#add_truck_id").val("");
            $("#add_truck_number").val("");
            $("#add_truck_type").val("");
        }

        function load_contlist() {
            var tableTD = '';
            container_list.forEach((element, index) => {
                tableTD += `
                    <tr id='content_table_${index}'>
                        <td><input type='text' class='form-control' id='container_number_${index}' value='${element.container_no}' readonly /></td>
                        <td><input type='text' class='form-control' id='request_number_${index}' value='${element.request_number}' readonly /></td>
                        <td><input type='text' class='form-control' id='container_size_${index}' value='${element.container_size}' readonly /></td>
                        <input type='hidden' id='trailer_type_${index}' name='trailer_type' value='40FT4A' />
                        <td><input type='text' class='form-control' id='truck_id_${index}' value='${element.truck_id}' readonly /></td>
                        <td><input type='text' class='form-control' id='truck_number_${index}' value='${element.truck_number}' readonly /></td>
                        <input type="hidden" id="isocode_${index}" value='${element.isocode}' />
                        <input type="hidden" id="pod_${index}" value='${element.pod}' />
                        <input type="hidden" id="weight_${index}" value='${element.weight}' />
                        <td colspan='6'><input type='button' class='btn btn-danger' id='delete_bt_${index}' value='Delete' /></td>
                    </tr>
                `;
            });

            var tableHTML = `
                <table id='Table1' class='table table-bordered'>
                    <thead>
                        <tr id='header_table'>
                            <th>Container Number</th>
                            <th>Request Number</th>
                            <th>Container Size</th>
                            <th>Truck ID</th>
                            <th>Police Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableTD}
                    </tbody>
                </table>
                <div id="save_bt_c" class="text-center mt-3">
                    <input type="button" id="save_bt" value="Save" class="btn btn-success" />
                </div>
            `;

            $("#container_detail").html(tableHTML);
            $("#Table1").styleTable();

            $("[id^=delete_bt_]").on('click', function() {
                var index = $(this).attr('id').split('_')[2];
                delete_cont(index);
            });

            $("#save_bt").on('click', function() {
                save_cont();
            });

            if (!container_list.length) {
                $("#container_detail").empty();
            }
        }

        function add_new_cont() {
            if (!$("#add_truck_id").val()) {
                Swal.fire('Error', 'Truck ID cannot be empty', 'error');
                return;
            }
            if (container_list.length === 1 && ($("#add_container_number").val() == container_list[0].container_no)) {
                Swal.fire('Error', 'Container number is duplicated, please check again', 'error');
                return;
            }
            if (container_list.length === 1 && ($("#add_truck_id").val() != container_list[0].truck_id)) {
                console.log(container_list.length);
                console.log($("#add_truck_id").val());
                console.log(container_list[0].truck_id);
                Swal.fire('Error', 'Cannot perform TCA Combo, please check truck ID', 'error');
                return;
            }

            if (container_list.length == 2) {

                Swal.fire('Error', 'Cannot perform TCA Combo for more than 2 containers', 'error');
                return;
            }

            var cont = {
                container_no: $("#add_container_number").val(),
                request_number: $("#add_request_number").val(),
                container_size: $("#add_container_size").val(),
                isocode: $("#add_isocode").val(),
                pod: $("#add_pod").val(),
                weight: $("#add_weight").val(),
                truck_id: $("#add_truck_id").val(),
                truck_number: $("#add_truck_number").val(),
                truck_type: $("#add_truck_type").val(),
                trailer_type: $("#add_trailer_type").val(),
            };

            container_list.push(cont);
            load_contlist();
        }

        function delete_cont($idx) {
            container_list.splice($idx, 1);
            load_contlist();
        }

        function save_cont() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to save this TCA",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, save it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('maintenance.tca.save_associate_praya ') }}";
                    var detail = container_list.map(e => {
                        return {
                            requestNumber: e.request_number,
                            containerNo: e.container_no,
                            pod: e.pod,
                            weight: e.weight,
                            isoCode: e.isocode
                        };
                    });
                    var payload = {
                        truckType: container_list[0].truck_type,
                        truckNumber: container_list[0].truck_number,
                        createdBy: "Admin Uster",
                        tid: container_list[0].truck_id,
                        axle: container_list[0].trailer_type,
                        type: "OEC",
                        actionCode: "R",
                        detail
                    };

                    $("#save_bt_c").html("<div class='spinner'></div>");

                    $.post(url, payload, function(data) {
                        var xdata = JSON.parse(data);
                        if (xdata.code === 0 && xdata.msg && !xdata.dataRec) {
                            Swal.fire('Error', xdata.msg, 'error');
                            $("#save_bt_c").html(
                                '<input type="button" id="save_bt" value="Save" class="btn btn-success" />'
                            );
                            return;
                        }
                        Swal.fire('Success', xdata.msg, 'success');
                        $("#save_bt_c").html('<p class="text-success" style="font-size: 12pt;">' + xdata
                            .msg + '</p>');
                    });
                }
            });
        }
    </script>
@endpush
