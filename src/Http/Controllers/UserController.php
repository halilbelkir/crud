<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Models\RoleGroup;
use crudPackage\Models\User;
use crudPackage\Support\Actions;
use crudPackage\Support\Permissions;
use crudPackage\Support\Search;
use crudPackage\Support\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request): View|Response
    {
        $records = $this->scoped($request)
            ->with('roleGroup')
            ->when($request->filled('q'), function ($query) use ($request)
            {
                $term = Search::pattern((string) $request->string('q'));

                $query->where(function ($inner) use ($term)
                {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->integer('status')))
            ->when($request->filled('role'), fn ($query) => $query->where('role_group_id', $request->integer('role')))
            ->orderByDesc('id');

        $columns = $this->columns();
        $records = Table::sort($records, $columns);

        if ($export = Table::export($records, $columns, 'Kullanıcılar'))
        {
            return $export;
        }

        $users  = $records->paginate(Table::perPage())->withQueryString();
        $roles  = $this->roles($request);
        $total   = $this->scoped($request)->count();
        $active  = $this->scoped($request)->where('status', 1)->count();
        $pending = $this->scoped($request)->where('force_password_change', 1)->count();

        $metrics = [
            [
                'label'   => 'Toplam kullanıcı',
                'value'   => $total,
                'percent' => 100,
                'tone'    => 'primary',
            ],
            [
                'label'   => 'Aktif kullanıcı',
                'value'   => $active,
                'percent' => $total > 0 ? (int) round($active / $total * 100) : 0,
                'tone'    => 'success',
            ],
            [
                'label'   => 'Pasif kullanıcı',
                'value'   => $total - $active,
                'percent' => $total > 0 ? (int) round(($total - $active) / $total * 100) : 0,
                'tone'    => 'accent',
            ],
            [
                'label'   => 'Bekleyen davet',
                'value'   => $pending,
                'percent' => $total > 0 ? (int) round($pending / $total * 100) : 0,
                'tone'    => 'accent',
            ],
        ];

        return view('crudPackage::users.index', compact('users', 'roles', 'columns', 'metrics', 'total', 'active', 'pending'));
    }

    protected function columns(): array
    {
        return [
            [
                'type'  => 'check',
                'value' => fn (User $user) => $user->getKey(),
            ],
            [
                'type'     => 'lead',
                'label'    => 'Kullanıcı',
                'width'    => '2.2fr',
                'sortable' => 'name',
                'name'     => fn (User $user) => $user->name,
                'sub'      => fn (User $user) => $user->email,
            ],
            [
                'type'  => 'badge',
                'label' => 'Yetki',
                'width' => '1.2fr',
                'value' => fn (User $user) => $user->roleGroup?->title ?? 'Yetkisiz',
                'tone'  => fn (User $user) => $user->role_group_id == 1 ? 'primary' : 'accent',
            ],
            [
                'type'     => 'status',
                'label'    => 'Durum',
                'width'    => '0.9fr',
                'sortable' => 'status',
                'value'    => fn (User $user) => $this->statusLabel($user),
                'tone'     => fn (User $user) => $user->isInvitePending() ? 'accent' : ($user->status == 1 ? 'success' : 'muted'),
            ],
            [
                'type'  => 'actions',
                'width' => '60px',
                'menu'  => fn (User $user) => Actions::menu('users', $user,
                [
                    'editTitle' => 'Kullanıcıyı düzenle',
                    'extra'     => $this->inviteAction($user),
                ]),
            ],
        ];
    }

    protected function statusLabel(User $user): string
    {
        if ($user->isInvitePending())
        {
            return 'Davet bekliyor';
        }

        return $user->status == 1 ? 'Aktif' : 'Pasif';
    }

    protected function inviteAction(User $user): array
    {
        if (! $user->isInvitePending() || ! Permissions::allows('users.invite.update'))
        {
            return [];
        }

        return [[
            'label'   => 'Daveti tekrar gönder',
            'icon'    => 'mail',
            'href'    => route('users.invite.update', $user),
            'method'  => 'POST',
            'confirm' => [
                'title'        => 'Davet tekrar gönderilsin mi?',
                'message'      => $user->email . ' adresine yeni bir şifre belirleme bağlantısı gönderilecek. Önceki bağlantı geçersiz olur.',
                'confirmLabel' => 'Evet, gönder',
            ],
        ]];
    }

    public function show(User $user): View
    {
        $user->loadMissing('roleGroup');

        return view('crudPackage::users.detail', compact('user'));
    }

    public function create(Request $request): View
    {
        $user  = new User(['status' => 1]);
        $roles = $this->roles($request);

        return view($request->ajax() ? 'crudPackage::users.form' : 'crudPackage::users.edit', compact('user', 'roles'));
    }

    public function edit(Request $request, User $user): View
    {
        $roles = $this->roles($request);

        return view($request->ajax() ? 'crudPackage::users.form' : 'crudPackage::users.edit', compact('user', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = new User;

        $user->forceFill(
        [
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'role_group_id'         => $data['role_group_id'],
            'status'                => $data['status'],
            'force_password_change' => 1,
            'password'              => Hash::make(Str::password(32)),
        ])->save();

        if (! $this->deliverInvite($user))
        {
            return redirect()->route('users.index')
                ->with('error', $user->name . ' oluşturuldu fakat davet e-postası gönderilemedi. Mail ayarlarını kontrol edip daveti tekrar gönderin.')
                ->with('reload', true);
        }

        return redirect()->route('users.index')->with('status', $user->name . ' oluşturuldu, şifre belirleme daveti gönderildi.');
    }

    public function invite(User $user): RedirectResponse
    {
        if (! $user->isInvitePending())
        {
            return back()->with('status', $user->name . ' şifresini zaten belirlemiş.');
        }

        if (! $this->deliverInvite($user))
        {
            return back()->with('error', 'Davet e-postası gönderilemedi. Mail ayarlarını kontrol edip tekrar deneyin.');
        }

        return back()->with('status', $user->name . ' için davet tekrar gönderildi.');
    }

    protected function deliverInvite(User $user): bool
    {
        try
        {
            $user->sendInvite();
        }
        catch (\Throwable $exception)
        {
            report($exception);

            return false;
        }

        return true;
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        $user->forceFill($data)->save();

        return redirect()->route('users.index')->with('status', 'Kullanıcı güncellendi.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user()))
        {
            return back()->with('status', 'Kendi hesabınızı silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Kullanıcı silindi.');
    }

    protected function scoped(Request $request)
    {
        $user = $request->user();

        return User::query()
            ->whereKeyNot($user->getKey())
            ->when($user->role_group_id != 1, fn ($query) => $query->where('role_group_id', $user->role_group_id));
    }

    protected function roles(Request $request): array
    {
        $user = $request->user();

        return RoleGroup::query()
            ->when($user->role_group_id != 1, fn ($query) => $query->whereKey($user->role_group_id))
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        return $request->validate(
        [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role_group_id' => ['required', Rule::in(array_keys($this->roles($request)))],
            'status'        => ['required', 'in:0,1'],
        ],
        [],
        [
            'name'          => 'Ad & Soyad',
            'email'         => 'E-mail',
            'role_group_id' => 'Yetki',
            'status'        => 'Durum',
        ]);
    }
}
