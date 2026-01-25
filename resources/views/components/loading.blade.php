<div class="loading d-inline-block d-none">
    <img src="{{ strstr(settings('loader'),'crud') ? asset(settings('loader')) : Storage::disk('upload')->url(settings('loader')) }}" alt="" style="height: 50px">
</div>