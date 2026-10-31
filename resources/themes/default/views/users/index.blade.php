<x-crudPackage::layouts.app
    title="Kullanıcılar"
    :breadcrumb="[['label' => 'Yönetim'], ['label' => 'Kullanıcılar']]"
>
    <div class="flex flex-col gap-3.5">

        <div data-refresh="metrics" class="hidden lg:grid grid-cols-4 gap-3.5">
            @foreach ($metrics as $metric)
                <x-crudPackage::ui.metric
                    :label="$metric['label']"
                    :value="$metric['value']"
                    :percent="$metric['percent']"
                    :tone="$metric['tone']"
                />
            @endforeach
        </div>

        <x-crudPackage::ui.datatable
            heading="Kullanıcılar"
            :subheading="$total . ' kullanıcı · ' . $active . ' aktif'
                . ($pending ? ' · Davet bekleyen: ' . $pending : '')"
            :columns="$columns"
            :rows="$users"
            :search="['name' => 'q', 'placeholder' => 'İsim veya e-posta ara']"
            :filters="[
                ['name' => 'role', 'label' => 'Yetki', 'options' => $roles],
            ]"
            :create="\crudPackage\Support\Permissions::allows('users.create') ? ['label' => 'Yeni kullanıcı', 'href' => route('users.create')] : null"
            record-label="kullanıcıyı"
            empty-title="Kullanıcı bulunamadı"
            empty-message="Aramanıza uyan kullanıcı yok. Filtreleri temizleyip tekrar deneyin."
        />

    </div>
</x-crudPackage::layouts.app>
