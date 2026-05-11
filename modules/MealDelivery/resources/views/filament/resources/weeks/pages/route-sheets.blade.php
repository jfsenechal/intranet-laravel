<x-filament-panels::page>
    @php
        $formattedDate = \Illuminate\Support\Str::title($sheets['date']->translatedFormat('l j F Y'));
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

            .route-sheet { color: #000; }

            .route-sheet + .route-sheet { page-break-before: always; }
        }

        .route-sheet { margin-bottom: 2.5rem; }

        .route-sheet table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .route-sheet th,
        .route-sheet td {
            border: 1px solid #00AA88;
            padding: 6px 10px;
            font-size: 12px;
            vertical-align: top;
        }

        .route-sheet th { background: #f5f5f5; text-align: center; }

        .route-sheet tr.alt td { background: #fafafa; }

        .route-sheet .text-muted { color: #6b7280; }
        .route-sheet .text-success { color: #166534; }

        .route-sheet .totals-table th,
        .route-sheet .totals-table td { border: none; padding: 4px 8px; }

        .route-sheet .button-row {
            display: flex;
            gap: .5rem;
            margin-bottom: .75rem;
        }
    </style>

    @foreach ($sheets['routes'] as $sheet)
        <div class="route-sheet" id="route-{{ $sheet['id'] }}">
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
                    wire:click="downloadRoutePdf({{ $sheet['id'] }})"
                    class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
                >
                    PDF
                </button>
            </div>

            <h3 class="text-success" style="font-size:18px; font-weight:600;">{{ $sheet['name'] }} : {{ $formattedDate }}</h3>

            @include('meal-delivery::filament.resources.weeks.pages._route-sheet-table', ['sheet' => $sheet])
        </div>
    @endforeach

    <div class="route-sheet" id="cafeteria">
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
                wire:click="downloadCafeteriaPdf"
                class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
            >
                PDF
            </button>
        </div>

        <h3 class="text-success" style="font-size:18px; font-weight:600;">Cafétéria : {{ $formattedDate }}</h3>

        @include('meal-delivery::filament.resources.weeks.pages._route-sheet-table', ['sheet' => $sheets['cafeteria']])
    </div>
</x-filament-panels::page>
