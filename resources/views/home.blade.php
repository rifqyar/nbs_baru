<!-- resources/views/home.blade.php -->

@extends('layouts.app')

@push('before-style')
    <!-- Existing CSS links -->
    <!-- ... -->
    <link href="{{ asset('assets/plugins/chartist-js/dist/chartist.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/chartist-js/dist/chartist-init.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Existing styles */
        @media (max-width: 640px) {
            .ct-label.ct-horizontal.ct-end {
                display: none;
            }
        }

        
    </style>
@endpush

@section('content')
    

    <div class="row page-titles">
        <!-- Existing content -->
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Dashboard</h3>
           
        </div>
        <div class="col-md-7 col-4 align-self-center">
            <div class="d-flex m-t-10 justify-content-end">
                <h6>Selamat Datang <p><b>{{ Session::get('name') }}</b></p></h6>
            </div>
        </div>
    </div>
    <!-- Total Requests Section -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <h3 class="card-title">Today Dashboard</h3>
        </div>
    </div>
    <div class="row">
        <!-- Total Request Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="round round-lg text-white d-inline-block text-center rounded-circle bg-warning">
                            <i class="fa-solid fa-code-pull-request"></i>
                        </div>
                        <div class="ml-2 align-self-center">
                            <h3 class="mb-0 font-weight-light" id="totalRequest">0</h3>
                            <h5 class="text-muted mb-0">Total Request</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nota Created Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="round round-lg text-white d-inline-block text-center rounded-circle bg-success">
                            <i class="fas fa-pencil-alt fa-2x"></i>
                        </div>
                        <div class="ml-2 align-self-center">
                            <h3 class="mb-0 font-weight-light" id="notaCreated">0</h3>
                            <h5 class="text-muted mb-0">Nota Created</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nota Paid Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="round round-lg text-white d-inline-block text-center rounded-circle bg-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="ml-2 align-self-center">
                            <h3 class="mb-0 font-weight-light" id="notaPaid">0</h3>
                            <h5 class="text-muted mb-0">Nota Paid</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Nota Unpaid Card -->
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row">
                        <div class="round round-lg text-white d-inline-block text-center rounded-circle bg-danger">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                        <div class="ml-2 align-self-center">
                            <h3 class="mb-0 font-weight-light" id="notaUnpaid">0</h3>
                            <h5 class="text-muted mb-0">Nota Unpaid</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Activity Overview -->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <div>
                            <h3 class="card-title">Container Activity Overview</h3>
                            <h6 class="card-subtitle">Summary of Container Activities at the Port</h6>
                        </div>
                        <div class="ml-auto align-self-center">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item px-2">
                                    <h6 class="text-success">
                                        <i class="fa fa-circle font-10 mr-2"></i>Total Activities
                                    </h6>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="campaign ct-charts mt-4"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <!-- Existing JS scripts -->
    <script src="{{ asset('assets/plugins/chartist-js/dist/chartist.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/chartist-plugin-tooltip-master/dist/chartist-plugin-tooltip.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Show loading spinner
           

            // AJAX request to fetch dashboard data
            $.ajax({
                url: "{{ route('api.dashboard.data') }}",
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response); // For debugging

                    // Update Total Request
                    $('#totalRequest').text(
                        parseInt(response.totalRequest).toLocaleString('id-ID')
                    );

                    // Update Nota Summary
                    $('#notaCreated').text(response.NotaSummary.total_today);
                    $('#notaPaid').text(response.NotaSummary.total_lunas);
                    $('#notaUnpaid').text(response.NotaSummary.total_belum_lunas);

                    // Prepare data for Chartist
                    var labels = [];
                    var dataSeries = [];

                    $.each(response.kegiatanSummary, function(index, kegiatan) {
                        // Corrected field access
                        let kegiatanName = kegiatan.kegiatan.charAt(0).toUpperCase() + kegiatan.kegiatan.slice(1).toLowerCase();
                        labels.push(kegiatanName);
                        dataSeries.push({
                            y: parseInt(kegiatan.total),
                            meta: kegiatanName
                        });
                    });

                    // Create the bar chart
                    new Chartist.Bar('.campaign', {
                        labels: labels,
                        series: [dataSeries]
                    }, {
                        low: 0,
                        showArea: true,
                        fullWidth: true,
                        chartPadding: {
                            right: 40,
                            left: 20,
                            top: 20,
                            bottom: 40
                        },
                        plugins: [
                            Chartist.plugins.tooltip({
                                // Optional: Customize tooltip here
                            })
                        ],
                        axisY: {
                            onlyInteger: true,
                            offset: 20,
                            labelInterpolationFnc: function(value) {
                                return value;
                            }
                        },
                        axisX: {
                            showGrid: false,
                            showLabel: true,
                            labelInterpolationFnc: function(value, index) {
                                return labels[index];
                            }
                        },
                        responsiveOptions: [
                            ['screen and (max-width: 640px)', {
                                seriesBarDistance: 10,
                                axisX: {
                                    labelInterpolationFnc: function(value) {
                                        return value.length > 6 ? value.slice(0, 6) + '...' : value;
                                    }
                                }
                            }]
                        ]
                    }).on('draw', function(data) {
                        if (data.type === 'bar') {
                            data.element.attr({
                                style: 'stroke-width: 20px; stroke: rgb(30,136,229);'
                            });

                            // Add animation for bars
                            data.element.animate({
                                y2: {
                                    begin: 0,
                                    dur: 1000,
                                    from: data.y1,
                                    to: data.y2,
                                    easing: Chartist.Svg.Easing.easeOutQuint
                                }
                            });
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to load dashboard data.');
                }
            });
        });
    </script>
@endpush
