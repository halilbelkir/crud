<x-crudPackage::layouts.app
    :title="$user->exists ? $user->name : 'Yeni kullanıcı'"
    :breadcrumb="[['label' => 'Kullanıcılar', 'url' => route('users.index')], ['label' => $user->exists ? 'Düzenle' : 'Yeni']]"
>
    <div class="max-w-[760px]">
        <x-crudPackage::ui.card title="Kullanıcı bilgileri" subtitle="Panele giriş yapacak kişinin bilgileri">
            @include('crudPackage::users.form')
        </x-crudPackage::ui.card>
    </div>
</x-crudPackage::layouts.app>
