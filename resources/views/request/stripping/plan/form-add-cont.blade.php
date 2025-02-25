<form action="javascript:void(0)" id="form-add-container" class="form-horizontal m-t-20" novalidate>
    @csrf
    <input type="hidden" name="no_req" value="{{$request->row_request->no_request_plan}}">
    <input type="hidden" name="no_req2" class="form-control" readonly id="no_req2" value="{{$request->row_request->o_reqnbs}}">

    <div class="row align-items-start">
        <div class="col-12 col-md-4 form-group">
            <label for="NO_CONT">No. Container <small class="text-danger">*</small> </label>
            <input type="text" name="NO_CONT" id="NO_CONT" class="form-control" required>
            <input type="hidden" name="BP_ID" ID="BP_ID" readonly="readonly" />
            <input type="hidden" name="NO_UKK" ID="NO_UKK" readonly="readonly" />
            <input type="hidden" name="TGL_STACK" ID="TGL_STACK" readonly="readonly" />
            <input type="hidden" name="SP2" ID="SP2" readonly="readonly" />
            <input type="hidden" name="BLOK" ID="BLOK" readonly="readonly" />
            <input type="hidden" name="SLOT" ID="SLOT" readonly="readonly" />
            <input type="hidden" name="ROW" ID="ROW" readonly="readonly" />
            <input type="hidden" name="TIER" ID="TIER" readonly="readonly" />
            <div class="invalid-feedback">Harap masukan nomor container</div>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="SIZE">Ukuran  <small class="text-danger">*</small></label>
            <input type="text" name="SIZE" ID="SIZE" class="form-control" readonly="readonly"/>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="ASAL_CONT">Lokasi  <small class="text-danger">*</small></label>
            <input type="text" name="ASAL_CONT" ID="ASAL_CONT" readonly="readonly" class="form-control"/>
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="STATUS">Status</label>
            <input type="text" name="STATUS" ID="STATUS" readonly="readonly" class="form-control" />
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="TYPE">Type  <small class="text-danger">*</small></label>
			<input type="text" name="TIPE" ID="TYPE" readonly="readonly" class="form-control" />
        </div>
        <div class="col-12 col-md-2 form-group">
            <label for="BERBAHAYA">Berbahaya  <small class="text-danger">*</small></label>
            <select name="BERBAHAYA" id="BERBAHAYA" class="form-control custom-select" required>
                <option value="N">TIDAK</option>
                <option value="Y">YA</option>
            </select>
        </div>

        <div class="col-12 col-md-3 form-group">
            <label for="KOMODITI">Komoditi <small class="text-danger">*</small></label>
            <input type="text" name="KOMODITI" ID="KOMODITI" class="form-control" required />
            <input type="hidden" name="kd_komoditi" ID="kd_komoditi" />
            <div class="invalid-feedback">Harap masukan komoditi</div>
        </div>

        <div class="col-12 col-md-6">
            <div class="row align-items-start">
                <div class="col-12 col-md-6 form-group">
                    <label for="VOYAGE">Voyage / Kapal <small class="text-danger">*</small></label>
                    <input type="text" name="VOYAGE" ID="VOYAGE" class="form-control" required/>
                </div>
                <div class="col-12 col-md-6 form-group">
                    <label for="VESSEL">&nbsp;</label>
                    <input type="text" name="VESSEL" id="VESSEL" class="form-control" required>
                </div>
            </div>
            <input type="hidden" name="NO_BOOKING" id="NO_BOOKING" value="{{$request->row_request->no_booking}}"/>
        </div>

        <div class="col-12 col-md-4 form-group">
            <label for="TGL_BONGKAR">Tanggal Bongkar</label>
            <input type="text" name="TGL_BONGKAR" id="TGL_BONGKAR" class="form-control" readonly>
        </div>
        <div class="col-12 col-md-4 form-group">
            <label for="lokasi">Depo Tujuan</label>
            <input type="text" id="lokasi" name="lokasi" readonly class="form-control">
        </div>
        <div class="col-12 col-md-4 form-group">
            <label for="AFTER_STRIP">Tujuan Setelah Strip <small class="text-danger">*</small></label>
            <select readonly name="AFTER_STRIP" id="AFTER_STRIP" class="form-control form-select custom-select">
                <option selected value="Depo Empty">Depo Empty</option>
                <option disabled value="Repo Muat">Repo Muat</option>
                <option disabled value="Depo Luar">Depo Luar</option>
                <option disabled value="Stuffing">Stuffing</option>
            </select>
        </div>
        <div class="col-12 col-md-6 form-group">
            <label for="tgl_mulai">Tgl. Mulai Stripping</label>
            <input readonly type="text" name="tgl_mulai" ID="tgl_mulai" class="form-control" />
        </div>
        <div class="col-12 col-md-6 form-group">
            <label for="TGL_SELESAI">Tgl. Selesai Stripping</label>
            <input type="text" name="tgl_selesai" ID="TGL_SELESAI" class="form-control" />
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
