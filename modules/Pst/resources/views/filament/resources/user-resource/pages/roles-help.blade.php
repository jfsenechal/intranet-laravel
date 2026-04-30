@php use App\Enums\RoleEnum; @endphp

<div class="flex flex-col gap-4 p-6 bg-white rounded-2xl shadow-md border border-gray-200 w-full max-w-2xl">
    <!-- En-tête -->
    <div class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-3">
        Qui peut faire quoi sur les actions
    </div>

    <!-- Section Création -->
    <div class="space-y-2">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
            Création d'actions
        </h3>
        <div class="flex items-start flex-col gap-2 text-gray-600">
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>N'importe quel agent peut ajouter une action sans restriction.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>Cette action devra être validée.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>Les agents avec un rôle mandataire ne peuvent pas ajouter.</span>
            </div>
        </div>
    </div>

    <!-- Section Modification - Admin uniquement -->
    <div class="space-y-2">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
            Modification d' actions
        </h3>
        <p class="text-sm text-gray-500 mb-2">Les champs suivants ne peuvent être modifiés que par un administrateur
            :</p>
        <ul class="space-y-2 ml-4">
            <li class="flex items-start gap-2 text-gray-600">
                <span class="text-blue-500 mt-1">•</span>
                <span>L'intitulé</span>
            </li>
            <li class="flex items-start gap-2 text-gray-600">
                <span class="text-blue-500 mt-1">•</span>
                <span>Le type (PST, pérenne...)</span>
            </li>
            <li class="flex items-start gap-2 text-gray-600">
                <span class="text-blue-500 mt-1">•</span>
                <span>Onglet ODDS</span>
            </li>
        </ul>
        <p class="text-sm text-gray-500 mb-2">Peuvent modifier les actions si :</p>
        <ul class="space-y-2 ml-4">
            <li class="flex items-start gap-2 text-gray-600">
                <span class="text-orange-500 mt-1">•</span>
                <span>Agents pilotes</span>
            </li>
            <li class="flex items-start gap-2 text-gray-600">
                <span class="text-orange-500 mt-1">•</span>
                <span>Agents appartenant à l’un des services porteurs de l’action</span>
            </li>
        </ul>
    </div>
    <!-- En-tête -->
    <div class="text-xl font-semibold text-gray-800 border-b border-gray-200 pb-3">
        Quelles sont les données en commun
    </div>

    <!-- Section Création -->
    <div class="space-y-2">

        <div class="flex items-start flex-col gap-2 text-gray-600">
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>La liste des services.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>La liste des partenaires.</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-green-500 mt-0.5">✓</span>
                <span>La liste des ODD.</span>
            </div>
        </div>
    </div>
</div>

@foreach(RoleEnum::cases() as $role)

    <div class="flex flex-col gap-2 p-2 bg-white rounded-2xl shadow-md border border-gray-200 w-full max-w-md">
        <div class="text-xl font-semibold text-gray-800">
            {{$role->getLabel()}}
        </div>
        <div class="text-gray-600">
            {{$role->getDescription()}}
        </div>
    </div>

@endforeach
