@if (! empty($guests['rows']))
    <h3 class="text-success" style="font-size:16px; font-weight:600;">
        Repas invités — {{ $guests['totals']['guests'] }} {{ \Illuminate\Support\Str::plural('repas', $guests['totals']['guests']) }}
    </h3>

    <table>
        <thead>
            <tr>
                <th style="width:30%;">Client</th>
                <th style="width:25%;">Adresse</th>
                <th style="width:10%;">Menu<br>1</th>
                <th style="width:10%;">Menu<br>2</th>
                <th style="width:10%;">Total</th>
                <th style="width:15%;">Remarques</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($guests['rows'] as $index => $row)
                <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                    <td style="text-align:left;">{{ $row['client_name'] }}</td>
                    <td style="text-align:left;">{{ $row['address_line'] }}</td>
                    <td style="text-align:center;">{{ $row['menu1'] > 0 ? $row['menu1'] : '' }}</td>
                    <td style="text-align:center;">{{ $row['menu2'] > 0 ? $row['menu2'] : '' }}</td>
                    <td style="text-align:center;">{{ $row['total'] }}</td>
                    <td style="text-align:left;">
                        @if ($row['notes'])
                            {!! nl2br(e($row['notes'])) !!}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:left;"><strong>Totaux</strong></td>
                <td></td>
                <td style="text-align:center;"><strong>{{ $guests['totals']['menu1'] }}</strong></td>
                <td style="text-align:center;"><strong>{{ $guests['totals']['menu2'] }}</strong></td>
                <td style="text-align:center;"><strong>{{ $guests['totals']['guests'] }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endif
