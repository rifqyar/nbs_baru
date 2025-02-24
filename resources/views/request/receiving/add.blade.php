@extends('layouts.app')

@section('title')
    Perencanaan Kegiatan Receiving
@endsection

@push('after-style')
    <link href="{{asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.css')}}" rel="stylesheet">
    <style>
        .ui-autocomplete-loading {
          background: white url("/assets/images/animated_loading.gif") right center no-repeat;
          background-repeat: no-repeat;
          background-position: center right calc(.375em + .1875rem);
          padding-right:  calc(1.5em + 0.75rem);
        }
    </style>
@endpush

@section('content')
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Request Receiving Dari Luar</h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">New Request</a></li>
                <li class="breadcrumb-item"><a href="{{route('uster.new_request.receiving.receiving_luar')}}">Receiving</a></li>
                <li class="breadcrumb-item active">Add Request</li>
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
                    <div class="card-title">
                        <h4>Form Add Request</h4>
                    </div>

                    <form action="javascript:void(0)" class="form-horizontal m-t-20" id="form-add" novalidate>
                        @csrf
                        <input type="hidden" name="type" value="add">
                        <input name="rec_dari" id="rec_dari" type="hidden" value="LUAR" />
                        <div class="row justify-content-center align-items-start">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="consignee">Penerima / Consignee <small class="text-danger">*</small></label>
                                    <input type="text" class="form-control" name="consignee" id="consignee" required>
                                    <input type="hidden" name="id_consignee" id="kd_consignee">
                                    <div class="invalid-feedback">Harap Masukan Penerima / Consignee</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="npwp">No. NPWP</label>
                                <input type="text" name="npwp_consignee" class="form-control" readonly id="npwp">
                            </div>
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                                    <label for="almt_consignee">Alamat Consignee</label>
                                    <textarea class="form-control" name="almt_consignee" id="almt_consignee" rows="3" readonly></textarea>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="no_ro">No. RO</label>
                                    <input type="text" name="no_ro" class="form-control" id="no_ro">
                                </div>
                            </div>

                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="di">D / I</label>
                                    <select id="di" name="di" class="form-control form-select">
                                        <option value="Domestik">Domestik</option>
                                        <option value="International">International</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 col-12">
                                <div class="form-group">
                                    <label for="keterangan">Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control" id="keterangan">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end m-t-40">
                            <a href="{{route('uster.new_request.receiving.receiving_luar')}}" class="btn btn-warning mr-3"> <i class="mdi mdi-chevron-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-info"><i class="mdi mdi-content-save"></i> Simpan </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-script')
    <script src="{{asset('assets/plugins/moment/moment.js')}}"></script>
    <script src="{{asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js')}}"></script>
    <script src="{{asset('pages/request/receiving/receiving.js')}}"></script>
@endpush
