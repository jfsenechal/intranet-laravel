<table>
    <thead>
        <tr>
            <th style="width:20%;"></th>
            <th style="width:25%;"></th>
            <th style="width:5%;">Pot</th>
            <th style="width:10%;">Menu<br>1</th>
            <th style="width:10%;">Menu<br>2</th>
            <th style="width:20%;">Remarques</th>
            <th style="width:5%;">RF</th>
            <th style="width:5%;">DF</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($sheet['rows'] as $index => $row)
            <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                <td style="text-align:left;">
                    <span>{{ $row['client_name'] }}</span>
                    @if ($row['phone'])
                        <br><span class="text-muted">{{ $row['phone'] }}</span>
                    @endif
                </td>
                <td style="text-align:left;">
                    {{ $row['address_line'] }}<br>
                    {{ $row['city_line'] }}
                </td>
                <td>{{ $row['soup'] > 0 ? $row['soup'] : '' }}</td>
                <td>
                    {{ $row['menu1'] > 0 ? $row['menu1'] : '' }}
                    @if (! empty($row['menu1_diets']))
                        <br><span class="text-muted">{{ implode(', ', $row['menu1_diets']) }}</span>
                    @endif
                </td>
                <td>
                    {{ $row['menu2'] > 0 ? $row['menu2'] : '' }}
                    @if (! empty($row['menu2_diets']))
                        <br><span class="text-muted">{{ implode(', ', $row['menu2_diets']) }}</span>
                    @endif
                </td>
                <td style="text-align:left;">
                    @if ($row['notes'])
                        <span>{!! nl2br(e($row['notes'])) !!}</span>
                    @endif
                </td>
                <td></td>
                <td>DF</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" style="text-align:center;"><em>Aucun client.</em></td>
            </tr>
        @endforelse
    </tbody>
</table>

<h3 style="font-size:16px; font-weight:600;">Totaux</h3>
<table class="totals-table">
    <tbody>
        <tr>
            <td style="width:20%;"><strong>Total des menus</strong></td>
            <td style="width:25%;"><strong>{{ $sheet['totals']['menus_total'] }}</strong></td>
            <td style="width:5%; text-align:center;">{{ $sheet['totals']['soup'] }}<br>potages</td>
            <td style="width:10%; text-align:center;">{{ $sheet['totals']['menu1'] }}<br>menus 1</td>
            <td style="width:10%; text-align:center;">{{ $sheet['totals']['menu2'] }}<br>menus 2</td>
            <td style="width:20%;"></td>
            <td colspan="2" style="width:10%; text-align:center;" class="text-muted">
                {{ $sheet['totals']['clients'] }} clients
            </td>
        </tr>
    </tbody>
</table>
