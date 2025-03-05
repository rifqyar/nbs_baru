<style>
    body {
        margin: 0px;
    }

    .style1 {
        font-size: 18px;
        font-weight: bold;
    }

    .style2 {
        font-size: 16px;
        font-weight: bold;
    }
</style>
@foreach ($row_list as $k => $rw)
    <div style= "font-family:arial; width:767px; height:990px; padding-left:8px; border:0px solid  #fff">
        <table border="0px" width="100%" style="margin:0px; font-size:12px" cellpadding="0" cellspacing="0">
            <tr>
                <td height="29" colspan="7"></td>
            </tr>
            <tr>
                <td height="8" width="15%">&nbsp;</td>
                <td height="8" width="39%">&nbsp;</td>
                <td height="8" width="14%" colspan="3"></td>
                <td height="8" width="17%" align="right"></td>
                <td height="8" align="left" width="17%">&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td>&nbsp;</td>
                <td align="left">&nbsp;{{ $rw->no_nota }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td>&nbsp;</td>
                <td align="left">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Halaman {{ $k + 1 }}</td>
            </tr>
            <tr>
                <td height="25">&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td valign="bottom" align="right">&nbsp;&nbsp; NO REQ :</td>
                <td valign="bottom">&nbsp;{{ $rw->no_request }}</td>
            </tr>
            <tr>
                <td height="70" colspan="7"> {{ $rw->status_req == 'PERP' ? '#SP2 PERPANJANGAN' : '' }}</td>
            </tr>
            <tr>
                <td height="52">&nbsp;</td>
                <td><b style="font-size:24px">{{ $rw->no_container }}</b></td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2"><span class="style1">[D E P O - S Y S T E M]</span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td valign="top">{{ $rw->size_ }} / {{ $rw->type_ }} / {{ $rw->status }} - {{ $rw->berat }}
                    Kg
                </td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2">&nbsp;</td>
            </tr>
            <tr>
                <td height="15">&nbsp;</td>
                <td></td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td height="15">{{ $rw->nama_emkl }}</td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2">{{ strtoupper(\Carbon\Carbon::parse($rw->tgl_request)->translatedFormat('d-M-y')) }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="4"><span class="style2">{{ $rw->name }} - {{ $rw->slot_ }} -
                        {{ $rw->row_ }} -
                        {{ $rw->tier_ }}</span></td>
                <td colspan="2">{{ $rw->no_ro }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td height="1" colspan="4">{{ $rw->nama_pnmt }}</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td height="1">&nbsp;</td>
                <td height="1">KET:&nbsp;{{ $rw->keterangan }}</td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2">
                    @if ($rw->status_req == 'PERP')
                        {{ strtoupper(\Carbon\Carbon::parse($rw->start_perp)->translatedFormat('d-M-y')) }}
                    @else
                        {{ strtoupper(\Carbon\Carbon::parse($rw->tgl_start)->translatedFormat('d-M-y')) }}
                    @endif
                    s/d {{ strtoupper(\Carbon\Carbon::parse($rw->tgl_end)->translatedFormat('d-M-y')) }}
                </td>
            </tr>
            <tr>
                <td height="1">&nbsp;</td>
                <td height="1" colspan="6">VIA:&nbsp;<b>{{ strtoupper($rw->via) }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td height="15" colspan="6"></td>
            </tr>
            <tr>
                <td height="15">&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2" style="padding-left:45px"></td>
            </tr>
            <tr>
                <td height="44">User : {{ $name }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3" align="left"> &nbsp; &nbsp;&nbsp; <span
                        style="padding-left:10px">{{ date('d M Y') }}</span></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td height="16">&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="2">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td height="45">&nbsp;</td>
                <td>&nbsp;</td>
                <td colspan="2">&nbsp;</td>
                <td colspan="3"> &nbsp;<span style="padding-left:10px"><strong>SUWANDA</strong></span></td>
            </tr>
        </table>
    </div>
    <div style="margin-top:35px;width:767px;"></div>
@endforeach
