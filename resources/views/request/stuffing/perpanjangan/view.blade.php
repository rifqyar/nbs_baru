@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Stuffing
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item">Stuffing</li>
                <li class="breadcrumb-item"><a
                        href="{{ route('uster.new_request.stuffing.stuffing_plan') }}">Perpanjangan Stuffing</a>
                </li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p>
                </h6>
            </div>
        </div>
    </div>
    <form id="form">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <h3><b> Request Perpanjangan Stuffing</b></h3>

                        <div class="p-3">

                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Pengguna Jasa</h4>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">No Request : </label>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" id="no_req" name="NO_REQ"
                                        value="{{ $result_request->no_request }}" readonly="readonly">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="NO_REQUEST2" id="NO_REQUEST2"
                                        value="{{ $no_req2 }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="NO_REQUEST3" id="NO_REQUEST3"
                                        value="{{ $no_req3 }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama EMKL : </label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="EMKL" id="EMKL"
                                        placeholder="{{ $result_request->nama_emkl }}">
                                    <input type="hidden" name="ID_EMKL" id="ID_EMKL"
                                        value="{{ $result_request->id_emkl }}" />
                                </div>
                            </div>
                        </div>


                        <div class="p-3">
                            <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Nama Kapal</h4>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Kapal : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="NM_KAPAL" name="NM_KAPAL"
                                        value="{{ $result_request->nm_kapal }}" style="background-color:#FFFFCC;"
                                        title="Autocomplete">


                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Voyage : </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="VOYAGE_IN" name="VOYAGE_IN"
                                        value="{{ $result_request->voyage_in }}" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Nama Agen : </label>
                                </div>
                                <div class="col-md-4">
                                    <input id="KD_AGEN" name="KD_AGEN" type="hidden" class="form-control" readonly />
                                    <input id="NM_AGEN" name="NM_AGEN" type="text"
                                        value="{{ $result_request->nm_agen }}" class="form-control" readonly />
                                </div>
                                <div class="col-md-2 py-2">
                                    <label for="tb-fname">Port Of Destination : </label>
                                </div>

                                <input id="NM_PELABUHAN_ASAL" name="NM_PELABUHAN_ASAL" type="text"
                                    value="{{ $result_request->nm_pelabuhan_asal }}" title="Autocomplete"
                                    class="form-control" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No PKK : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" id="NO_UKK" name="NO_UKK"
                                    readonly="readonly" title="Autocomplete">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Final Discharge : </label>
                            </div>
                            <div class="col-md-4">

                                <input class="form-control" id="NM_PELABUHAN_TUJUAN" name="NM_PELABUHAN_TUJUAN"
                                    type="text" value="{{ $result_request->nm_pelabuhan_tujuan }}"
                                    title="Autocomplete" readonly />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No Booking : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="NO_BOOKING" name="NO_BOOKING"
                                    value="{{ $result_request->no_booking }}" readonly>
                            </div>
                        </div>


                    </div>



                    <div class="p-3">
                        <h4 class="card-title mb-3 pb-3 border-bottom">Informasi Dokumen Pendukung</h4>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No P.E.B : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor Dokumen : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">No. N.P.E : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor JPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor D.O : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">BPRP : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor B.L : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Nomor SPPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Tanggal SPPB : </label>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 py-2">
                                <label for="tb-fname">Keterangan : </label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" name="KETERANGAN" id="KETERANGAN" value="{{ $result_request->keterangan }}" class="form-control">
                            </div>
                        </div>

                    </div>



                    <div class="card p-2" style="margin-left: 10px;margin-right:10px">
                        <div class="table-responsive">
                            <table class="datatables-service table " id="service-table">
                                <thead>
                                    <tr>
                                        <th width="5%">NO</th>
                                        <th width="15%">NO CONTAINER</th>
                                        <th width="10%">SIZE / TYPE</th>
                                        <th width="15%">COMMODITY</th>
                                        <th width="15%">AWAL</th>
                                        <th width="20%">PERP EMPTY S/D</th>
                                        <th>INFO</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>


                    <div id="button_save" class="text-center my-3">
                        <div id="button_save" class="text-center my-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Informasi Stuffing</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modalContent" style="color:black"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.new_request.stuffing.perpanjangan.view.datatable', request('no_req')) !!}',
                    type: 'GET',
                    data: function(d) {
                        d.active = 'view'
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
                        data: 'kd_size',
                        name: 'kd_size',
                        render: function(data, type, row, meta) {
                            // Check if data or row.no_npe is null, and handle accordingly
                            var kd_size = data !== null ? data : '';
                            var kd_type = row.kd_type !== null ? row.kd_type : '';

                            // Render the combined value of 'nm_kapal' and 'voyage' with space and comma
                            return kd_size + ' , ' + kd_type;
                        }
                    },
                    {
                        data: 'commodity',
                        name: 'commodity'
                    },
                    {
                        data: 'tgl_mulai',
                        name: 'tgl_mulai'
                    },
                    {
                        data: 'tgl_approve_input',
                        name: 'tgl_approve_input',
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return $('<div />').html(data).text(); // decode HTML entities
                        }
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
                rowCallback: function(row, data, rowIdx) {
                    // Add hidden input field to each row
                    $(row).append('<input type="hidden" name="NO_CONT_' + (rowIdx + 1) +
                        '" class="hidden-input" value="' + data.no_container + '">');
                    $(row).append('<input type="hidden" name="TGL_PERP_' + (rowIdx + 1) +
                        '" class="hidden-input" value="' + data.no_container + '">');
                },
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
                initComplete: function() {
                    var table = $('#service-table').DataTable();
                    var totalRows = table.rows().count();
                    $('#button_save').html('<button type="button" onclick="simpanarea(' +
                        totalRows +
                        ')" value="Save" class="btn btn-info">Simpan Perpanjangan Stuffing</button>'
                    );
                }
            });
        });

        function info_lapangan() {

            Swal.fire({
                title: 'Mendapatkan Data...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: "{{ route('uster.new_request.stuffing.perpanjangan.infoContainerPerpanjanganStuffing') }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#infoModal').modal('show');
                    // Update the modal content with the received data
                    $("#modalContent").html(response);
                    Swal.close();
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.log("Error:", error);
                    Swal.close();
                }
            });
        }

        function simpanarea(totalRows) {


            Swal.fire({
                title: 'Menambahkan Data Container...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            var url = '{{ route('uster.new_request.stuffing.perpanjangan.checkClose') }}';
            var no_booking = '{{ $no_booking_cur }}';

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post(url, {
                no_booking: no_booking
            }, function(data) {
                if (data == 'Y') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal perpanjangan melebihi masa closing time',
                    });
                } else {
                    var x;
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: "Perpanjangan akan dilakukan, data tidak bisa diedit lagi",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Perpanjang!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            event.preventDefault();

                            const formData = $('form').serialize();
                            const url =
                                '{{ route('uster.new_request.stuffing.perpanjangan.addContainer') }}';
                            $.post(url, formData, function(response) {
                                // Lakukan sesuatu dengan respons jika perlu
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'You pressed Cancel!',
                            });
                        }
                    });


                }
            });
        }
    </script>
@endpush
