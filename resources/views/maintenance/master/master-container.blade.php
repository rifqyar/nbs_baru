@extends('layouts.app')

@section('title')
    Master Container
@endsection


@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Maintenance</a></li>
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active">Master Container</li>
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
                    <h3><b>Master Container</b></h3>


                    <div class="table-responsive">
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="service-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NO CONTAINER</th>
                                    <th>SIZE</th>
                                    <th>TYPE</th>
                                    <th>POSISI</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientAdd" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="addClientForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Add New Client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col mb-0">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" id="nik" name="nik" class="form-control"
                                    placeholder="Enter NIK" maxlength="16" required>
                            </div>
                            <div class="col mb-0">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Enter Name" maxlength="30" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-0">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="xxxx@xxx.xx" maxlength="30" required>
                            </div>
                            <div class="col mb-0">
                                <label for="birthdate" class="form-label">DOB</label>
                                <input type="date" id="birthdate" name="birthdate" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col mb-0">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="Enter Phone" maxlength="13" required>
                            </div>
                            <div class="col mb-0">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" id="address" name="address" class="form-control"
                                    placeholder="Enter Address" maxlength="100" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="containerUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form class="UpdateContainerForm" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="containerUpdateID">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Update Client</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="form-input" style="margin: 30px 30px 30px 30px;">
                            <tr>
                                <td class="form-field-caption"> Nomor Container </td>
                                <td> : </td>
                                <td>
                                    <input class="form-control" type="text" ID="NO_CONT" readonly />
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption"> Ukuran </td>
                                <td> : </td>
                                <td> <select class="form-control" id="SIZE">
                                        <option value="20">20</option>
                                        <option value="40">40</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption"> TIPE </td>
                                <td> : </td>
                                <td> <select class="form-control" id="TYPE" style="width:80px">
                                        <option value="DRY">DRY</option>
                                        <option value="FLT">FLT</option>
                                        <option value="HQ">HQ</option>
                                        <option value="OT">OT</option>
                                        <option value="OVD">OVD</option>
                                        <option value="RFR">RFR</option>
                                        <option value="TNK">TNK</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="form-field-caption"> Location </td>
                                <td> : </td>
                                <td>
                                    <select class="form-control" id="LOCATION">
                                        <option value="GATO">GATO</option>
                                        <option value="IN_YARD">IN_YARD</option>
                                        <option value="GATI">GATI</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="save()"">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('after-script')
    <script>
        $(document).ready(function() {
            $('#service-table').DataTable({
                responsive: true,
                scrollX: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('uster.maintenance.master_container.datatable') !!}',
                    type: 'GET',
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
                        data: 'size_',
                        name: 'size_'
                    },
                    {
                        data: 'type_',
                        name: 'type_'
                    },
                    {
                        data: 'location',
                        name: 'location'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                lengthMenu: [10, 20, 50, 100], // Set the default page lengths
                pageLength: 10, // Set the initial page length
            });
        });
    </script>

    <!-- Your Ajax script -->
    <script>
        function GetById(id) {
            Swal.fire({
                title: 'Mendapatkan Data...',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });
            $.ajax({
                url: '{{ route('uster.maintenance.master_container.getContainerByID') }}?no_container=' + id,
                type: 'GET',
                success: function(response) {
                    // Assuming you have a function to fill the modal with data
                    fillModalWithData(response);
                    Swal.close();
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

        function fillModalWithData(data) {

            $('#NO_CONT').val(data.no_container);
            $('#SIZE').val(data.size_);
            $('#TYPE').val(data.type_);
            $('#LOCATION').val(data.location);

            // Show the modal after filling data
            $('#containerUpdate').modal('show');
        }

        function save() {
            var formData = {
                no_container: $('#NO_CONT').val(),
                size: $('#SIZE').val(),
                type: $('#TYPE').val(),
                location: $('#LOCATION').val()
            };

            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: 'Apakah data sudah benar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mengubah Data Container...',
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('uster.maintenance.master_container.EditContainer') }}', // Replace with your server endpoint
                        data: JSON.stringify(formData),
                        contentType: 'application/json; charset=utf-8',
                        dataType: 'json',
                        success: function(response) {
                            // Handle success response with SweetAlert
                            Swal.fire({
                                title: 'Success!',
                                text: 'Data updated successfully',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                            $('#service-table').DataTable().ajax.reload();
                            $('#containerUpdate').modal('hide');
                        },
                        error: function(error) {
                            // Handle error response with SweetAlert
                            Swal.fire({
                                title: 'Error!',
                                text: 'Error updating data',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                           
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Batal Mengedit Container',
                    });
                }

            });
        }
    </script>
@endpush
