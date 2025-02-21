@extends('layouts.app')

@section('title')
    NBS | Add Batal Request
@endsection

@push('after-style')
    <style>
        .ui-autocomplete-loading {
          background: white url("/assets/images/animated_loading.gif");
          background-repeat: no-repeat;
          background-position: center right calc(.375em + .1875rem);
          padding-right:  calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Request Batal SPPS</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.batal_spps')}}">Batal SPPS</a></li>
                <li class="breadcrumb-item active">Add Request Batal SPPS</li>
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
                    <div class="card" id="data-section">
                        <form action="javascript:void" id="form-add" novalidate>
                            @csrf
                            <div class="card-body">
                                <div class="row align-items-start">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="no_ba">No. Berita Acara <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="no_ba" required>
                                            <div class="invalid-feedback">Harap Masukan Nomor Berita Acara</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-group">
                                            <label for="no_cont">No. Container <small class="text-danger">*</small></label>
                                            <input type="text" class="form-control" name="no_cont" id="no_cont" required>
                                            <div class="invalid-feedback">Harap Masukan Container</div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="sc">Size Container</label>
                                            <input type="text" name="sc" id="sc" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="tc">Type Container</label>
                                            <input type="text" name="tc" id="tc" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="stc">Status Container</label>
                                            <input type="text" name="stc" id="stc" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="vessel">Vessel</label>
                                            <input type="text" name="vessel" id="vessel" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="voyage_in">Voyage In</label>
                                            <input type="text" name="voyage_in" id="voyage_in" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <input type="text" name="status" id="status" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="id_req">No Req SPPS</label>
                                            <input type="text" name="id_req" id="id_req" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="id_ureq">&nbsp;</label>
                                            <input type="text" name="id_ureq" id="id_ureq" class="form-control" readonly>
                                            <input type="hidden" name="no_ukk" id="no_ukk" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="cont_location">Lokasi Container</label>
                                            <input type="text" name="cont_location" id="cont_location" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row-reverse">
                                    <button type="submit" class="btn btn-info">Simpan Data</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{asset('pages/request/batalSpps/batalspps.js')}}"></script>
@endpush
