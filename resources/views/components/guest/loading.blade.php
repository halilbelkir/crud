<div class="loading justify-content-center d-flex d-none" style="height: 50px">
    <img src="{{ strstr(settings('loader'),'crud') ? asset(settings('loader')) : uploadUrl(settings('loader')) }}">
</div>