<style>
    body {
        margin: 0px;
        padding-top: 0px;
        width: 100%;
        height: 100%;
        font-family: Arial
    }

    .style1 {

        font-size: 18px;

        font-weight: bold;

    }
</style>


@foreach ($row_list as $index => $row)
    <div style="width:767px; height:487px; border:1px solid  #FFF">

        <table border="0px" width="100%" height="519" cellpadding="0" cellspacing="0"
            style="margin:0px; margin-top:0px; margin-bottom:15px; font-size:12px">

            <tr>

                <td width="15%" height="70">&nbsp;</td>

                <td width="41%">&nbsp;</td>


                <td width="12%">&nbsp;</td>
                <td width="22%" align="right" valign="top">No SPPS : {{ $row->no_request }}</td>

                <td width="14%" valign="top">
                    <p></p>

                    <p>

                        <p2> &nbsp</p2>

                    </p>

                    <p> &nbsp </p>
                </td>
            </tr>


            <tr height="10">

                <td height="58">&nbsp;</td>

                <td>&nbsp;</td>

                <td>&nbsp;</td>

                <td>
                    @if ($row->status_req == 'PERP')
                        #PERPANJANGAN#
                    @endif
                </td>

                <td>&nbsp;</td>

            </tr>
            <tr>

                <td height="41" colspan="5" align="right"><span
                        style="padding-right:30px; font-size:24px"></span>
                </td>



            <tr>

                <td width="15%" height="30">&nbsp;</td>

                <td valign="top"><b style="font-size:24px">{{ $row->no_container }}</b> <br /> <b
                        style="font-size: 18px">
                        </b></td>

                <td>&nbsp;</td>

                <td valign="top"><span class="style1">USTER IPC</span></td>

                <td>&nbsp;</td>

            </tr>

            <tr>

                <td height="24">&nbsp;</td>

                <td>{{ $row->size_ }} / MTY</td>


                <td colspan="2" align="right">

                    <img alt="testing"
                        src="{{ route('uster.helper.getBarcode', ['value' => $row->no_container ?? 'null']) }}" />
                </td>

                <td>&nbsp;</td>

            </tr>

            <tr>

                <td height="20">&nbsp;</td>

                <td>{{ $row->nm_kapal ?? '' }} / {{ $row->voyage ?? '' }}</td>

                <td>&nbsp;</td>

                <td></td>

                <td>&nbsp;</td>

            </tr>

            <tr>

                <td height="20">&nbsp;</td>

                <td>{{ $row->emkl ?? ''}}</td>

                <td>&nbsp;</td>

                <td> &nbsp;
                <?php
                    
                    if ($row->status_req == 'PERP') {
                        $no_co = $row->no_container;
                        $no_re = $row->no_request;
                    
                        $tgl_awal = "SELECT a.TGL_BONGKAR FROM container_stripping a, request_stripping b where a.no_request = b.no_request
                                                                                                                                                                                                                                                                            and a.no_container = '$no_co' AND a.no_request = '$no_re'";
                    
                        $perp = Illuminate\Support\Facades\DB::connection('uster')->select($tgl_awal);
                    
                        echo $row->tgl_awal ?? '';
                    } else {
                        echo $row->tgl_akhir ?? '';
                    }
                    
                    ?>
                </td>

                <td>&nbsp;</td>

            </tr>

            <tr>


                <td height="20">&nbsp;</td>

                <td>
                    <strong style="font:14px"><span class="style1">
                            {{ $row->lokasi_tpk ?? ''}}
                        </span> {{$row->row_tpk}}--{{$row->slot_tpk}}--{{$row->tier_tpk}}</strong>
                </td>

                <td>&nbsp;</td>

                <td>
                    <?php
                    if ($row->status_req == 'PERP') {
                        $tgl_akhir = "SELECT PERP_SD TGL_AKHIR FROM REQUEST_STRIPPING a WHERE a.NO_REQUEST = '$no_re'";
                    
                        $perp = Illuminate\Support\Facades\DB::connection('uster')->select($tgl_akhir);
                        echo $row->end_stack_pnkn ?? '';
                    } else {
                        echo $row->tgl_akhir ?? '';
                    }
                    ?>
                </td>

                <td>&nbsp;</td>



            </tr>

            <tr>

                <td height="18">&nbsp;</td>

                <td>{{ $row->emkl  ?? ''}}</td>

                <td>&nbsp;</td>

                <td colspan="2">

                </td>



            </tr>

            <tr>

                <td height="1">&nbsp;</td>
        
                <td>........ / ......... - ......... - ......... </td>
        
                <td>&nbsp;</td>
        
                <td></td>
        
              </tr>
        

            <tr>

                <td height="1">&nbsp;</td>

                <td><strong>STUFFING {{$row->type_stuffing ?? ''}}</strong></td>

                <td>&nbsp;</td>

                <td>
                   
                </td>

            </tr>

            <tr>

                <td height="1">&nbsp;</td>

                <td><strong>STUFFING DARI : {{ $row->asal_cont  ?? ''}}</strong></td>

                <td>&nbsp;</td>

                <td>&nbsp;</td>

            </tr>

            <tr>

                <td height="1">&nbsp;</td>

                <td>ASAL CONTAINER : {{ $row->asal_cont ?? '' }}</td>

                <td>&nbsp;</td>

                <td>&nbsp;</td>

            </tr>


            <tr>

                <td height="1"></td>

                <td>user print SPPS : {{ $name }}</td>

                <td>&nbsp;</td>

                <td colspan="2" style="padding-left:15px">&nbsp;</td>

            </tr>

            <tr>

                <td height="29">&nbsp;</td>

                <td>&nbsp;</td>

                <td>&nbsp;</td>

                <td><span style="padding-left:15px">&nbsp;<?= date('d M Y') ?></span></td>

                <td>&nbsp;</td>

            </tr>

            <tr>

                <td height="59">&nbsp;</td>

                <td>&nbsp;</td>

                <td>&nbsp;</td>

                <td colspan="2" style="padding-left:15px">&nbsp;</td>

            </tr>

        </table>

        </td>

        </tr>

        </table>

    </div>

    <?php $ck = ($index+1)%2; if($ck == 0){?>

    <div style=" margin-top:-12px;"></div>
    <?php } else {
            
         ?>
    <div style=" margin-top:10px;"></div>
    <?php  }?>
@endforeach
