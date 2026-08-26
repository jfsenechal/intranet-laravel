<x-filament-panels::page>
    @php
        $totals = $this->getTotals();
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

            .week-summary { color: #000; }
        }

        .week-summary table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .week-summary th,
        .week-summary td {
            border: 1px solid #00AA88;
            padding: 6px 10px;
            font-size: 12px;
        }

        .week-summary th { background: #f5f5f5; text-align: center; }

        .week-summary td.number { text-align: right; }

        .week-summary tfoot td { font-weight: 600; background: #f5f5f5; }

        .week-summary .button-row {
            display: flex;
            gap: .5rem;
            margin-bottom: .75rem;
        }
    </style>

    <div class="week-summary">
        <div class="button-row d-print-none">
            <button
                type="button"
                onclick="window.print()"
                class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
            >
                Imprimer
            </button>
            <button
                type="button"
                wire:click="downloadPdf"
                class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
            >
                PDF
            </button>
        </div>

        <h3 class="text-success" style="font-size:18px; font-weight:600;">Semaine du {{ $this->record->formattedFirstDay() }}</h3>

        <table>
            <thead>
            <tr>
                <th>Date</th>
                <th>Clients</th>
                <th>Potages</th>
                <th>Menus 1</th>
                <th>Menus 2</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($this->days as $day)
                <tr>
                    <td>{{ $day['label'] }}</td>
                    <td class="number">{{ $day['clients_count'] }}</td>
                    <td class="number">{{ $day['soup_count'] }}</td>
                    <td class="number">{{ $day['menu1_count'] }}</td>
                    <td class="number">{{ $day['menu2_count'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Aucun jour de repas.</td>
                </tr>
            @endforelse
            </tbody>
            @if ($this->days !== [])
                <tfoot>
                <tr>
                    <td>Total</td>
                    <td class="number">{{ $totals['clients_count'] }}</td>
                    <td class="number">{{ $totals['soup_count'] }}</td>
                    <td class="number">{{ $totals['menu1_count'] }}</td>
                    <td class="number">{{ $totals['menu2_count'] }}</td>
                </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-filament-panels::page>
