<div class="text-gray-800 fs-2qx fw-bold text-center mb-10">
    <img src="{{ strstr(settings('logo'),'crud') ? asset(settings('logo')) : Storage::disk('upload')->url(settings('logo')) }}" style="height: 60px" alt="">
</div>