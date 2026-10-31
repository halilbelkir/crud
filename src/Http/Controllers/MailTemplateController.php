<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Mail\TemplateMail;
use crudPackage\Models\MailTemplate;
use crudPackage\Support\MailTemplates;
use crudPackage\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailTemplateController extends Controller
{
    public function index(): View
    {
        $stored = MailTemplate::all()->keyBy('key');

        $templates = collect(MailTemplates::all())
            ->map(fn ($definition, $key) => (object)
            [
                'key'        => $key,
                'title'      => $definition['title'] ?? $key,
                'subject'    => MailTemplates::fields($key)['subject'],
                'custom'     => $stored->has($key),
                'updated_at' => $stored->get($key)?->updated_at,
            ])
            ->values();

        $columns = $this->columns();

        return view('crudPackage::mailTemplates.index', compact('templates', 'columns'));
    }

    protected function columns(): array
    {
        return [
            [
                'type'  => 'lead',
                'label' => 'Şablon',
                'width' => '2fr',
                'name'  => fn ($template) => $template->title,
                'sub'   => fn ($template) => $template->subject,
            ],
            [
                'type'  => 'badge',
                'label' => 'İçerik',
                'width' => '1fr',
                'value' => fn ($template) => $template->custom ? 'Düzenlendi' : 'Varsayılan',
                'tone'  => fn ($template) => $template->custom ? 'primary' : 'accent',
            ],
            [
                'type'  => 'text',
                'label' => 'Son düzenleme',
                'width' => '1fr',
                'value' => fn ($template) => $template->updated_at?->translatedFormat('d.m.Y H:i') ?? '—',
            ],
            [
                'type'  => 'actions',
                'width' => '60px',
                'menu'  => fn ($template) => $this->menu($template),
            ],
        ];
    }

    protected function menu(object $template): array
    {
        $items = [];

        if (Permissions::allows('mail-templates.show'))
        {
            $items[] = [
                'label'  => 'Önizle',
                'icon'   => 'eye',
                'href'   => route('mail-templates.show', $template->key),
                'drawer' => true,
                'title'  => $template->title . ' önizlemesi',
            ];
        }

        if (Permissions::allows('mail-templates.edit'))
        {
            $items[] = [
                'label'  => 'Düzenle',
                'icon'   => 'pen',
                'href'   => route('mail-templates.edit', $template->key),
                'drawer' => true,
                'title'  => $template->title . ' şablonunu düzenle',
            ];
        }

        if ($template->custom && Permissions::allows('mail-templates.destroy'))
        {
            $items[] = 'separator';
            $items[] = [
                'label'   => 'Varsayılana dön',
                'icon'    => 'history',
                'href'    => route('mail-templates.destroy', $template->key),
                'method'  => 'DELETE',
                'confirm' => [
                    'title'        => $template->title . ' şablonu varsayılana dönsün mü?',
                    'message'      => 'Yaptığınız değişiklikler silinir, e-posta paketin varsayılan metniyle gönderilir.',
                    'tone'         => 'warning',
                    'confirmLabel' => 'Evet, varsayılana dön',
                ],
            ];
        }

        return $items;
    }

    public function show(Request $request, string $key): View
    {
        abort_unless(MailTemplates::exists($key), 404);

        $definition = MailTemplates::definition($key);
        $html       = (new TemplateMail($key, MailTemplates::samples($key), url('/')))->render();

        return view($request->ajax() ? 'crudPackage::mailTemplates.preview' : 'crudPackage::mailTemplates.detail', compact('key', 'definition', 'html'));
    }

    public function edit(Request $request, string $key): View
    {
        abort_unless(MailTemplates::exists($key), 404);

        $definition = MailTemplates::definition($key);
        $fields     = MailTemplates::fields($key);

        return view($request->ajax() ? 'crudPackage::mailTemplates.form' : 'crudPackage::mailTemplates.edit', compact('key', 'definition', 'fields'));
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        abort_unless(MailTemplates::exists($key), 404);

        $data = $request->validate(
        [
            'subject' => ['required', 'string', 'max:255'],
            'heading' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'button'  => ['required', 'string', 'max:100'],
            'note'    => ['nullable', 'string'],
        ],
        [],
        [
            'subject' => 'Konu',
            'heading' => 'Başlık',
            'content' => 'Metin',
            'button'  => 'Buton yazısı',
            'note'    => 'Not',
        ]);

        MailTemplate::updateOrCreate(['key' => $key], $data);

        return redirect()->route('mail-templates.index')->with('status', 'E-posta şablonu kaydedildi.');
    }

    public function destroy(string $key): RedirectResponse
    {
        abort_unless(MailTemplates::exists($key), 404);

        MailTemplate::where('key', $key)->delete();

        return redirect()->route('mail-templates.index')->with('status', 'E-posta şablonu varsayılana döndü.');
    }
}
