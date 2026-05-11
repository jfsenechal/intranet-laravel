<x-filament-panels::page>
    @php
        $formattedDate = \Illuminate\Support\Str::title($summary['date']->translatedFormat('l j F Y'));
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

            .kitchen-export {
                color: #000;
            }
        }

        .kitchen-export table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .kitchen-export .menus-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem;
        }

        .kitchen-export .menu-card {
            border: 1px solid #d4d4d8;
            padding: 0.5rem;
        }

        .kitchen-export .menu-card th,
        .kitchen-export .menu-card td {
            border: 1px solid #d4d4d8;
            padding: 0.5rem 0.75rem;
        }

        .kitchen-export h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .kitchen-export h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0.25rem 0;
        }

        .kitchen-export .text-success {
            color: #166534;
        }
    </style>

    <div class="kitchen-export">
        <div class="d-print-none" style="margin-bottom: 1rem;">
            <button
                type="button"
                onclick="window.print()"
                class="fi-btn fi-color-primary fi-btn-color-primary fi-size-md"
            >
                Imprimer
            </button>
        </div>

        <h3 class="text-success">REPAS A DOMICILE : <strong>{{ $formattedDate }}</strong></h3>

        <h4><strong>Potages :</strong> {{ $summary['soup_total'] }}</h4>
        <h4><strong>Menus :</strong> {{ $summary['menus_total'] }}</h4>

        <div class="menus-grid">
            @foreach ($summary['menus'] as $menu)
                <table class="menu-card">
                    <tbody>
                        <tr>
                            <th>Menu {{ $menu['position'] }}</th>
                            <th>Nombre</th>
                        </tr>
                        <tr>
                            <th></th>
                            <td>{{ $menu['total'] }}</td>
                        </tr>
                        <tr>
                            <th colspan="2">Détails par régimes :</th>
                        </tr>
                        @forelse ($menu['diets'] as $diet)
                            <tr>
                                <td>{{ $diet['label'] }}</td>
                                <th>{{ $diet['total'] }}</th>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2"><em>Aucune commande.</em></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
