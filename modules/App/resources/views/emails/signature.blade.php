<table cellpadding="0" cellspacing="0" border="0" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4;">
    <tr>
        <td align="center" valign="middle" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; padding-right: 15px; vertical-align: middle; text-align: center;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $logoTitle }}" style="max-width: 120px; height: auto; display: block; margin: 0 auto;"/>
            @endif
            <div style="margin-top: 6px;">
                <a href="https://www.marche.be/disclaimer/" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; color: #d4a017; text-decoration: none;">Disclaimer</a>
            </div>
        </td>
        <td style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; vertical-align: top; border-left: 2px solid #d4a017; padding-left: 15px;">
            <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; font-size: 14px; font-weight: bold; color: #000;">
                {{ $signature->first_name }} {{ $signature->last_name }}
            </div>
            @if ($signature->job_title)
                <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; font-style: italic;">{{ $signature->job_title }}</div>
            @endif
            @if ($signature->service)
                <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif;">{{ $signature->service }}</div>
            @endif
            @if ($logoTitle)
                <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; font-weight: bold; margin-top: 4px;">{{ $logoTitle }}</div>
            @endif
            <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; margin-top: 6px;">
                {{ $signature->address }} &mdash; {{ $signature->postal_code }} {{ $signature->city }}
            </div>
            <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; margin-top: 4px;">
                @if ($signature->phone)
                    <span>Tél. : <a href="tel:{{ $signature->phone }}" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; color: #333; text-decoration: none;">{{ $signature->phone }}</a></span>
                @endif
                @if ($signature->mobile)
                    <span style="margin-left: 8px;">GSM : <a href="tel:{{ $signature->mobile }}" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; color: #333; text-decoration: none;">{{ $signature->mobile }}</a></span>
                @endif
            </div>
            <div style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif;">
                <a href="mailto:{{ $signature->email }}" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; color: #d4a017; text-decoration: none;">{{ $signature->email }}</a>
                @if ($signature->website)
                    &nbsp;&bull;&nbsp;
                    <a href="{{ $signature->website }}" style="font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif; color: #d4a017; text-decoration: none;">{{ $signature->website }}</a>
                @endif
            </div>
        </td>
    </tr>
</table>
