<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\DB;

class ContainerService
{

    /**
     * Fungsi ini digunakan untuk mendapatkan riwayat kontainer berdasarkan nomor kontainer.
     *
     * @param string $noContainer Nomor kontainer yang akan dicari riwayatnya.
     * @return array Array berisi riwayat kontainer yang sesuai dengan nomor kontainer yang diberikan.
     */
    public function listContainerHistory($noContainer)
    {
        $sql = "SELECT * FROM (SELECT Q.NO_CONTAINER, Q.SIZE_, Q.TYPE_, Q.LOCATION, Q.TIME_, Q.NO_BOOKING, Q.COUNTER,
            CASE WHEN Q.NO_BOOKING LIKE 'BP%' THEN '[I]'
            WHEN Q.NO_BOOKING LIKE 'VESSEL_%' THEN
                    CASE WHEN W.KEGIATAN = 'REQUEST RECEIVING' THEN  '[I]' ELSE '[E]' END
            ELSE '[E]'
            END IE, W.KEGIATAN, Q.NM_KAPAL, Q.VOYAGE_IN
            FROM (SELECT DISTINCT MC.NO_CONTAINER, MC.SIZE_, MC.TYPE_, MC.LOCATION, P.NO_BOOKING, MAX(P.TGL_UPDATE) TIME_, P.COUNTER , PK.NM_KAPAL, PK.VOYAGE_IN
                                FROM MASTER_CONTAINER MC
                                JOIN HISTORY_CONTAINER P ON MC.NO_CONTAINER = P.NO_CONTAINER
                                LEFT JOIN V_PKK_CONT PK ON REPLACE(P.NO_BOOKING, 'VESSEL_NOTHING', 'BSK100000023') = PK.NO_BOOKING
                                WHERE MC.NO_CONTAINER like '%$noContainer%'
                                --AND P.KEGIATAN IN ('REQUEST RECEIVING','REQUEST DELIVERY')
                                GROUP BY MC.NO_CONTAINER, MC.SIZE_, MC.TYPE_, MC.LOCATION, P.NO_BOOKING, P.COUNTER, PK.NM_KAPAL, PK.VOYAGE_IN
                                ORDER BY TIME_ DESC) Q,
            (SELECT NO_REQUEST,COUNTER, NO_BOOKING,KEGIATAN,NO_CONTAINER FROM HISTORY_CONTAINER) W
            WHERE Q.NO_BOOKING = W.NO_BOOKING(+)
            AND Q.COUNTER = W.COUNTER(+)
            AND Q.NO_CONTAINER = W.NO_CONTAINER(+)
            AND W.NO_BOOKING(+) = 'VESSEL_NOTHING'
            AND W.KEGIATAN(+) = 'REQUEST RECEIVING'
            AND Q.NO_BOOKING IS NOT NULL
        ORDER BY TIME_ DESC ) WHERE ROWNUM <= 6";

        return DB::connection('uster')->select($sql);
    }

    /**
     * Mendapatkan status dan tanggal update dari sebuah kontainer berdasarkan nomor kontainer dan nomor booking.
     *
     * @param string $no_cont Nomor kontainer.
     * @param string $no_booking Nomor booking.
     * @return object|null Objek hasil query atau null jika tidak ditemukan.
     */
    function getStatusContainer($no_cont, $no_booking)
    {
        $query = "SELECT P.STATUS_CONT, P.TGL_UPDATE FROM HISTORY_CONTAINER P
        WHERE P.NO_CONTAINER = '$no_cont' AND P.NO_BOOKING = '$no_booking'
        ORDER BY P.TGL_UPDATE DESC";
        return DB::connection('uster')->selectOne($query);
    }

    /**
     * Mengambil lokasi dari nomor kontainer yang diberikan.
     *
     * @param string $no_cont Nomor kontainer yang akan dicari lokasinya.
     * @return mixed Objek hasil query yang berisi lokasi kontainer.
     */
    function getLocation($no_cont)
    {
        $query = "SELECT LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
        return DB::connection('uster')->selectOne($query);
    }

    /**
     * Mendapatkan nomor booking berdasarkan nomor kontainer.
     *
     * @param string $no_cont Nomor kontainer.
     * @return mixed Objek hasil query nomor booking.
     */
    function getBooking($no_cont)
    {
        $query = "select no_booking from request_delivery, container_delivery
        where request_delivery.no_request = container_delivery.no_request and delivery_ke = 'TPK'
        AND to_date(tgl_request, 'YYYY-MM-DD HH24:MI:SS') BETWEEN to_date('2013-04-09 00:00:00', 'YYYY-MM-DD HH24:MI:SS') AND to_date('2013-04-09 00:00:00', 'YYYY-MM-DD HH24:MI:SS')
        and jn_repo = 'EMPTY'
        and no_container = '$no_cont'";
        return  DB::connection('uster')->selectOne($query);
    }

    /**
     * Mengambil data kontainer berdasarkan nomor booking.
     *
     * @param string $no_book Nomor booking kontainer.
     * @return object|null Objek hasil query atau null jika tidak ditemukan.
     */
    function ContainerVessel($no_book)
    {
        $query = "SELECT A.NO_UKK, A.NM_KAPAL, A.VOYAGE_IN, A.VOYAGE_OUT, A.NO_BOOKING, '' BP_ID FROM v_pkk_cont A
        WHERE A.NO_BOOKING = '$no_book'";
        return DB::connection('uster')->selectOne($query);
    }


    /**
     * Mendapatkan detail kontainer berdasarkan nomor kontainer, tindakan, counter, dan nomor booking.
     *
     * @param string $no_cont Nomor kontainer.
     * @param string $act Tindakan yang dilakukan (handling atau stripping).
     * @param string $counter Counter.
     * @param string $no_booking Nomor booking.
     * @return array|null Detail kontainer jika ditemukan, null jika tidak ditemukan.
     */
    function getDetail($no_cont, $act, $counter, $no_booking)
    {
        if ($act == "handling") {
            if ($no_booking == "VESSEL_NOTHING") {
                $q_detail = "SELECT MC.NO_CONTAINER, HC.STATUS_CONT, HC.ID_YARD ,YAR.NAMA_YARD, HC.KEGIATAN,
                    CASE WHEN HC.KEGIATAN = 'REALISASI STRIPPING'
                    THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi')  FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RST.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REALISASI STUFFING'
                    THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi') FROM CONTAINER_STUFFING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RSF.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'BORDER GATE IN'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RST.NO_REQUEST OR NO_REQUEST = RSF.NO_REQUEST OR NO_REQUEST = RR.NO_REQUEST))
                     WHEN HC.KEGIATAN = 'BORDER GATE OUT'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RR.NO_REQUEST OR NO_REQUEST = RD.NO_REQUEST))
                    WHEN HC.KEGIATAN = 'GATE OUT'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RST.NO_REQUEST OR NO_REQUEST = RSF.NO_REQUEST OR NO_REQUEST = RR.NO_REQUEST OR NO_REQUEST = RD.NO_REQUEST))
                    WHEN HC.KEGIATAN = 'GATE IN'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST DELIVERY'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_DELIVERY WHERE NO_REQUEST = RD.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST RECEIVING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_RECEIVING WHERE NO_REQUEST = RR.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST STRIPPING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STRIPPING WHERE NO_REQUEST = RST.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST STUFFING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STUFFING WHERE NO_REQUEST = RSF.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST BATALMUAT'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_BATAL_MUAT WHERE NO_REQUEST = RBM.NO_REQUEST)
                    ELSE to_char(HC.TGL_UPDATE,'DD-MM-YYYY hh24:mi')
                    END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, HC.ID_USER, MU.NAME NAMA_LENGKAP, RR.NO_REQUEST NO_REQ_REC,
                    RST.NO_REQUEST NO_REQ_STR, RD.NO_REQUEST NO_REQ_DEL, RSF.NO_REQUEST NO_REQ_STF, REL.NO_REQUEST NO_REQ_REL, RBM.NO_REQUEST NO_REQ_RBM
                    FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
                    ON MC.NO_CONTAINER = HC.NO_CONTAINER
                    LEFT JOIN REQUEST_RECEIVING RR
                    ON RR.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_STRIPPING RST
                    ON RST.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_DELIVERY RD
                    ON RD.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_STUFFING RSF
                    ON RSF.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_RELOKASI REL
                    ON REL.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_BATAL_MUAT RBM
                    ON RBM.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN YARD_AREA YAR ON HC.ID_YARD = YAR.ID
                    LEFT JOIN billing_nbs.tb_user MU ON to_char(MU.ID) = HC.ID_USER
                    WHERE MC.NO_CONTAINER = '$no_cont'
                    AND HC.NO_BOOKING = '$no_booking'
                    AND HC.COUNTER = '$counter'
                    ORDER BY HC.TGL_UPDATE DESC";
            } else {
                $q_detail = "SELECT MC.NO_CONTAINER, HC.STATUS_CONT, HC.ID_YARD ,YAR.NAMA_YARD, HC.KEGIATAN,
                    CASE WHEN HC.KEGIATAN = 'REALISASI STRIPPING'
                    THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi')  FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RST.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REALISASI STUFFING'
                    THEN (SELECT to_char(MAX(TGL_REALISASI),'DD-MM-YYYY hh24:mi') FROM CONTAINER_STUFFING WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RSF.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'BORDER GATE IN'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RST.NO_REQUEST OR NO_REQUEST = RSF.NO_REQUEST OR NO_REQUEST = RR.NO_REQUEST))
                     WHEN HC.KEGIATAN = 'BORDER GATE OUT'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM BORDER_GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RR.NO_REQUEST OR NO_REQUEST = RD.NO_REQUEST))
                    WHEN HC.KEGIATAN = 'GATE OUT'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_OUT WHERE NO_CONTAINER = MC.NO_CONTAINER AND (NO_REQUEST = RST.NO_REQUEST OR NO_REQUEST = RSF.NO_REQUEST OR NO_REQUEST = RR.NO_REQUEST OR NO_REQUEST = RD.NO_REQUEST))
                    WHEN HC.KEGIATAN = 'GATE IN'
                    THEN (SELECT to_char(MAX(TGL_IN),'DD-MM-YYYY hh24:mi') FROM GATE_IN WHERE NO_CONTAINER = MC.NO_CONTAINER AND NO_REQUEST = RR.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST DELIVERY'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_DELIVERY WHERE NO_REQUEST = RD.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST RECEIVING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_RECEIVING WHERE NO_REQUEST = RR.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST STRIPPING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STRIPPING WHERE NO_REQUEST = RST.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST STUFFING'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_STUFFING WHERE NO_REQUEST = RSF.NO_REQUEST)
                    WHEN HC.KEGIATAN = 'REQUEST BATALMUAT'
                    THEN (SELECT to_char(TGL_REQUEST,'DD-MM-YYYY hh24:mi') FROM REQUEST_BATAL_MUAT WHERE NO_REQUEST = RBM.NO_REQUEST)
                    ELSE to_char(HC.TGL_UPDATE,'DD-MM-YYYY hh24:mi')
                    END TGL_UPDATE, HC.NO_BOOKING NO_BOOKING, HC.ID_USER, MU.NAME NAMA_LENGKAP, RR.NO_REQUEST NO_REQ_REC,
                    RST.NO_REQUEST NO_REQ_STR, RD.NO_REQUEST NO_REQ_DEL, RSF.NO_REQUEST NO_REQ_STF, REL.NO_REQUEST NO_REQ_REL, RBM.NO_REQUEST NO_REQ_RBM
                    FROM MASTER_CONTAINER MC INNER JOIN HISTORY_CONTAINER HC
                    ON MC.NO_CONTAINER = HC.NO_CONTAINER
                    LEFT JOIN REQUEST_RECEIVING RR
                    ON RR.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_STRIPPING RST
                    ON RST.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_DELIVERY RD
                    ON RD.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_STUFFING RSF
                    ON RSF.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_RELOKASI REL
                    ON REL.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN REQUEST_BATAL_MUAT RBM
                    ON RBM.NO_REQUEST = HC.NO_REQUEST
                    LEFT JOIN YARD_AREA YAR ON HC.ID_YARD = YAR.ID
                    LEFT JOIN billing_nbs.tb_user MU ON TO_CHAR(MU.ID) = HC.ID_USER
                    WHERE MC.NO_CONTAINER = '$no_cont'
                    AND HC.NO_BOOKING = '$no_booking'
                    AND HC.COUNTER = '$counter'
                    ORDER BY HC.TGL_UPDATE DESC";
            }
        } else if ($act == "stripping") {
            $q_get_req_str = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.KEGIATAN = 'REQUEST STRIPPING' AND HC.COUNTER = '$counter'";
            $res_detail1 = DB::connection('uster')->selectOne($q_get_req_str);
            $no_req_str = $res_detail1->no_request ?? NULL;
            if ($no_req_str == NULL) {
                exit();
            }
            $q_detail = "SELECT DISTINCT REQUEST_STRIPPING.NO_REQUEST,
                    REQUEST_STRIPPING.TGL_REQUEST,
                    REQUEST_STRIPPING.NO_DO,
                    REQUEST_STRIPPING.NO_BL,
                    REQUEST_STRIPPING.PERP_KE,
                    REQUEST_STRIPPING.STATUS_REQ,
                    REQUEST_STRIPPING.TYPE_STRIPPING,
                    CONTAINER_STRIPPING.TGL_BONGKAR TGL_MULAI,
                    CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI IS NULL
                    THEN CONTAINER_STRIPPING.TGL_BONGKAR+4
                     ELSE CONTAINER_STRIPPING.TGL_SELESAI
                     END  TGL_SELESAI,
                     CONTAINER_STRIPPING.TGL_APPROVE,
                     CONTAINER_STRIPPING.TGL_APP_SELESAI,
                     CONTAINER_STRIPPING.START_PERP_PNKN,
                     CONTAINER_STRIPPING.END_STACK_PNKN,
                    CONTAINER_STRIPPING.TGL_REALISASI,
                    CONTAINER_STRIPPING.ID_USER_REALISASI,
                    MUS.NAME NAMA_LKP,
                    emkl.NM_PBM AS NAMA_CONSIGNEE,
                    pnmt.NM_PBM AS NAMA_PENUMPUK,
                    NOTA_STRIPPING.NO_NOTA,
                    NOTA_STRIPPING.NO_FAKTUR,
                    CASE WHEN NOTA_STRIPPING.STATUS = 'BATAL'
                        THEN 'BATAL'
                        ELSE NOTA_STRIPPING.LUNAS
                    END LUNAS
                    FROM REQUEST_STRIPPING INNER JOIN v_mst_pbm emkl ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                    JOIN v_mst_pbm pnmt ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM AND pnmt.KD_CABANG = '05'
                    JOIN REQUEST_RECEIVING ON REQUEST_RECEIVING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST_RECEIVING
                    JOIN CONTAINER_STRIPPING ON CONTAINER_STRIPPING.NO_REQUEST = REQUEST_STRIPPING.NO_REQUEST
                    LEFT JOIN billing_nbs.tb_user MUS ON CONTAINER_STRIPPING.ID_USER_REALISASI = MUS.ID
                    LEFT JOIN NOTA_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST
                    LEFT JOIN HISTORY_CONTAINER HC ON CONTAINER_STRIPPING.NO_CONTAINER = HC.NO_CONTAINER
                    AND CONTAINER_STRIPPING.NO_REQUEST = HC.NO_REQUEST
                    WHERE CONTAINER_STRIPPING.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.COUNTER = '$counter'
                    order by REQUEST_STRIPPING.TGL_REQUEST desc";
        } else if ($act == "receiving") {
            if ($no_booking == "VESSEL_NOTHING") {
                $q_get_req_rec = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.COUNTER = '$counter' AND HC.KEGIATAN = 'REQUEST RECEIVING'";
            } else {
                $q_get_req_rec = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND  HC.KEGIATAN = 'REQUEST RECEIVING' AND HC.COUNTER = '$counter'";
            }
            $res_detail2 = DB::connection('uster')->selectOne($q_get_req_rec);
            $no_req_rec = $res_detail2->no_request ?? NULL;
            if ($no_booking != "VESSEL_NOTHING") {
                $q_detail = "SELECT DISTINCT a.NO_REQUEST AS NO_REQUEST,
                                        a.TGL_REQUEST,
                                      a.KETERANGAN AS KETERANGAN,
                                      a.RECEIVING_DARI AS RECEIVING_DARI,
                                      A.PERALIHAN AS PERALIHAN,
                                      a.KD_CONSIGNEE AS KD_CONSIGNEE,
                                      A.NO_DO, A.NO_BL, cr.AKTIF,
                                      d.NM_PBM AS CONSIGNEE,
                                      d.NO_NPWP_PBM AS NO_NPWP_PBM,
                                      d.ALMT_PBM AS ALMT_PBM,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE nr.NO_NOTA
                                      END NO_NOTA,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE nr.NO_FAKTUR
                                      END NO_FAKTUR,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE CASE WHEN nr.STATUS = 'BATAL' THEN 'BATAL' ELSE nr.LUNAS END
                                      END LUNAS,
                                      cr.STATUS_REQ
                               FROM   REQUEST_RECEIVING a
                               INNER JOIN CONTAINER_RECEIVING cr ON A.NO_REQUEST = CR.NO_REQUEST
                               LEFT JOIN NOTA_RECEIVING nr ON nr.NO_REQUEST = a.NO_REQUEST
                               LEFT JOIN  V_MST_PBM d ON a.KD_CONSIGNEE = d.KD_PBM
                               JOIN master_container on MASTER_CONTAINER.NO_CONTAINER = cr.no_container
                               join history_container on history_container.no_container = cr.no_container and history_container.NO_REQUEST = cr.NO_REQUEST
                               WHERE history_container.no_booking = '$no_booking'
                               AND CR.NO_CONTAINER = '$no_cont'
                               AND history_container.COUNTER = '$counter'";
            } else {
                $q_detail = "SELECT DISTINCT a.NO_REQUEST AS NO_REQUEST,
                                        a.TGL_REQUEST,
                                      a.KETERANGAN AS KETERANGAN,
                                      a.RECEIVING_DARI AS RECEIVING_DARI,
                                      A.PERALIHAN AS PERALIHAN,
                                      a.KD_CONSIGNEE AS KD_CONSIGNEE,
                                      A.NO_DO, A.NO_BL, cr.AKTIF,
                                      d.NM_PBM AS CONSIGNEE,
                                      d.NO_NPWP_PBM AS NO_NPWP_PBM,
                                      d.ALMT_PBM AS ALMT_PBM,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE nr.NO_NOTA
                                      END NO_NOTA,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE nr.NO_FAKTUR
                                      END NO_FAKTUR,
                                      CASE WHEN A.PERALIHAN = 'STRIPPING' OR A.PERALIHAN = 'STUFFING'
                                      THEN 'AUTO_RECEIVE'
                                      ELSE CASE WHEN nr.STATUS = 'BATAL' THEN 'BATAL' ELSE nr.LUNAS END
                                      END LUNAS,
                                      cr.STATUS_REQ
                               FROM   REQUEST_RECEIVING a
                               INNER JOIN CONTAINER_RECEIVING cr ON A.NO_REQUEST = CR.NO_REQUEST
                               LEFT JOIN NOTA_RECEIVING nr ON nr.NO_REQUEST = a.NO_REQUEST
                               LEFT JOIN  V_MST_PBM d ON a.KD_CONSIGNEE = d.KD_PBM
                               JOIN master_container on MASTER_CONTAINER.NO_CONTAINER = cr.no_container
                               WHERE CR.NO_REQUEST = '$no_req_rec' AND CR.NO_CONTAINER = '$no_cont'";
            }
        } else if ($act == "stuffing") {
            $q_get_req_stu = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.KEGIATAN = 'REQUEST STUFFING' AND HC.COUNTER = '$counter'";
            $r_req3 = DB::connection('uster')->selectOne($q_get_req_stu);
            $no_req_stu = $r_req3->no_request ?? NULL;

            $q_detail = "SELECT DISTINCT CS.NO_CONTAINER, CS.ASAL_CONT, CS.TGL_REALISASI, CS.ID_USER_REALISASI, MUS.NAMA_LENGKAP NAMA_LKP, rs.*,
                            EMKL.NM_PBM, cs.TGL_APPROVE,
                            case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN cs.START_STACK
                                WHEN rs.status_req = 'PERP' then cs.START_PERP_PNKN
                                WHEN cs.REMARK_SP2 = 'Y' then cs.START_PERP_PNKN
                            else cs.START_STACK
                            END AS START_STACK,
                            case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN cs.START_PERP_PNKN
                                WHEN rs.status_req = 'PERP' then cs.END_STACK_PNKN
                                WHEN cs.REMARK_SP2 = 'Y' then cs.END_STACK_PNKN
                            else cs.START_PERP_PNKN
                            END AS START_PERP_PNKN,
                            case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN 'ALIH_KAPAL'
                                WHEN rs.status_req = 'PERP' then ns.NO_NOTA
                            else ns.NO_NOTA
                            END AS NO_NOTA,
                            case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN 'ALIH_KAPAL'
                                WHEN rs.status_req = 'PERP' then ns.NO_FAKTUR
                            else ns.NO_FAKTUR
                            END AS NO_FAKTUR,
                             case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN (SELECT LUNAS FROM NOTA_BATAL_MUAT, REQUEST_BATAL_MUAT
                             WHERE NOTA_BATAL_MUAT.NO_REQUEST = REQUEST_BATAL_MUAT.NO_REQUEST AND REQUEST_BATAL_MUAT.NO_REQ_BARU = RS.NO_REQUEST)
                                WHEN rs.status_req = 'PERP' then ns.LUNAS
                            else ns.LUNAS
                            END AS LUNAS,
                             case when rs.STUFFING_DARI = 'AUTO' and rs.status_req is null
                                THEN (SELECT STATUS FROM NOTA_BATAL_MUAT, REQUEST_BATAL_MUAT
                             WHERE NOTA_BATAL_MUAT.NO_REQUEST = REQUEST_BATAL_MUAT.NO_REQUEST AND REQUEST_BATAL_MUAT.NO_REQ_BARU = RS.NO_REQUEST)
                                WHEN rs.status_req = 'PERP' then ns.STATUS
                            else ns.STATUS
                            END AS STATUS
                           FROM REQUEST_STUFFING rs
                           INNER JOIN CONTAINER_STUFFING cs ON RS.NO_REQUEST = CS.NO_REQUEST
                           LEFT JOIN V_MST_PBM emkl ON rs.KD_CONSIGNEE = EMKL.KD_PBM
                           AND rs.KD_PENUMPUKAN_OLEH = EMKL.KD_PBM
                           LEFT JOIN MASTER_USER MUS ON cs.ID_USER_REALISASI = MUS.ID
                           LEFT JOIN NOTA_STUFFING ns ON rs.NO_REQUEST = ns.NO_REQUEST
                           JOIN master_container on MASTER_CONTAINER.NO_CONTAINER = cs.no_container
                           left join history_container on history_container.no_container = cs.no_container and history_container.no_request = cs.no_request
                           WHERE history_container.no_booking = '$no_booking' and
                           CS.NO_CONTAINER = '$no_cont' and history_container.COUNTER = '$counter'
                           order by rs.tgl_request desc, rs.no_request";
        } else if ($act == "delivery") {
            if ($no_booking == "VESSEL_NOTHING") {
                $q_get_req_del = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.COUNTER = '$counter' AND HC.KEGIATAN = 'REQUEST DELIVERY'";
                $add_q = "AND HISTORY_CONTAINER.COUNTER = '$counter'";
            } else {
                $q_get_req_del = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.KEGIATAN = 'REQUEST DELIVERY' AND HC.COUNTER = '$counter'";
                $add_q = "";
            }
            $r_req4 = DB::connection('uster')->selectOne($q_get_req_del);

            $no_req_del = $r_req4->no_request ?? '';

            $q_detail = " SELECT distinct
                        case when request_delivery.status = 'PERP'
                            THEN container_delivery.start_perp+1
                            else container_delivery.start_stack
                         end start_stack, container_delivery.tgl_delivery, container_delivery.komoditi, container_delivery.aktif, container_delivery.hz, request_delivery.peralihan, request_delivery.delivery_ke,
                        request_delivery.DELIVERY_KE, REQUEST_DELIVERY.NO_REQUEST, request_delivery.tgl_request,
                        emkl.NM_PBM AS NAMA_EMKL, emkl.ALMT_PBM, emkl.NO_NPWP_PBM, request_delivery.VESSEL, request_delivery.VOYAGE,
                        case when request_delivery.status = 'AUTO_REPO' then 'AUTO_REPO_EX_BATALMUAT'
                        ELSE nota_delivery.no_nota END no_nota,
                        case when request_delivery.status = 'AUTO_REPO' then 'AUTO_REPO_EX_BATALMUAT'
                        ELSE nota_delivery.no_faktur END no_faktur,
                        CASE when request_delivery.status = 'AUTO_REPO' THEN nota_batal_muat.lunas
                        ELSE
                        CASE when nota_delivery.status = 'BATAL' THEN 'BATAL'
                        ELSE nota_delivery.lunas END END lunas, request_delivery.jn_repo, request_delivery.PERP_KE
                        FROM REQUEST_DELIVERY INNER JOIN CONTAINER_DELIVERY ON REQUEST_DELIVERY.NO_REQUEST = CONTAINER_DELIVERY.NO_REQUEST
                        left join nota_delivery on request_delivery.no_request = nota_delivery.no_request
                        LEFT JOIN v_mst_pbm emkl ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM and emkl.kd_cabang = '05'
                        JOIN master_container on MASTER_CONTAINER.NO_CONTAINER = container_delivery.no_container
                        left join request_batal_muat bmu on  request_delivery.no_request = bmu.no_req_baru
                        left join nota_batal_muat on bmu.no_request = nota_batal_muat.no_request
                        left join history_container on history_container.no_container = CONTAINER_DELIVERY.no_container and history_container.no_request = CONTAINER_DELIVERY.no_request
                        WHERE CONTAINER_DELIVERY.NO_CONTAINER = '$no_cont' AND
                        history_container.COUNTER = '$counter' AND history_container.no_booking = '$no_booking' " . $add_q . "
                        --AND CONTAINER_DELIVERY.NO_REQUEST = '$no_req_del'
                        order by request_delivery.tgl_request desc";
        } else if ($act == "placement") {
            if ($no_booking == "VESSEL_NOTHING") {
                $q_get_req_pl = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.COUNTER = '$counter' AND HC.KEGIATAN = 'REQUEST RECEIVING'";
            } else {
                $q_get_req_pl = "SELECT HC.NO_REQUEST FROM HISTORY_CONTAINER HC
                    WHERE HC.NO_CONTAINER = '$no_cont' AND HC.NO_BOOKING = '$no_booking' AND HC.KEGIATAN = 'REQUEST RECEIVING' AND HC.COUNTER = '$counter'";
            }
            $r_req5 = DB::connection('uster')->selectOne($q_get_req_pl);
            $no_req_pl = $r_req5->no_request ?? NULL;
            $q_detail = "SELECT CEK.*,  CASE WHEN CEK.INSERT_VIA = 'H' THEN 'HANDHELD' ELSE 'DESKTOP' END VIA FROM (SELECT HISTORY_PLACEMENT.*, MASTER_USER.NAME NAMA_LENGKAP, BLOCKING_AREA.NAME, YARD_AREA.NAMA_YARD FROM
                            HISTORY_PLACEMENT INNER JOIN BLOCKING_AREA
                            ON HISTORY_PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID
                            LEFT JOIN YARD_AREA ON BLOCKING_AREA.ID_YARD_AREA = YARD_AREA.ID
                            LEFT JOIN billing_nbs.tb_user MASTER_USER ON (HISTORY_PLACEMENT.NIPP_USER = TO_CHAR(MASTER_USER.ID) OR HISTORY_PLACEMENT.NIPP_USER = MASTER_USER.NIPP) WHERE NO_CONTAINER = '$no_cont'
                            AND HISTORY_PLACEMENT.INSERT_VIA IS NULL
                            UNION ALL
                            SELECT HISTORY_PLACEMENT.*, MASTER_USER.NAME NAMA_LENGKAP, BLOCKING_AREA.NAME, YARD_AREA.NAMA_YARD FROM
                            HISTORY_PLACEMENT INNER JOIN BLOCKING_AREA
                            ON HISTORY_PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID
                            LEFT JOIN YARD_AREA ON BLOCKING_AREA.ID_YARD_AREA = YARD_AREA.ID
                            LEFT JOIN billing_nbs.tb_user MASTER_USER ON HISTORY_PLACEMENT.NIPP_USER = MASTER_USER.ID WHERE NO_CONTAINER = '$no_cont'
                            AND HISTORY_PLACEMENT.INSERT_VIA = 'H'
                            UNION ALL
                            SELECT HISTORY_PLACEMENT.*, MASTER_USER.NAME NAMA_LENGKAP, BLOCKING_AREA.NAME, YARD_AREA.NAMA_YARD FROM
                            HISTORY_PLACEMENT INNER JOIN BLOCKING_AREA
                            ON HISTORY_PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID
                            LEFT JOIN YARD_AREA ON BLOCKING_AREA.ID_YARD_AREA = YARD_AREA.ID
                            LEFT JOIN billing_nbs.tb_user MASTER_USER ON HISTORY_PLACEMENT.NIPP_USER = MASTER_USER.ID WHERE NO_CONTAINER = '$no_cont'
                            AND HISTORY_PLACEMENT.INSERT_VIA = 'DB') CEK
                            ORDER BY CEK.TGL_UPDATE DESC";
        }
        return DB::connection('uster')->select($q_detail);
    }

    function QueryBuilder($query, $filter, $size, $lite = false)
    {
        if ($lite == false) {
            return  "SELECT
            *
            FROM
                ($query)
            WHERE $filter";
        } else {
            return "$query WHERE $filter ";
        }
    }


    function listContainerStatus($request)
    {
        $tgl_awal = date('d/m/Y', strtotime($request->TGL_AWAL));
        $tgl_akhir = date('d/m/Y', strtotime($request->TGL_AKHIR));
        $kegiatan     = $request->KEGIATAN;
        $status     = $request->STATUS;
        $start = $request->start;
        $length = $request->length;
        $limit = ($length * 3) + $start;

        $query_status = '';
        $orderby = '';


        $receiving_luar = "SELECT
            *
        FROM
            (
            SELECT
                a.no_container,
                a.size_,
                a.type_,
                a.status,
                a.tgl_request,
                a.no_request,
                a.emkl,
                a.hz,
                -- a.nama_yard,
                a.nama_lengkap,
                b.tgl_in,
                b.nopol,
                b.no_seal,
                c.tgl_update tgl_placement,
                ba.name nama_blok,
                c.slot_,
                c.row_,
                c.tier_
            FROM
                (
                SELECT
                    a.no_container,
                    a.size_,
                    a.type_,
                    b.status,
                    c.tgl_request,
                    c.no_request,
                    d.emkl,
                    b.hz,
                    --   e.nama_yard,
                    nvl(f.name,(SELECT nama_lengkap FROM master_user WHERE id = c.id_user)) nama_lengkap
                FROM
                    master_container a,
                    container_receiving b,
                    request_receiving c,
                    nota_receiving d,
                    -- yard_area e,
                    billing_nbs.tb_user f
                WHERE
                    a.no_container = b.no_container
                    AND b.no_request = c.no_request
                    AND c.no_request = d.no_request
                    --AND c.id_yard = e.id(+)
                    AND c.id_user = f.id(+)
                    AND d.status <> 'BATAL'
                    AND c.receiving_dari = 'LUAR'
                    AND d.LUNAS = 'YES') a
            LEFT JOIN gate_in b
                    ON
                a.no_container = b.no_container
                AND a.no_request = b.no_request
            LEFT JOIN history_placement c
                    ON
                a.no_container = c.no_container
                AND a.no_request = c.no_request
            LEFT JOIN blocking_area ba ON
                c.id_blocking_area = ba.id)";

        $tgl_app = "SELECT
                a.no_container,
                a.size_,
                a.type_,
                a.status,
                tgl_request,
                a.no_request,
                a.nm_pbm as emkl,
                a.hz,
                a.nama_lengkap,
                a.tgl_approve
            FROM
                (
                SELECT
                    cs.tgl_approve,
                    a.no_container,
                    a.size_,
                    a.type_,
                    CASE
                        WHEN tgL_realisasi IS NULL THEN 'FCL'
                        ELSE 'MTY'
                    END
                                status,
                    tgl_request,
                    c.no_request,
                    pbm.nm_pbm,
                    b.hz,
                    f.nama_lengkap,
                    c.no_request_receiving
                FROM
                    master_container a
                JOIN plan_container_stripping b
                                ON
                    a.no_container = b.no_container
                JOIN history_container hc
                                ON
                    b.no_container = hc.no_container
                    AND hc.no_request = b.no_request
                    AND hc.kegiatan = 'PLAN REQUEST STRIPPING'
                JOIN plan_request_stripping c
                                ON
                    b.no_request = c.no_request
                JOIN v_mst_pbm pbm
                                ON
                    c.kd_consignee = pbm.kd_pbm
                    AND pbm.kd_cabang = '05'
                LEFT JOIN container_stripping cs
                                ON
                    b.no_container = cs.no_container
                    AND b.no_request = REPLACE (cs.no_request,
                    'S',
                    'P')
                LEFT JOIN master_user f
                                ON
                    c.id_user = f.id)a";

        $V_STATUS_STRIPPING_TPK = "
            Select
                a.no_container,
                a.size_,
                a.type_,
                a.status,
                tgl_request,
                a.no_request,
                a.emkl,
                a.hz,
                a.id_yard,
                a.nama_lengkap,
                TO_CHAR (c.tgl_update, 'dd-mm-rrrr hh24:mi:ss') tgl_placement,
                ba.name nama_blok,
                c.slot_,
                c.row_,
                c.tier_,
                TO_CHAR (c1.tgl_update, 'dd-mm-rrrr hh24:mi:ss') tgl_relokasi,
                ba1.name nama_blok_mty,
                c1.slot_ slot2,
                c1.row_ row2,
                c1.tier_ tier2,
                a.tgl_realisasi,
                a.tgl_approve,
                TO_CHAR (b.tgl_in, 'dd-mm-rrrr hh24:mi:ss') tgl_in,
                b.no_seal,
                b.nopol,
                a.perp_ke,
                a.max_perp
            FROM
                (
                SELECT
                    b.tgl_approve,
                    a.no_container,
                    a.size_,
                    a.type_,
                    CASE
                        WHEN tgL_realisasi IS NULL THEN 'FCL'
                        ELSE 'MTY'
                    END
                                    status,
                    tgl_request,
                    c.no_request,
                    d.emkl,
                    b.hz,
                    c.id_yard,
                    f.nama_lengkap,
                    c.no_request_receiving,
                    tgl_realisasi,
                    (
                    SELECT
                        MAX (perp_ke)
                    FROM
                        request_stripping,
                        container_stripping
                    WHERE
                        container_stripping.no_request =
                                            request_stripping.no_request
                        AND container_stripping.no_container =
                                                a.no_container)
                                    max_perp,
                    c.perp_ke
                FROM
                    master_container a
                JOIN container_stripping b
                                    ON
                    a.no_container = b.no_container
                JOIN history_container hc
                                    ON
                    b.no_container = hc.no_container
                    AND hc.no_request = b.no_request
                    AND hc.kegiatan IN
                                            ('REQUEST STRIPPING',
                                            'PERPANJANGAN STRIPPING')
                JOIN request_stripping c
                                    ON
                    b.no_request = c.no_request
                JOIN nota_stripping d
                                    ON
                    c.no_request = d.no_request
                LEFT JOIN master_user f
                                    ON
                    c.id_user = f.id
                WHERE
                    d.status <> 'BATAL'
                    AND (b.status_req IS NULL
                        OR b.status_req = 'PERP')
                    AND d.LUNAS = 'YES'
                ORDER BY
                    no_container) a,
                border_gate_in b,
                history_placement c,
                blocking_area ba,
                history_placement c1,
                blocking_area ba1
            WHERE
                a.no_container = b.no_container(+)
                AND a.no_request_receiving = b.no_request(+)
                AND a.no_container = c.no_container(+)
                AND a.no_request_receiving = c.no_request(+)
                AND c.id_blocking_area = ba.id(+)
                AND c.no_container = c1.no_container(+)
                AND c.no_request = c1.no_request(+)
                AND c1.id_blocking_area = ba1.id(+)
                AND c1.keterangan(+) = 'RELOKASI'
                AND a.perp_ke = a.max_perp";

        $V_STATUS_STUFFING_DEPO = "SELECT a.no_container,
                a.size_,
                a.type_,
                a.status,
                a.tgl_request,
                a.no_request,
                a.emkl,
                a.hz,
                a.nama_yard,
                a.nama_lengkap,
                a.commodity,
                a.asal_cont,
                a.type_stuffing,
                a.berat,
                a.tgl_realisasi,
                rd.tgl_request AS tgl_req_delivery,
		        RD.NO_REQUEST AS no_request_devery,
                rd.vessel,
                rd.voyage,
                bd.tgl_in tgl_gate,
                CASE
                WHEN     a.tgl_request IS NOT NULL
                        AND a.tgl_realisasi IS NULL
                        AND rd.tgl_request IS NULL
                        AND bd.tgl_in IS NULL
                THEN
                    'STF'
                WHEN     a.tgl_request IS NOT NULL
                        AND a.tgl_realisasi IS NOT NULL
                        AND rd.tgl_request IS NULL
                        AND bd.tgl_in IS NULL
                THEN
                    'REAL'
                WHEN     a.tgl_request IS NOT NULL
                        AND a.tgl_realisasi IS NOT NULL
                        AND rd.tgl_request IS NOT NULL
                        AND bd.tgl_in IS NULL
                THEN
                    'REPO'
                WHEN     a.tgl_request IS NOT NULL
                        AND a.tgl_realisasi IS NOT NULL
                        AND rd.tgl_request IS NOT NULL
                        AND bd.tgl_in IS NOT NULL
                THEN
                    'GATO'
                END
                status_cont
        FROM (SELECT a.no_container,
                        a.size_,
                        a.type_,
                        CASE
                        WHEN b.tgl_realisasi IS NULL THEN 'MTY'
                        ELSE 'FCL'
                        END
                        status,
                        tgl_request,
                        c.no_request,
                        d.emkl,
                        b.hz,
                        e.nama_yard,
                        f.nama_lengkap,
                        c.no_request_receiving,
                        b.tgl_realisasi,
                        b.commodity,
                        b.berat,
                        b.type_stuffing,
                        b.asal_cont,
                        c.no_booking
                FROM master_container a
                        JOIN container_stuffing b
                        ON a.no_container = b.no_container
                        JOIN request_stuffing c
                        ON b.no_request = c.no_request
                        JOIN nota_stuffing d
                        ON c.no_request = d.no_request
                        LEFT JOIN yard_area e
                        ON c.id_yard = e.id
                        LEFT JOIN master_user f
                        ON c.id_user = f.id
                WHERE     d.status <> 'BATAL'
                        AND (B.STATUS_REQ = 'PERP' OR B.STATUS_REQ IS NULL)
                        AND D.LUNAS = 'YES') a
                LEFT JOIN container_delivery cd
                ON cd.noreq_peralihan = a.no_request
                    AND cd.no_container = a.no_container
                LEFT JOIN request_delivery rd
                ON rd.no_request = cd.no_request
                LEFT JOIN border_gate_out bd
                ON cd.no_container = bd.no_container
                    AND bd.no_request = cd.no_request";


        $V_STATUS_REPO_MTY = "SELECT a.no_container,
                        a.size_,
                        a.type_,
                        a.status,
                        a.tgl_request,
                        a.no_request,
                        a.emkl,
                        a.hz,
                        a.nama_lengkap,
                        a.komoditi,
                        a.berat,
                        a.vessel,
                        a.voyage,
                        b.tgl_in as TGL_GATE
                FROM    (SELECT c.no_container,
                                a.size_,
                                a.type_,
                                c.status,
                                tgl_request,
                                d.no_request,
                                n.emkl,
                                c.hz,
                                f.nama_lengkap,
                                c.komoditi,
                                c.berat,
                                d.vessel,
                                d.voyage
                            FROM master_container a
                                INNER JOIN container_delivery c
                                    ON a.no_container = c.no_container
                                INNER JOIN request_delivery d
                                    ON d.no_request = c.no_request
                                INNER JOIN nota_delivery n
                                    ON d.no_request = n.no_request
                                LEFT JOIN master_user f
                                    ON d.id_user = f.id
                            WHERE     n.status <> 'BATAL'
                                AND n.lunas = 'YES'
                                AND d.jn_repo = 'EMPTY') a
                        LEFT JOIN
                        border_gate_out b
                        ON a.no_container = b.no_container AND a.no_request = b.no_request";

        $V_DELIVERY_SP2 = "SELECT
                a.no_container,
                a.size_,
                a.type_,
                a.status,
                a.tgl_request,
                a.no_request,
                a.emkl,
                a.hz,
                a.nama_lengkap,
                a.komoditi,
                a.berat,
                b.tgl_in,
                a.perp_dari,
                a.perp_ke,
                a.max_perp,
                a.tgl_delivery as tgl_gate
            FROM
                (
                SELECT
                    *
                FROM
                    (
                    SELECT
                        c.no_container,
                        a.size_,
                        a.type_,
                        c.status,
                        d.tgl_request,
                        d.no_request,
                        n.emkl,
                        c.hz,
                        f.nama_lengkap,
                        c.komoditi,
                        c.berat,
                        d.PERP_DARI,
                        CASE
                            WHEN d.perp_ke IS NULL THEN 0
                            ELSE d.perp_ke
                        END
                                            perp_ke,
                        CASE
                            WHEN (
                            SELECT
                                MAX (perp_ke)
                            FROM
                                request_delivery,
                                container_delivery
                            WHERE
                                request_delivery.no_request =
                                                            container_delivery.no_request
                                AND no_container =
                                                                a.no_container
                                AND request_delivery.no_request =
                                                                d.no_request)
                                                    IS NULL
                                            THEN
                                                0
                            ELSE
                                                (
                            SELECT
                                MAX (perp_ke)
                            FROM
                                request_delivery,
                                container_delivery
                            WHERE
                                request_delivery.no_request =
                                                            container_delivery.no_request
                                AND no_container = a.no_container
                                AND request_delivery.no_request =
                                                                d.no_request)
                        END
                                            max_perp,
                        c.tgl_delivery
                    FROM
                        master_container a
                    INNER JOIN container_delivery c
                                            ON
                        a.no_container = c.no_container
                    INNER JOIN request_delivery d
                                            ON
                        d.no_request = c.no_request
                    INNER JOIN nota_delivery n
                                            ON
                        d.no_request = n.no_request
                    LEFT JOIN master_user f
                                            ON
                        d.id_user = f.id
                    WHERE
                        n.status <> 'BATAL'
                        AND n.lunas = 'YES'
                        AND d.delivery_ke = 'LUAR'
                    ORDER BY
                        c.no_container) c
                WHERE
                    c.perp_ke = c.max_perp
                    AND c.no_request NOT IN
                                        (
                    SELECT
                        perp_dari
                    FROM
                        request_delivery
                    WHERE
                        delivery_ke = 'LUAR'
                        AND perp_dari IS NOT NULL)) a
            LEFT JOIN
                        gate_out b
                    ON
                a.no_container = b.no_container
                AND a.no_request = b.no_request";


        if ($kegiatan == 'receiving_luar') {
            if ($status == 'req') {
                $query_status = 'and tgl_request is not null';
            } else if ($status == 'gati') {
                $query_status = 'and tgl_in is not null';
            } else if ($status == 'plac') {
                $query_status = 'and tgl_placement is not null';
            }

            $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy') " . $query_status . " " . $orderby;
            $query_list = $this->QueryBuilder($receiving_luar, $filter, $limit);
            //echo $query_list;
        } else if ($kegiatan == 'stripping_tpk') {
            if ($status == 'tgl_app') {
                $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy')" . $query_status . " " . $orderby;
                $query_list = $this->QueryBuilder($tgl_app, $filter, $limit);
            } else {
                if ($status == 'req') {
                    $query_status = 'and tgl_request is not null';
                } else if ($status == 'gati') {
                    $query_status = 'and tgl_in is not null';
                } else if ($status == 'plac') {
                    $query_status = 'and tgl_placement is not null';
                } else if ($status == 'real') {
                    $query_status = 'and tgl_realisasi is not null';
                } else if ($status == 'plac_mty') {
                    $query_status = 'and tgl_relokasi is not null';
                }

                $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy')" . $query_status . " " . $orderby;
                $query_list = $this->QueryBuilder($V_STATUS_STRIPPING_TPK, $filter, $limit);
            }
        } else if ($kegiatan == 'stuffing_tpk') {
            if ($status == 'req') {
                $query_status = 'and tgl_request is not null';
            } else if ($status == 'gati') {
                $query_status = 'and tgl_gate is not null';
            } else if ($status == 'plac') {
                $query_status = 'and tgl_placement is not null';
            } else if ($status == 'tgl_app') {
                $query_status = 'and tgl_approve is not null';
            } else if ($status == 'real') {
                $query_status = 'and tgl_realisasi is not null';
            } else if ($status == 'gato') {
                $query_status = 'and tgl_placement is not null';
            }

            $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy')" . $query_status;

            $query_list = $this->QueryBuilder($V_STATUS_STUFFING_DEPO, $filter, $limit);
        } else if ($kegiatan == 'stuffing_depo') {
            if ($status == 'req') {
                $query_status = 'and tgl_request is not null';
            } else if ($status == 'real') {
                $query_status = 'and tgl_realisasi is not null';
            } else if ($status == 'req_del') {
                $query_status = 'and tgl_req_delivery is not null';
            } else if ($status == 'gato') {
                $query_status = 'and tgl_gate is not null';
            }

            $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy')" . $query_status . " " . $orderby;
            $query_list = $this->QueryBuilder($V_STATUS_STUFFING_DEPO, $filter, $limit);
        } else if ($kegiatan == 'delivery_tpk_mty') {
            if ($status == 'req') {
                $query_status = 'and tgl_request is not null';
            } else if ($status == 'gato') {
                $query_status = 'and b.tgl_in is not null';
            }

            $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy') " . $query_status . " " . $orderby;
            $query_list = $this->QueryBuilder($V_STATUS_REPO_MTY, $filter, $limit, true);
        } else if ($kegiatan == 'delivery_luar') {
            if ($status == 'req') {
                $query_status = 'and tgl_request is not null';
            } else if ($status == 'gato') {
                $query_status = 'and tgl_gate is not null';
            }

            $filter = "TGL_REQUEST BETWEEN TO_DATE('$tgl_awal','dd/mm/yyyy') AND TO_DATE('$tgl_akhir','dd/mm/yyyy') " . $query_status . " " . $orderby;

            $query_list = $this->QueryBuilder($V_DELIVERY_SP2, $filter, $limit);
        } else if ($kegiatan == 'relokasi') {
        }

        return DB::connection('uster')->select($query_list);
    }
}
