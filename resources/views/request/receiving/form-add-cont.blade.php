<form action="javascript:void(0)" id="form-add-container" class="form-horizontal m-t-20" novalidate>
    @csrf
    <input type="hidden" name="no_req" value="{{$request->no_request}}">
    <div class="row align-items-start">
        <div class="col-12 col-md-4 form-group">
            <label for="no_cont">No. Container <small class="text-danger">*</small> </label>
            <input type="text" name="no_cont" id="no_cont" class="form-control" required>
            <input type="hidden" name="id_vsb" id="id_vsb" />
            <input type="hidden" name="vessel" id="vessel" />
            <div class="invalid-feedback">Harap masukan nomor container</div>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="size">Ukuran  <small class="text-danger">*</small></label>
            <select name="size" id="size" class="form-control custom-select" required>
                <option value="">-- Pilih Size Container --</option>
                <option value="20">20</option>
                <option value="40">40</option>
            </select>
            <div class="invalid-feedback">Harap pilih ukuran container</div>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="status">Status  <small class="text-danger">*</small></label>
            <select name="status" id="status" class="form-control custom-select" required>
                <option value="">-- Pilih Status Container --</option>
                <option value="MTY">MTY</option>
                <option value="FCL">FCL</option>
            </select>
            <div class="invalid-feedback">Harap pilih status container</div>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="type">Type  <small class="text-danger">*</small></label>
            <select name="type" id="type" class="form-control custom-select" required>
                <option value="">-- Pilih Type Container --</option>
                <option value="DRY">DRY</option>
                <option value="HQ">HQ</option>
                <option value="OT">OT</option>
                <option value="OVD">OVD</option>
                <option value="RFR">RFR</option>
                <option value="TNK">TNK</option>
                <option value="FLT">FLT</option>
            </select>
            <div class="invalid-feedback">Harap pilih Container Type</div>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="berbahaya">Berbahaya  <small class="text-danger">*</small></label>
            <select name="berbahaya" id="berbahaya" class="form-control custom-select" required>
                <option value="N">TIDAK</option>
                <option value="Y">YA</option>
            </select>
        </div>

        <div class="col-12 col-md-4 form-group">
            <label for="komoditi">Komoditi</label>
            <input type="text" name="komoditi" ID="komoditi" class="form-control" />
            <input type="hidden" name="kd_komoditi" ID="kd_komoditi" />
            <div class="invalid-feedback">Harap masukan komoditi</div>
        </div>

        <div class="col-12 col-md-2 form-group">
            <label for="depo_tujuan">Depo Tujuan</label>
            <input type="text" name="nm_depo_tujuan" ID="nm_depo_tujuan" class="form-control" value="TPK" disabled />
            <input type="hidden" value="1" name="depo_tujuan" ID="depo_tujuan" class="form-control" />
        </div>

        <div class="col-12 col-md-2 form-group">
            <label for="via">Via  <small class="text-danger">*</small></label>
            <select name="via" id="via" class="form-control custom-select" required>
                <option value="darat">DARAT</option>
                <option value="tongkang">TONGKANG</option>
            </select>
        </div>

        <div class="col-12 col-md-4 form-group">
            <label for="owner">Pemilik Container  <small class="text-danger">*</small></label>
            <input type="text" name="owner" ID="owner" class="form-control" required />
            <input type="HIDDEN" name="kd_owner" ID="kd_owner" />
            <div class="invalid-feedback">Harap masukan pemilik container</div>
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-warning btn-rounded mr-4" onclick="cancelAddCont()">
            <i class="mdi mdi-chevron-left"></i> Cancel
        </button>
        <button type="submit" class="btn btn-info btn-rounded">
            <i class="mdi mdi-content-save"></i> Simpan Container
        </button>
    </div>
</form>
