<style>
    body {
        margin: 0px;
        padding-top: 10px;
        width: 100%;
        height: 100%;
        font-family: Arial
    }

    .style1 {
        font-size: 18px
    }
</style>

@foreach($row_list as $row)
<div style="width:767px; height:487px; border:1px solid  #FFF">
    <table width="135%" height="564" cellpadding="0" cellspacing="0"
        style="margin:0px; margin-top:20px; margin-bottom:20px; font-size:12px">
        <tr>
            <td width="15%" height="118">&nbsp;</td>
            <td width="39%">&nbsp;</td>
            <td width="13%">&nbsp;</td>
            <td width="19%" align="right" valign="top">No SPPS&nbsp;&nbsp;&nbsp;&nbsp;: </td>
            <td width="14%" valign="top">
                <p>{{ strtoupper ($row->no_request) }}</p>
                <p2>
                    <p></p>
                    <p></p>
                </p2>
            </td>
        </tr>

        <tr height="10">
            <td height="20"></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>
                @if (strtoupper ($row->status_req) == 'PERP')
                    #PERPANJANGAN#
                @endif
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="25" colspan="5" align="right"><span style="padding-right:30px; font-size:24px"></span>
            </td>
        <tr>
            <td width="15%" height="35">&nbsp;</td>
            <td><b style="font-size:24px">{{ $row->no_container }}</b> <br /> <b style="font-size: 18px">
                    {{ $row->pin_number }}</b></td>
            <td>&nbsp;</td>
            <td>
                <span class="style1">
                    <strong>USTER IPC</strong>
                </span>
            </td>
        </tr>
        <tr>
            <td height="18">&nbsp;</td>

            <td>{{ $row->size ?? ''}} /FCL</td>
            <td>&nbsp;</td>
            <td colspan="2">&nbsp;
                <img alt="testing"
                    src="{{ route('uster.helper.getBarcode', ['value' => $row->pin_number ?? 'null']) }}" />
            </td>

        </tr>
        <tr>
            <td height="15">&nbsp;</td>
            <td>{{ $row->o_vessel }} / {{ $row->o_voyin }}</td>
            <td>&nbsp;</td>
            <td valign="bottom"> {{ $row->tgl_jam_tiba ?? ''}}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="18">&nbsp;</td>
            <td>{{ $row->emkl }}</td>
            <td>&nbsp;</td>
            <td valign="bottom"></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="20">&nbsp;</td>
            <td><strong style="font:14px"><span class="style1">{{ $row->lokasi_tpk }}</strong></td>
            <td>&nbsp;</td>
            <td valign="bottom">{{ $row->no_do }}</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="19">&nbsp;</td>
            <td><strong>AFTER STRIPPING : {{ $row->after_strip }}</strong></td>
            <td>&nbsp;</td>
            <td colspan="2">
                @if(strtoupper ($row->status_req) == 'PERP')
                    <?php
                    $no_co = $row->no_container;
                    $no_re = $row->no_request;
                    echo $row->start_pnkn_;
                    ?>
                @else
                    {{ $row->start_pnkn }}
                @endif
            </td>
        </tr>
        <tr>
            <td height="17">&nbsp;</td>
            <td valign="top">........ / ......... - ......... - ......... </td>
            <td align="left">&nbsp;</td>
            <td>
                @if(strtoupper ($row->status_req) == 'PERP')
                    {{ $row->end_pnkn_ }}
                @else
                    {{ $row->end_pnkn }}
                @endif
            </td>
        </tr>
        <tr>
            <td height="17">&nbsp;</td>
            <td>{{ $row->consignee_personal }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="15">&nbsp;</td>
            <td>&nbsp;ASAL CONTAINER : {{ $row->asal_cont }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;@if (strtoupper ($row->status_req) != 'PERP')
                {{ $row->tgl_awal . ' s/d ' . $row->tgl_akhir }}
            @endif</td>
        </tr>
        <tr>
            <td height="15">&nbsp;</td>
            <td style="text-transform:uppercase">user print SPPS : {{ $name }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="19"></td>
            <td><span style="text-transform:uppercase"></span></td>
            <td>&nbsp;</td>
            <td colspan="2" style="padding-left:15px">&nbsp;<?= date('d M Y') ?></td>
        </tr>
        <tr>
            <td height="40">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td height="102">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td colspan="2" style="padding-left:15px">&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <!-- END DATA -->
    </td>
    </tr>
    </table>
</div>

@if($loop->iteration % 2 == 0)
    <div style=" height:20px; border:1px solid #FFF"></div>
@else
    <div style=" margin-top:18px;"></div>
@endif

@endforeach
