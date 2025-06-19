@extends('layouts.app')

@section('title')
    Master Pelanggan
@endsection

@section('content')
    <div class="row page-titles">
        <!-- ... (rest of the header) -->
    </div>
    <style>
        /* This will ensure the text in the Alamat column wraps */
        .wrap-text {
            white-space: normal !important;
            word-wrap: break-word;
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3><b>Master Pelanggan</b></h3>

                    <!-- Add New Pelanggan Button -->
                    <button type="button" class="btn btn-success mb-3" id="addPelangganBtn">Sync Pelanggan / Tambah
                        Pelanggan</button>

                    <div class="table-responsive">
                        <table class="datatables-service display nowrap table data-table" cellspacing="0" width="100%"
                            id="pelangganTable">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NPWP</th>
                                    <th>NPWP16</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No Telp</th>
                                    <!-- Removed Action column -->
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Pelanggan -->
    <div class="modal fade" id="pelangganModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="pelangganForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Pelanggan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- NPWP field with 'Check' button -->
                        <div class="form-group">
                            <label for="npwp">NPWP</label>
                            <div class="input-group">
                                <input type="text" id="npwp" name="npwp" class="form-control" required>
                                <div class="input-group-append">
                                    <button class="btn btn-secondary" type="button" id="checkNpwpBtn">Check</button>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="no_account_pbm" id='no_account_pbm'>

                        <!-- NPWP15 and NPWP16 fields (read-only), initially hidden -->
                        <div class="form-group" id="npwp15Field" style="display: none;">
                            <label for="npwp15">NPWP15</label>
                            <input type="text" id="npwp15" name="npwp15" class="form-control" readonly>
                        </div>

                        <div class="form-group" id="npwp16Field" style="display: none;">
                            <label for="npwp16">NPWP16</label>
                            <input type="text" id="npwp16" name="npwp16" class="form-control" readonly>
                        </div>

                        <!-- Other fields, initially hidden -->
                        <div id="otherFields" style="display: none;">
                            <div class="form-group">
                                <label for="nama">Nama</label>
                                <input type="text" id="nama" name="nama" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <textarea id="alamat" name="alamat" class="form-control" required rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="no_telp">No Telp</label>
                                <input type="text" id="no_telp" name="no_telp" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="action" value="">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <!-- Action button, initially hidden -->
                        <button type="button" class="btn btn-primary" id="submitForm" style="display: none;">Save
                            changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#pelangganTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('uster.maintenance.pelanggan.datatable') }}',
                columns: [{
                        data: null, // Use null for automatic numbering
                        name: 'no',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            // Render the sequence number
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'no_npwp_pbm',
                        name: 'no_npwp_pbm',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'no_npwp_pbm16',
                        name: 'no_npwp_pbm16',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'nm_pbm',
                        name: 'nm_pbm',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'almt_pbm',
                        name: 'almt_pbm',
                        defaultContent: 'N/A',
                        className: 'wrap-text'
                    },
                    {
                        data: 'no_telp',
                        name: 'no_telp',
                        defaultContent: 'N/A'
                    }
                ]
            });


            // Handle Add New Pelanggan Button click
            $('#addPelangganBtn').on('click', function() {
                // Clear the form
                $('#pelangganForm')[0].reset();
                $('#modalTitle').text('Pelanggan');
                $('#npwp').prop('readonly', false); // Allow NPWP input
                $('#action').val(''); // Reset action
                $('#npwp15Field').hide();
                $('#npwp16Field').hide();
                $('#otherFields').hide();
                $('#submitForm').hide();

                // Show the modal
                $('#pelangganModal').modal('show');
            });

            // Handle Check Button click
            $('#checkNpwpBtn').on('click', function() {
                var npwp = $('#npwp').val().trim();
                if (npwp === '') {
                    Swal.fire('Error', 'Please enter NPWP first', 'error');
                    return;
                }

                // Show loading modal
                Swal.fire({
                    title: 'Checking NPWP...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                // First, check if NPWP exists in database
                $.ajax({
                    url: '{{ route('uster.maintenance.pelanggan.checkNpwp') }}',
                    type: 'GET',
                    data: {
                        npwp: npwp
                    },
                    success: function(response) {
                        // Close loading modal
                        Swal.close();

                        if (response.exists) {
                            // Show loading modal again
                            Swal.fire({
                                title: 'Fetching data from external API...',
                                allowOutsideClick: false,
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: 'https://ibs-unicorn.pelindo.co.id/api/ApiBupot/ValidasiNpwpV3?NPWP=' +
                                    npwp,
                                type: 'POST',
                                success: function(apiResponse) {
                                    // Close loading modal
                                    Swal.close();

                                    if (apiResponse.status === "1") {
                                        // Fill the form with the data from the API
                                        $('#npwp').val(apiResponse.data.npwp15);
                                        $('#npwp15').val(apiResponse.data.npwp15);
                                        $('#npwp16').val(apiResponse.data.npwp16);

                                    } else {
                                        Swal.fire('Error',
                                            'NPWP validation failed: ' +
                                            apiResponse.message, 'error');
                                    }
                                },
                                error: function(error) {
                                    // Close loading modal
                                    Swal.close();
                                    Swal.fire('Error',
                                        'Failed to validate NPWP with external API',
                                        'error');
                                }
                            });

                            // NPWP exists in database
                            // Populate fields with data from database
                            $('#nama').val(response.data.nama);
                            $('#alamat').val(response.data.alamat);
                            $('#no_telp').val(response.data.no_telp);
                            $('#no_account_pbm').val(response.data.id);

                            // Show NPWP15 and NPWP16 fields
                            $('#npwp15Field').show();
                            $('#npwp16Field').show();

                            // Show other fields
                            $('#otherFields').show();

                            // Set action to 'edit'
                            $('#action').val('edit');
                            $('#submitForm').text('Update').show();

                            // Show notification
                            Swal.fire('Notice',
                                'NPWP exists in database. You can update the data.', 'info');
                        } else {
                            // Show loading modal again
                            Swal.fire({
                                title: 'Fetching data from external API...',
                                allowOutsideClick: false,
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: 'https://ibs-unicorn.pelindo.co.id/api/ApiBupot/ValidasiNpwpV3?NPWP=' +
                                    npwp,
                                type: 'POST',
                                success: function(apiResponse) {
                                    // Close loading modal
                                    Swal.close();

                                    if (apiResponse.status === "1") {
                                        // Fill the form with the data from the API
                                        $('#npwp').val(apiResponse.data.npwp15);
                                        $('#npwp15').val(apiResponse.data.npwp15);
                                        $('#npwp16').val(apiResponse.data.npwp16);
                                        $('#nama').val(apiResponse.data.namaWp);
                                        $('#alamat').val(apiResponse.data.alamat);

                                        // No Telp remains empty

                                        // Show NPWP15 and NPWP16 fields
                                        $('#npwp15Field').show();
                                        $('#npwp16Field').show();

                                        // Show other fields
                                        $('#otherFields').show();

                                        // Set action to 'add'
                                        $('#action').val('add');
                                        $('#submitForm').text('Insert').show();

                                        // Show notification
                                        Swal.fire('Notice',
                                            'NPWP not found in database. You can insert new data.',
                                            'info');
                                    } else {
                                        Swal.fire('Error',
                                            'NPWP validation failed: ' +
                                            apiResponse.message, 'error');
                                    }
                                },
                                error: function(error) {
                                    // Close loading modal
                                    Swal.close();
                                    Swal.fire('Error',
                                        'Failed to validate NPWP with external API',
                                        'error');
                                }
                            });
                        }
                    },
                    error: function(error) {
                        // Close loading modal
                        Swal.close();
                        Swal.fire('Error', 'Failed to check NPWP in database', 'error');
                    }
                });
            });

            // Handle Save Changes
            $('#submitForm').on('click', function() {
                var action = $('#action').val();
                var formData = {
                    id: $('#no_account_pbm').val(),
                    nama: $('#nama').val(),
                    alamat: $('#alamat').val(),
                    no_telp: $('#no_telp').val(),
                    npwp: $('#npwp').val(),
                    npwp15: $('#npwp15').val(),
                    npwp16: $('#npwp16').val()
                };

                var url = '';
                if (action === 'add') {
                    url = '{{ route('uster.maintenance.pelanggan.store') }}';
                } else if (action === 'edit') {
                    url = '{{ route('uster.maintenance.pelanggan.update') }}';
                } else {
                    Swal.fire('Error', 'Invalid action', 'error');
                    return;
                }

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to ' + (action === 'add' ? 'insert' : 'update') +
                        ' this data?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, ' + (action === 'add' ? 'insert' : 'update') + ' it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading modal
                        Swal.fire({
                            title: 'Menyimpan Data...',
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                // Close loading modal
                                Swal.close();

                                Swal.fire('Success', response.message, 'success');
                                $('#pelangganModal').modal('hide');
                                table.ajax.reload(); // Reload the DataTable
                            },
                            error: function(xhr) {
                                // Close loading modal
                                Swal.close();

                                var errorMessage = 'Failed to save data';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', errorMessage, 'error');
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire('Cancelled', 'Your data is safe :)', 'info');
                    }
                });
            });
        });
    </script>
@endpush
