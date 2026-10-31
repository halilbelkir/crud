<div class="flex flex-col gap-3">
    <span class="text-xs text-muted">Örnek verilerle gösteriliyor; buton bağlantısı gönderimde kişiye özel oluşturulur.</span>

    <iframe
        srcdoc="{{ $html }}"
        title="{{ $definition['title'] ?? $key }}"
        class="w-full h-[640px] rounded-[20px] border border-line-soft bg-app"
    ></iframe>
</div>
