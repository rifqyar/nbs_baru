<?php

namespace App\Services\Report\Container;

use Exception;
use Illuminate\Support\Facades\DB;

class ContainerService
{

    function DataTable($request)
    {
        $no_nota = $request->NO_NOTA;
        $no_request = $request->NO_REQUEST;
        $kegiatan = $request->KEGIATAN;

        if ($kegiatan == 'DELIVERY_MTY' || $kegiatan == 'RELOKASI_MTY_KE_TPK' || $kegiatan == 'RELOKASI_TPK_EKS_STUFFING' || $kegiatan == 'PENUMPUKAN_SP2') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.tgl_delivery tgl_akhir, mc.size_, mc.type_, cd.status, cd.hz, cd.komoditi, cd.berat
				from container_delivery cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'STRIPPING' || $kegiatan == 'RELOKASI_MTY_EKS_STRIPPING') {
            $query = "select cd.no_container, cd.tgl_bongkar tgl_awal, case when cd.tgl_selesai is null then cd.tgl_bongkar+4 else cd.tgl_selesai end tgl_akhir,
				mc.size_, mc.type_, 'FCL' status , cd.hz, cd.commodity komoditi, '22000' berat
				from container_stripping cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'STUFFING') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.start_perp_pnkn tgl_akhir,
				mc.size_, mc.type_, 'MTY' status , cd.hz, cd.commodity komoditi, cd.berat
				from container_stuffing cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'RECEIVING') {
            $query = "select cd.no_container, '' tgl_awal, '' tgl_akhir,
				mc.size_, mc.type_, 'MTY' status , case when cd.hz is null then 'N' else cd.hz end hz,
				cd.komoditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
				from container_receiving cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'BATAL_MUAT') {
            $query = "select cd.no_container, cd.start_pnkn tgl_awal , cd.end_pnkn tgl_akhir,
                mc.size_, mc.type_, cd.status status , 'N' hz,
                '' komiditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
                from container_batal_muat cd inner join master_container mc 
                on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        }


        return DB::connection('uster')->select($query);
    }
    function nota($request)
    {
        $no_nota        = strtoupper($request);

        //echo "SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER FROM NOTA_ALL_H WHERE NO_NOTA LIKE '$no_nota%'";
        $query             = "SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RECEIVING' KEGIATAN FROM NOTA_RECEIVING WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NOTA_DELIVERY.NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, CASE WHEN DELIVERY_KE = 'LUAR' THEN 'DELIVERY_MTY' ELSE 
        CASE WHEN JN_REPO = 'EMPTY' THEN 'RELOKASI_MTY_KE_TPK' ELSE 'RELOKASI_TPK_EKS_STUFFING' END END AS KEGIATAN FROM NOTA_DELIVERY, REQUEST_DELIVERY WHERE NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST AND NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STUFFING' KEGIATAN FROM NOTA_STUFFING WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STRIPPING' KEGIATAN FROM NOTA_STRIPPING WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RELOKASI_ANTAR_DEPO' KEGIATAN FROM NOTA_RELOKASI WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RELOKASI_MTY_EKS_STRIPPING' KEGIATAN FROM NOTA_RELOKASI_MTY WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'PENUMPUKAN_SP2' KEGIATAN FROM NOTA_PNKN_DEL WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')
        UNION
        SELECT NO_NOTA, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TGL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'BATAL_MUAT' KEGIATAN FROM NOTA_BATAL_MUAT WHERE NO_FAKTUR = '$no_nota' --AND (STATUS = 'NEW' OR STATUS = 'PERP')";
        return DB::connection('uster')->select($query);
    }

    function generatePdf($request)
    {
        $tgl_awal    = $request->tgl_awal;
        $tgl_akhir    = $request->tgl_akhir;
        $jenis        = $request->jenis;

        $query_list_     = "SELECT * FROM (
					SELECT NO_NOTA, NOTA_DELIVERY.NO_REQUEST, TRUNC(TGL_NOTA) TGL_NOTA, 'DELIVERY'  AS KEGIATAN, EMKL, TO_CHAR(TOTAL_TAGIHAN, '999,999,999,999') TOTAL_TAGIHAN, DELIVERY_KE ASAL
					FROM NOTA_DELIVERY INNER JOIN REQUEST_DELIVERY ON NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
					WHERE TRUNC(TGL_NOTA) > TO_DATE('$tgl_awal','yyyy-mm-dd') AND TRUNC(TGL_NOTA) < TO_DATE('$tgl_akhir','yyyy-mm-dd')
					UNION
					SELECT NO_NOTA, NOTA_RECEIVING.NO_REQUEST, TRUNC(TGL_NOTA) TGL_NOTA, 'RECEIVING'  AS KEGIATAN, EMKL, TO_CHAR(TOTAL_TAGIHAN, '999,999,999,999') TOTAL_TAGIHAN, RECEIVING_DARI ASAL
					FROM NOTA_RECEIVING INNER JOIN REQUEST_RECEIVING ON NOTA_RECEIVING.NO_REQUEST = REQUEST_RECEIVING.NO_REQUEST
					WHERE TRUNC(TGL_NOTA) > TO_DATE('$tgl_awal','yyyy-mm-dd') AND TRUNC(TGL_NOTA) < TO_DATE('$tgl_akhir','yyyy-mm-dd')  
					UNION
					SELECT NO_NOTA, NOTA_STRIPPING.NO_REQUEST, TRUNC(TGL_NOTA) TGL_NOTA, 'STRIPPING'  AS KEGIATAN, EMKL, TO_CHAR(TOTAL_TAGIHAN, '999,999,999,999') TOTAL_TAGIHAN, STRIPPING_DARI ASAL
					FROM NOTA_STRIPPING INNER JOIN REQUEST_STRIPPING ON NOTA_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST
					WHERE TRUNC(TGL_NOTA) > TO_DATE('$tgl_awal','yyyy-mm-dd') AND TRUNC(TGL_NOTA) < TO_DATE('$tgl_akhir','yyyy-mm-dd')  
					UNION
					SELECT NO_NOTA, NOTA_STUFFING.NO_REQUEST, TRUNC(TGL_NOTA) TGL_NOTA, 'STUFFING'  AS KEGIATAN, EMKL, TO_CHAR(TOTAL_TAGIHAN, '999,999,999,999') TOTAL_TAGIHAN, STUFFING_DARI ASAL
					FROM NOTA_STUFFING INNER JOIN REQUEST_STUFFING ON NOTA_STUFFING.NO_REQUEST = REQUEST_STUFFING.NO_REQUEST
					WHERE TRUNC(TGL_NOTA) > TO_DATE('$tgl_awal','yyyy-mm-dd') AND TRUNC(TGL_NOTA) < TO_DATE('$tgl_akhir','yyyy-mm-dd')) 
					WHERE KEGIATAN LIKE '%$jenis%'";
        $row_list        = DB::connection('uster')->select($query_list_);

        $n = 0;
        $html1 = "";
        $html2 = "";
        $html3 = "";
        foreach ($row_list as $row) {
            $n++;
            //print_r($n);die;
            if ($row->asal == "TPK") {
                $no_request = $row->no_request;
                //echo $no_request; die;
                //$cont_ungate = get_detail($no_request);
                $query_cek_ = "SELECT * FROM (
								SELECT CONTAINER_DELIVERY.NO_REQUEST NO_REQUEST, CONTAINER_DELIVERY.NO_CONTAINER NO_CONTAINER, BORDER_GATE_IN.TGL_IN TGL
								FROM CONTAINER_DELIVERY LEFT JOIN BORDER_GATE_IN ON CONTAINER_DELIVERY.NO_REQUEST = BORDER_GATE_IN.NO_REQUEST
								UNION
								SELECT CONTAINER_RECEIVING.NO_REQUEST NO_REQUEST, CONTAINER_RECEIVING.NO_CONTAINER NO_CONTAINER, BORDER_GATE_IN.TGL_IN TGL
								FROM CONTAINER_RECEIVING LEFT JOIN BORDER_GATE_IN ON CONTAINER_RECEIVING.NO_REQUEST = BORDER_GATE_IN.NO_REQUEST
								UNION
								SELECT CONTAINER_STRIPPING.NO_REQUEST NO_REQUEST, CONTAINER_STRIPPING.NO_CONTAINER NO_CONTAINER, BORDER_GATE_IN.TGL_IN TGL
								FROM CONTAINER_STRIPPING LEFT JOIN BORDER_GATE_IN ON CONTAINER_STRIPPING.NO_REQUEST = BORDER_GATE_IN.NO_REQUEST
								UNION
								SELECT CONTAINER_STUFFING.NO_REQUEST NO_REQUEST, CONTAINER_STUFFING.NO_CONTAINER NO_CONTAINER, BORDER_GATE_IN.TGL_IN TGL
								FROM CONTAINER_STUFFING LEFT JOIN BORDER_GATE_IN ON CONTAINER_STUFFING.NO_REQUEST = BORDER_GATE_IN.NO_REQUEST)
								WHERE NO_REQUEST LIKE '$no_request'";
                $row_ungate = DB::connection('uster')->select($query_cek_);

                $jumlah = count($row_ungate);
                foreach ($row_ungate as $rowsa) {
                    $html3 .= " " . $rowsa->no_container . " ";
                }
                $html2 = $jumlah . " container belum Gate IN (" . $html3 . ") <br/>";
                //echo $html2; die;
                $status = "Not Ready to Transfer";
                if ($jumlah == 0) {
                    $status = "Ready to Transfer";
                    $html2 = " ";
                }
            } else {
                $status = "Ready to Transfer";
            }
            $html1 .= "<tr>
				<td>	" . $n . "</td>
				<td>	" . $row->no_nota . "</td>
				<td>	" . $row->no_request . "</td>
				<td>	" . $row->kegiatan . "</td>
				<td>	" . $row->tgl_nota . "</td>
				<td>	" . $row->emkl . "</td>
				<td>	" . $row->total_tagihan . "</td>
				<td>	" . $status . "</td>		
				<td>	" . $html2 . "</td>
				</tr>";
            $html1 = "";
            $html2 = "";
            $html3 = "";
        }

        return $html1;
    }


    //REQUEST
    function DataTableRequest($request)
    {

        $no_request = $request->NO_REQUEST;
        $kegiatan = $request->KEGIATAN;

        if ($kegiatan == 'DELIVERY') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.tgl_delivery tgl_akhir, mc.size_, mc.type_, cd.status, cd.hz, cd.komoditi, cd.berat
				from container_delivery cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'STRIPPING' || $kegiatan == 'RELOKASI_MTY_EKS_STRIPPING') {
            $query = "select cd.no_container, cd.tgl_bongkar tgl_awal, case when cd.tgl_selesai is null then cd.tgl_bongkar+4 else cd.tgl_selesai end tgl_akhir,
				mc.size_, mc.type_, 'FCL' status , cd.hz, cd.commodity komoditi, '22000' berat
				from container_stripping cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'STUFFING') {
            $query = "select cd.no_container, cd.start_stack tgl_awal, cd.start_perp_pnkn tgl_akhir,
				mc.size_, mc.type_, 'MTY' status , cd.hz, cd.commodity komoditi, cd.berat
				from container_stuffing cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'RECEIVING') {
            $query = "select cd.no_container, '' tgl_awal, '' tgl_akhir,
				mc.size_, mc.type_, 'MTY' status , case when cd.hz is null then 'N' else cd.hz end hz,
				cd.komoditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
				from container_receiving cd inner join master_container mc 
				on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        } else if ($kegiatan == 'BATAL_MUAT') {
            $query = "select cd.no_container, cd.start_pnkn tgl_awal , cd.end_pnkn tgl_akhir,
                mc.size_, mc.type_, cd.status status , 'N' hz,
                '' komiditi , case when mc.size_ = '20' then '2000' else '4000' end as berat
                from container_batal_muat cd inner join master_container mc 
                on cd.no_container = mc.no_container
				where no_request = '$no_request'";
        }


        return DB::connection('uster')->select($query);
    }

    function notaRequest($request)
    {
        $no_nota        = strtoupper($request);
        $query             = "SELECT NO_REQUEST, 'DELIVERY' KEGIATAN FROM REQUEST_DELIVERY WHERE NO_REQUEST LIKE '%$no_nota%'
        UNION
        SELECT NO_REQUEST, 'STRIPPING' KEGIATAN FROM REQUEST_STRIPPING WHERE NO_REQUEST LIKE '%$no_nota%'
        UNION
        SELECT NO_REQUEST, 'STUFFING' KEGIATAN FROM REQUEST_STUFFING WHERE NO_REQUEST LIKE '%$no_nota%'
        UNION
        SELECT NO_REQUEST, 'RECEIVING' KEGIATAN FROM REQUEST_RECEIVING WHERE NO_REQUEST LIKE '%$no_nota%'
        UNION
        SELECT NO_REQUEST, 'RELOKASI' KEGIATAN FROM REQUEST_RECEIVING WHERE NO_REQUEST LIKE '%$no_nota%'";
        return DB::connection('uster')->select($query);
    }

    //AREAA
    function DataTableArea($request)
    {

        $option_status    = $request->option_status;
        $option_locate    = $request->option_locate;

        if ($option_locate == 'stripping') {
            $option_locate    = "where ba.keterangan = 'STRIPING'";
        } else if ($option_locate == 'stuffing') {
            $option_locate    = "where ba.keterangan = 'STUFFING'";
        } else if ($option_locate == 'mty') {
            $option_locate    = "where ba.keterangan = 'EMPTY'";
        } else {
            $option_locate    = " ";
        }


        $query_list_ = "select hs.no_container, mc.size_, mc.type_, hs.status_cont, pl.id_blocking_area, ba.name block_, pl.slot_,
                        pl.row_,pl.tier_, pl.tgl_placement, hs.no_booking, ba.keterangan kegiatan, mu.nama_lengkap
                        from placement pl inner join blocking_area ba on pl.id_blocking_area = ba.id
                        inner join history_container hs on pl.no_container = hs.no_container and
                        hs.tgl_update = (select max(tgl_update) from history_container where no_container = hs.no_container)
                        inner join master_container mc on hs.no_container = mc.no_container and
                        mc.counter = hs.counter
                        left join master_user mu on pl.user_name = mu.username " . $option_locate . " ";
        if ($option_status != 'ALL') {
            $query_list_ .= "and hs.status_cont = '$option_status'";
        }


        return DB::connection('uster')->select($query_list_);
    }
}
