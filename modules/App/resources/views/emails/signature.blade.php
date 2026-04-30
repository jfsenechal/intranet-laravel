<table cellpadding="0" cellspacing="0" border="0" style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; line-height: 1.4;">
    <tr>
        <td style="padding-right: 15px; vertical-align: top;">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $logoTitle }}" style="max-width: 120px; height: auto; display: block;"/>
            @endif
        </td>
        <td style="vertical-align: top; border-left: 2px solid #d4a017; padding-left: 15px;">
            <div style="font-size: 14px; font-weight: bold; color: #000;">
                {{ $signature->first_name }} {{ $signature->last_name }}
            </div>
            @if ($signature->job_title)
                <div style="font-style: italic;">{{ $signature->job_title }}</div>
            @endif
            @if ($signature->service)
                <div>{{ $signature->service }}</div>
            @endif
            @if ($logoTitle)
                <div style="font-weight: bold; margin-top: 4px;">{{ $logoTitle }}</div>
            @endif
            <div style="margin-top: 6px;">
                {{ $signature->address }} &mdash; {{ $signature->postal_code }} {{ $signature->city }}
            </div>
            <div style="margin-top: 4px;">
                @if ($signature->phone)
                    <span>Tél. : <a href="tel:{{ $signature->phone }}" style="color: #333; text-decoration: none;">{{ $signature->phone }}</a></span>
                @endif
                @if ($signature->mobile)
                    <span style="margin-left: 8px;">GSM : <a href="tel:{{ $signature->mobile }}" style="color: #333; text-decoration: none;">{{ $signature->mobile }}</a></span>
                @endif
            </div>
            <div>
                <a href="mailto:{{ $signature->email }}" style="color: #d4a017; text-decoration: none;">{{ $signature->email }}</a>
                @if ($signature->website)
                    &nbsp;&bull;&nbsp;
                    <a href="{{ $signature->website }}" style="color: #d4a017; text-decoration: none;">{{ $signature->website }}</a>
                @endif
            </div>
        </td>
    </tr>
</table>
