<?php

namespace App\Services\Report;

use Exception;
use Illuminate\Support\Facades\DB;

class DeliveryPerKapalService
{

   function DataTable($request)
   {
      $nm_kapal    = $request->NM_KAPAL;
      $voyage_in    = $request->VOYAGE;
      $kegiatan    = $request->KEGIATAN;
      // $no_booking = $request->NO_BOOKING;
      $status      = $request->STATUS;
      //	$delivery	= $request->DELIVERY_KE;

      if ($status == NULL) {
         $query_status = '';
      } else {
         if ($status == 'FCL') {
            $query_status = "and b.status = 'FCL'";
         } else if ($status == 'MTY') {
            $query_status = "and b.status = 'MTY'";
         } else if ($status == 'LCL') {
            $query_status = "and b.status = 'LCL'";
         } else {
            $query_status = "";
         }
      }
      $query = "SELECT
				b.no_container,
				mc.size_,
				mc.type_,
				a.no_request,
				b.status,
				b.via,
				c.nopol,
				TO_CHAR(c.tgl_in, 'dd/mm/rrrr hh:ii:ss') tgl_in,
				nvl((SELECT username FROM master_user WHERE to_char(id)= to_char(c.id_user)), c.id_user) username,
				n.no_faktur no_nota,
				n.lunas
			FROM
				request_delivery a,
				container_delivery b,
				master_container mc,
				border_gate_out c,
				nota_delivery n
			WHERE
				a.no_request = b.no_request
				AND b.no_request = c.no_request(+)
				AND b.no_container = c.no_container(+)
				AND b.no_container = mc.no_container
				AND a.no_request = n.no_request(+)
				AND n.status NOT IN ('BATAL')
				$query_status
				AND a.VESSEL = '$nm_kapal'
				AND a.O_VOYIN = '$voyage_in'
			ORDER BY
				c.tgl_in DESC";

      return DB::connection('uster')->select($query);
   }
   function masterVessel($request)
   {
      $nama_kapal        = strtoupper($request);
      $query            = "SELECT
                              a.VOYAGE,
                              a.O_VOYIN VOYAGE_IN,
                              a.O_VOYOUT,
                              a.VESSEL NM_KAPAL
                           FROM request_delivery a,
                              container_delivery b,
                              master_container mc,
                              border_gate_out c,
                              nota_delivery n
                           WHERE
                              a.no_request = b.no_request
                              AND b.no_request = c.no_request(+)
                              AND b.no_container = c.no_container(+)
                              AND b.no_container = mc.no_container
                              --and C.VIA is null
                              AND a.no_request = n.no_request(+)
                              AND n.status NOT IN ('BATAL')
                              AND VESSEL LIKE '%$nama_kapal%'
                              AND a.O_VOYIN IS NOT NULL
                           GROUP BY
                              a.VOYAGE,
                              a.O_VOYIN,
                              a.O_VOYOUT,
                              a.VESSEL
                           ORDER BY O_VOYIN DESC";


      return DB::connection('uster')->select($query);
   }

   function generatePdf($request)
   {
      $tgl_awal   = $request->tgl_awal;
      $tgl_akhir  = $request->tgl_akhir;
      $jenis      = $request->jenis;

      $query_list_    = "SELECT * FROM (
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

      $row_list      = DB::connection('uster')->select($query_list_);

      $n = 0;
      $html1 = "";
      $html2 = "";
      $html3 = "";
      foreach ($row_list as $row) {
         $n++;
         if ($row->asal == "TPK") {
            $no_request = $row->no_request;
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
               $html3 .= " " . $row->no_container . " ";
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
}
