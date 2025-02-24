<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {


        return view('home');
    }

    // In your Controller, e.g., HomeController.php

    public function getDashboardData()
    {
        // Fetch kegiatan summary
        $kegiatanSummary = DB::connection('uster')->select("
          SELECT DISTINCT hc.KEGIATAN, COUNT(hc.KEGIATAN) AS total 
          FROM HISTORY_CONTAINER hc
          LEFT JOIN USTER.MASTER_USER mu ON hc.ID_USER = TO_CHAR(mu.ID)
          WHERE TRUNC(hc.TGL_UPDATE) = TRUNC(SYSDATE) 
          GROUP BY hc.KEGIATAN
          ORDER BY total DESC");

        // Calculate total requests (assuming it's the sum of all kegiatan totals)
        $totalRequest = array_sum(array_column($kegiatanSummary, 'total'));

        $NotaSummary = DB::connection('uster')->selectOne("
          SELECT
              COUNT(*) AS total_today,
              COUNT(CASE 
                      WHEN LUNAS = 'YES' 
                          AND PAYMENT_CODE IS NOT NULL 
                          AND TRUNC(TANGGAL_LUNAS) = TRUNC(SYSDATE)
                      THEN 1 
                  END) AS total_lunas,
              COUNT(CASE 
                      WHEN LUNAS <> 'YES' 
                      THEN 1 
                  END) AS total_belum_lunas
          FROM (
              -- RECEIVING
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  'RECEIVING' AS KEGIATAN
              FROM
                  nota_receiving
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  CASE
                      WHEN STATUS = 'PERP' THEN 'PERP_PNK'
                      ELSE 'STUFFING'
                  END AS KEGIATAN
              FROM
                  nota_stuffing
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  CASE
                      WHEN SUBSTR(NO_NOTA, 1, 2) = '03' THEN 'STRIPPING'
                      ELSE 'PERP_STRIP'
                  END AS KEGIATAN
              FROM
                  nota_stripping
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  CASE
                      WHEN STATUS = 'PERP' THEN 'PERP_DEV'
                      ELSE 'DELIVERY'
                  END AS KEGIATAN
              FROM
                  nota_delivery
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  'RELOKASI' AS KEGIATAN
              FROM
                  nota_relokasi
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  -- Asumsi: nota_batal_muat tidak memiliki TANGGAL_LUNAS, jadi gunakan NULL
                  NULL AS TANGGAL_LUNAS,
                  LUNAS,
                  'BATAL_MUAT' AS KEGIATAN
              FROM
                  nota_batal_muat
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  'RELOK_MTY' AS KEGIATAN
              FROM
                  nota_relokasi_mty
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  'DEL_PNK' AS KEGIATAN
              FROM
                  nota_pnkn_del
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE)
              UNION ALL
              SELECT
                  NO_NOTA,
                  TGL_NOTA,
                  PAYMENT_CODE,
                  TANGGAL_LUNAS,
                  LUNAS,
                  'STUF_PNK' AS KEGIATAN
              FROM
                  nota_pnkn_stuf
              WHERE
                  STATUS <> 'BATAL'
                  AND NO_NOTA IS NOT NULL
                  AND TRUNC(TGL_NOTA) = TRUNC(SYSDATE))");


        return response()->json([
            'kegiatanSummary' => $kegiatanSummary,
            'totalRequest' => $totalRequest,
            'NotaSummary' => $NotaSummary,
        ]);
    }
}
