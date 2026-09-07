<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
    @endphp

    <style>
        @media print {
            .fi-topbar,
            .fi-sidebar,
            .fi-page-header,
            .fi-breadcrumbs,
            .fi-header-actions,
            .d-print-none {
                display: none !important;
            }

            .guests-export { color: #000; }
        }

        .guests-export table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .guests-export th,
        .guests-export td {
            border: 1px solid #d4d4d8;
            padding: 0.5rem 0.75rem;
            text-align: left;
            vertical-align: top;
        }

        .guests-export tfoot th,
        .guests-export tfoot td {
            font-weight: 700;
        }

        .guests-export h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0.5rem 0;
            color: #166534;
        }
    </style>

    <div class="guests-export">
        <div class="d-print-none" style="margin-bottom: 1rem;">
            <button
                type="button"
                onclick="window.print()"
                class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
            >
                Imprimer
            </button>
        </div>

        <h3>REPAS INVITÉS : <strong>{{ $this->formattedDate() }}</strong></h3>

        @if (count($summary['rows']) === 0)
            <p><em>Aucun repas invité pour ce jour.</em></p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Résident</th>
                        <th>Menu 1</th>
                        <th>Menu 2</th>
                        <th>Total</th>
                        <th>Remarques</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($summary['rows'] as $row)
                        <tr>
                            <td>{{ $row['resident_name'] }}</td>
                            <td>{{ $row['menu1'] > 0 ? $row['menu1'] : '' }}</td>
                            <td>{{ $row['menu2'] > 0 ? $row['menu2'] : '' }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>@if ($row['notes']){!! nl2br(e($row['notes'])) !!}@endif</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Totaux</th>
                        <td>{{ $summary['totals']['menu1'] }}</td>
                        <td>{{ $summary['totals']['menu2'] }}</td>
                        <td>{{ $summary['totals']['guests'] }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</x-filament-panels::page>
