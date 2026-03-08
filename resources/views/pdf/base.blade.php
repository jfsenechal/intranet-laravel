<html lang="en">
<head>
    <title>Action</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>


<div class="px-2 py-8 max-w-xl mx-auto">

    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center">
            <div class="text-gray-700 font-semibold text-lg">PST</div>
        </div>
        <div class="text-gray-700">
            @php $logo = public_path('images/Marche_logo.png'); @endphp
            @inlinedImage($logo)
        </div>
    </div>
    <div class="border-b-2 border-gray-300 pb-8 mb-8">
        <h2 class="text-2xl font-bold mb-4">{{$declaration->name}}</h2>
    </div>
    <table class="w-full text-left mb-8">
        <tr>
            <th class="text-gray-700 font-bold uppercase py-2">Etat d'avancement</th>
            <td class="py-4 text-gray-700">{{$declaration->state->getLabel()}}</td>
        </tr>
        <tr>
            <th class="text-gray-700 font-bold uppercase py-2">Type</th>
            <td class="py-4 text-gray-700">
                @if($declaration->type)
                    {{$declaration->type->getLabel()}}
                @endif
            </td>
        </tr>
        <tr>
            <th class="text-gray-700 font-bold uppercase py-2">Agents</th>
            <td class="py-4 text-gray-700">
                @foreach($declaration->users()->get() as $agent)
                    {{$declaration->first_name}} {{$agent->last_name}}
                @endforeach
            </td>
        </tr>
        <tr>
            <th class="text-gray-700 font-bold uppercase py-2">Services</th>
            <td class="py-4 text-gray-700">
                @foreach($declaration->leaderServices()->get() as $service)
                    {{$service->name}}
                @endforeach
            </td>
        </tr>
    </table>

    <div class="text-right mb-8">
        <div class="text-gray-700 mr-2">{{$declaration->description}}</div>

    </div>
    <div class="flex justify-end mb-8">
        <div class="text-gray-700 mr-2">Xxx</div>
        <div class="text-gray-700 font-bold text-xl">xxx.5xx0</div>
    </div>
    <div class="border-t-2 border-gray-300 pt-8 mb-8">
        <div class="text-gray-700 mb-2">

        </div>
    </div>
</div>

</body>
</html>
