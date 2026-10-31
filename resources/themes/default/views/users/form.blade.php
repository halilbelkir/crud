<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="flex flex-col gap-5">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div class="grid sm:grid-cols-2 gap-4">
        <x-crudPackage::form.input name="name" label="Ad soyad" :value="old('name', $user->name)" required />
        <x-crudPackage::form.input name="email" type="email" label="E-posta" :value="old('email', $user->email)" required />
        <x-crudPackage::form.select
            name="role_group_id"
            label="Yetki"
            :options="$roles"
            :selected="old('role_group_id', $user->role_group_id)"
            placeholder="Yetki seçin"
            required
        />
    </div>

    @if (! $user->exists)
        <x-crudPackage::ui.alert tone="outline" icon="mail">
            Kullanıcıya hoş geldiniz e-postası gönderilecek, şifresini kendisi belirleyecek.
        </x-crudPackage::ui.alert>
    @endif

    <x-crudPackage::form.field label="Durum">
        <x-crudPackage::form.toggle
            name="status"
            on-label="Aktif"
            off-label="Pasif"
            :checked="(bool) old('status', $user->status ?? 1)"
        />
    </x-crudPackage::form.field>

    <div class="-mx-5 lg:-mx-8 mt-1 px-5 lg:px-8 pt-4 lg:pt-5 border-t border-line-soft flex items-center gap-3">
        <x-crudPackage::ui.cancel-button :href="route('users.index')" size="field" />
        <x-crudPackage::ui.button type="submit" size="field" trailing="arrow" class="flex-1">Kaydet</x-crudPackage::ui.button>
    </div>
</form>
