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
        <h2 class="text-2xl font-bold mb-4">{{$declaration->last_name}}</h2>
    </div>
</div>

</body>
</html>
