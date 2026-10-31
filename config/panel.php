<?php

return [

    'quick_action' => null,

    'menu_icons' => [
        'grid',
        'users',
        'user',
        'user-check',
        'settings',
        'file',
        'menu',
        'chart',
        'wallet',
        'bank',
        'shield-check',
        'lock',
        'history',
        'clock',
        'calendar',
        'mail',
        'phone',
        'inbox',
        'bell',
        'link',
        'search',
        'upload',
        'export',
        'info',
    ],

    'menu_icon_aliases' => [
        'ki-screen'        => 'file',
        'ki-pointers'      => 'shield-check',
        'ki-profile-user'  => 'users',
        'ki-burger-menu-6' => 'menu',
        'ki-setting-2'     => 'settings',
        'bi-clock-history' => 'history',
        'bi-images'        => 'upload',
        'bi-view-list'     => 'menu',
        'bi-view-stacked'  => 'menu',
        'fa-list-check'    => 'menu',
        'bi-card-text'     => 'file',
        'bi-file-earmark'  => 'file',
        'bi-question'      => 'info',
        'bi-signpost'      => 'link',
        'bi-chat'          => 'mail',
        'bi-input-cursor'  => 'inbox',
        'bi-gear'          => 'settings',
        'bi-house-gear'    => 'settings',
        'bi-suitcase'      => 'calendar',
        'bi-buildings'     => 'bank',
    ],

    'title_columns' => ['title', 'name', 'name_surname', 'question', 'subject', 'email', 'phone'],

    'field' => [
        'box' => [
            'md'   => 'h-field rounded-[26px] px-5 gap-3',
            'sm'   => 'h-field-sm rounded-[20px] px-4 gap-3',
            'grid' => 'h-[38px] rounded-xl px-2.5 gap-2',
            'row'  => 'h-[38px] rounded-[19px] px-3.5 gap-2',
            'line' => 'h-11 rounded-[22px] px-4 gap-2.5',
            'auth' => 'h-field-auth rounded-[30px] px-5 gap-3',
            'area' => 'rounded-[20px] px-5 py-4 gap-3',
        ],

        'chevron' => [
            'grid' => 14,
            'row'  => 14,
            'line' => 15,
        ],

        'text' => [
            'md'   => 'text-[15px]',
            'sm'   => 'text-sm',
            'grid' => 'text-xs',
            'row'  => 'text-[12.5px]',
            'line' => 'text-[13.5px]',
            'auth' => 'text-[15px]',
            'area' => 'text-[15px]',
        ],
    ],

    'actions' => [

        'show' => [
            'label' => 'Detay',
            'icon'  => 'eye',
            'route' => 'show',
        ],

        'edit' => [
            'label' => 'Düzenle',
            'icon'  => 'pen',
            'route' => 'edit',
        ],

        'delete' => [
            'label' => 'Sil',
            'icon'  => 'trash',
            'route' => 'destroy',
        ],

    ],

    'status' => [
        'column' => 'status',
        'models' => [
            'users' => \crudPackage\Models\User::class,
            'menus' => \crudPackage\Models\Menu::class,
            'cruds' => \crudPackage\Models\Crud::class,
        ],
    ],

    'sortable' => [
        'column' => 'order',
        'models' => [
            'menus'      => \crudPackage\Models\MenuItem::class,
            'crud-items' => \crudPackage\Models\CrudItem::class,
        ],
    ],

    'media' => [
        'hidden' => ['cache'],
    ],

    'mail_templates' => [
        'invite' => [
            'title'     => 'Kullanıcı daveti',
            'subject'   => '{site} paneline davet edildiniz',
            'preview'   => 'Şifrenizi belirlemek için bağlantı {days} gün geçerlidir.',
            'heading'   => 'Hoş geldiniz',
            'content'   => '<p>Merhaba {name},<br>Sizin için <strong>{site}</strong> panelinde bir hesap oluşturuldu. Hesabınızı kullanmaya başlamak için aşağıdaki butona tıklayıp şifrenizi belirleyin.</p>',
            'button'    => 'Şifremi belirle',
            'note'      => '<p>Bağlantı <strong>{days} gün</strong> geçerlidir. Süresi dolarsa giriş ekranındaki <strong>Şifremi unuttum</strong> bağlantısıyla yeni bir bağlantı isteyebilirsiniz.</p>',
            'variables' => [
                'name' => ['label' => 'Kullanıcının adı',                'sample' => 'Ayşe Yılmaz'],
                'site' => ['label' => 'Panel adı',                       'sample' => null],
                'days' => ['label' => 'Bağlantının geçerli olduğu gün', 'sample' => 7],
            ],
        ],

        'password_reset' => [
            'title'     => 'Şifre sıfırlama',
            'subject'   => 'Şifre sıfırlama talebi · {site}',
            'preview'   => 'Şifre sıfırlama bağlantınız {expire} dakika geçerlidir.',
            'heading'   => 'Şifre sıfırlama talebi',
            'content'   => '<p>Merhaba {name},<br>Hesabınız için bir şifre sıfırlama talebi aldık. Yeni şifrenizi belirlemek için aşağıdaki butona tıklayın.</p>',
            'button'    => 'Yeni şifre belirle',
            'note'      => '<p>Bağlantı <strong>{expire} dakika</strong> geçerlidir. Bu talebi siz yapmadıysanız bu e-postayı yok sayabilirsiniz; şifreniz değişmez.</p>',
            'variables' => [
                'name'   => ['label' => 'Kullanıcının adı',                   'sample' => 'Ayşe Yılmaz'],
                'site'   => ['label' => 'Panel adı',                          'sample' => null],
                'expire' => ['label' => 'Bağlantının geçerli olduğu dakika', 'sample' => 60],
            ],
        ],
    ],

    'logs' => [
        'updated' => '2',

        'events' => [
            '1' => ['label' => 'Eklendi',     'tone' => 'success'],
            '2' => ['label' => 'Güncellendi', 'tone' => 'accent'],
            '3' => ['label' => 'Silindi',     'tone' => 'primary'],
        ],

        'skipped' => [
            'id',
            'created_at',
            'updated_at',
        ],

        'hidden' => [
            'password',
            'remember_token',
            '*_password',
            '*_token',
            '*_secret',
        ],

        'tables' => [
            'crud_items' => 'Modül alanları',
            'menu_items' => 'Menü öğeleri',
            'menus'      => 'Menüler',
            'roles'      => 'Yetki izinleri',
        ],

        'fields' => [
            'title'           => 'Başlık',
            'name'            => 'Ad soyad',
            'email'           => 'E-posta',
            'status'          => 'Durum',
            'order'           => 'Sıra',
            'slug'            => 'Slug',
            'role_group_id'   => 'Yetki',
            'crud_id'         => 'Modül',
            'menu_item_id'    => 'Menü öğesi',
            'browse'          => 'Listeleme',
            'read'            => 'Detay görme',
            'edit'            => 'Düzenleme',
            'add'             => 'Ekleme',
            'delete'          => 'Silme',
            'seo_title'       => 'SEO başlığı',
            'seo_description' => 'SEO açıklaması',
        ],
    ],

    'errors' => [

        'default' => [
            'icon'    => 'alert',
            'code'    => 'HATA',
            'title'   => 'Bir şeyler ters gitti',
            'message' => 'İşlem tamamlanamadı. Sayfayı yenileyip tekrar deneyin.',
        ],

        'codes' => [

            401 => [
                'icon'    => 'lock',
                'title'   => 'Yetkiniz bulunamadı',
                'message' => 'Bu işlem için yetkiniz yok. Erişmeniz gerekiyorsa ana kullanıcınızdan rolünüze izin eklenmesini isteyin.',
            ],

            403 => [
                'icon'    => 'lock',
                'title'   => 'Bu sayfaya erişemezsiniz',
                'message' => 'Bu bölüm için yetkiniz yok. Erişmeniz gerekiyorsa yöneticinizden rolünüze izin eklenmesini isteyin.',
            ],

            404 => [
                'icon'    => 'search',
                'title'   => 'Sayfa bulunamadı',
                'message' => 'Aradığınız sayfa taşınmış, adı değişmiş ya da kayıt silinmiş olabilir.',
            ],

            419 => [
                'icon'    => 'clock',
                'title'   => 'Oturumunuz zaman aşımına uğradı',
                'message' => 'Uzun süre işlem yapılmadığı için form geçersiz oldu. Sayfayı yenileyip tekrar deneyin.',
            ],

            429 => [
                'icon'    => 'history',
                'title'   => 'Çok fazla istek gönderildi',
                'message' => 'Kısa sürede çok sayıda deneme yapıldı. Bir süre bekleyip tekrar deneyin.',
            ],

            500 => [
                'icon'    => 'alert',
                'title'   => 'Sunucu hatası',
                'message' => 'İstek işlenirken beklenmeyen bir hata oluştu. Sorun sürerse yöneticinize bildirin.',
            ],

            503 => [
                'icon'    => 'settings',
                'title'   => 'Panel bakımda',
                'message' => 'Kısa bir bakım çalışması yapılıyor. Birazdan tekrar deneyin.',
            ],

        ],

    ],

];
