import Alpine from 'alpinejs';

function modal(action, name)
{
    window.dispatchEvent(new CustomEvent('modal-' + action, { detail: name }));
}

async function copyBlob(type, blob, message, failure)
{
    try
    {
        if (typeof ClipboardItem !== 'undefined' && navigator.clipboard?.write)
        {
            await navigator.clipboard.write([new ClipboardItem({ [type]: blob })]);
        }
        else if (type === 'text/plain')
        {
            await navigator.clipboard.writeText(await (await blob).text());
        }
        else
        {
            throw new Error('unsupported');
        }

        Alpine.store('toast').show(message);

        return true;
    }
    catch (error)
    {
        Alpine.store('toast').show(failure, 'danger');

        return false;
    }
}

function copyImage(url, message = 'Görsel kopyalandı')
{
    const blob = Promise.resolve(url).then(target => fetch(target, { credentials: 'same-origin' })).then(response =>
    {
        if (! response.ok)
        {
            throw new Error(String(response.status));
        }

        return response.blob();
    });

    return copyBlob('image/png', blob, message, 'Görsel kopyalanamadı, lütfen tekrar deneyin.');
}

function copyText(text, message = 'Kopyalandı')
{
    const blob = Promise.resolve(text).then(value => new Blob([value], { type: 'text/plain' }));

    return copyBlob('text/plain', blob, message, 'Kopyalanamadı, lütfen tekrar deneyin.');
}

function postJson(url, body = {})
{
    return fetch(url,
    {
        method: 'POST',
        headers:
        {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        },
        body: JSON.stringify(body),
    }).then(async response =>
    {
        if (! response.ok)
        {
            const error = new Error(String(response.status));

            error.status = response.status;
            error.data   = await response.json().catch(() => ({}));

            throw error;
        }

        return response.json();
    });
}

window.copyImage   = copyImage;
window.copyText    = copyText;
window.postJson    = postJson;

Alpine.store('overlay',
{
    open: [],
    top: 0,

    init()
    {
        const viewport = window.visualViewport;

        if (! viewport)
        {
            return;
        }

        const settle = () =>
        {
            if (! this.open.length || viewport.height < window.innerHeight - 1 || window.scrollY === this.top)
            {
                return;
            }

            window.scrollTo(0, this.top);
        };

        viewport.addEventListener('resize', settle);
        window.addEventListener('scroll', settle, { passive: true });
    },

    set(id, state)
    {
        const index = this.open.indexOf(id);

        if (state && index === -1)
        {
            if (! this.open.length)
            {
                this.top = window.scrollY;
            }

            this.open.push(id);
        }

        if (! state && index !== -1)
        {
            this.open.splice(index, 1);
        }

        document.documentElement.classList.toggle('overlay-open', this.open.length > 0);
    },
});

Alpine.store('ui',
{
    sidebar: localStorage.getItem('ui.sidebar') !== 'collapsed',
    mobileMenu: false,

    toggleSidebar()
    {
        this.sidebar = !this.sidebar;

        localStorage.setItem('ui.sidebar', this.sidebar ? 'open' : 'collapsed');
        document.documentElement.classList.toggle('menu-collapsed', ! this.sidebar);
    },

    openMobileMenu()
    {
        this.mobileMenu = true;
    },

    closeMobileMenu()
    {
        this.mobileMenu = false;
    },
});

Alpine.store('menu',
{
    closed: JSON.parse(localStorage.getItem('ui.menu.closed') || 'null'),

    prime(defaults)
    {
        if (this.closed === null)
        {
            this.closed = defaults;
        }
    },

    open(group)
    {
        return ! (this.closed || []).includes(group);
    },

    toggle(group)
    {
        this.closed = this.open(group)
            ? [...(this.closed || []), group]
            : (this.closed || []).filter(item => item !== group);

        localStorage.setItem('ui.menu.closed', JSON.stringify(this.closed));
    },
});

Alpine.store('toast',
{
    items: [],
    counter: 0,

    show(message, tone = 'success', lead = null)
    {
        this.counter += 1;

        this.items = [...this.items,
        {
            id:      this.counter,
            message: message,
            lead:    lead,
            tone:    tone,
        }];
    },

    close(id)
    {
        this.items = this.items.filter(item => item.id !== id);
    },
});

Alpine.store('dialog',
{
    confirm:
    {
        open: false,
        title: '',
        message: '',
        tone: 'danger',
        icon: null,
        confirmLabel: 'ONAYLA',
        cancelLabel: 'Vazgeç',
        verify: null,
        typed: '',
        choices: null,
        choiceName: null,
        choiceLabel: '',
        choice: '',
        prompt: null,
        image: null,
        value: '',
        action: null,
        declined: null,
        busy: false,
    },

    result:
    {
        open: false,
        title: '',
        message: '',
        tone: 'success',
    },

    ask(options)
    {
        this.confirm = {
            open: true,
            title: options.title || 'Emin misiniz?',
            message: options.message || '',
            tone: options.tone || 'danger',
            icon: options.icon || null,
            confirmLabel: options.confirmLabel || 'ONAYLA',
            cancelLabel: options.cancelLabel || 'Vazgeç',
            verify: options.verify || null,
            typed: '',
            choices: options.choices || null,
            choiceName: options.choiceName || null,
            choiceLabel: options.choiceLabel || '',
            choice: '',
            prompt: options.prompt || null,
            image: options.image || null,
            value: options.value || '',
            action: options.action || null,
            declined: options.declined || null,
            busy: false,
        };
    },

    verified()
    {
        if (this.confirm.choices && ! this.confirm.choice)
        {
            return false;
        }

        if (this.confirm.prompt && ! this.confirm.value.trim())
        {
            return false;
        }

        if (! this.confirm.verify)
        {
            return true;
        }

        return this.confirm.typed.trim().toLocaleUpperCase('tr') === this.confirm.verify.toLocaleUpperCase('tr');
    },

    extra()
    {
        if (! this.confirm.choiceName || ! this.confirm.choice)
        {
            return {};
        }

        return { [this.confirm.choiceName]: this.confirm.choice };
    },

    cancel()
    {
        if (this.confirm.busy)
        {
            return;
        }

        const declined = this.confirm.declined;

        this.confirm.open = false;
        this.confirm.action = null;
        this.confirm.declined = null;

        if (declined)
        {
            declined();
        }
    },

    escape()
    {
        if (this.confirm.busy)
        {
            window.location.reload();

            return;
        }

        this.cancel();
    },

    handleEscape(event)
    {
        if (! this.confirm.open)
        {
            this.dismiss();

            return;
        }

        event.stopPropagation();

        this.escape();
    },

    async accept(button = null)
    {
        if (! this.verified() || this.confirm.busy)
        {
            return;
        }

        const action = this.confirm.action;

        this.confirm.declined = null;

        if (! action)
        {
            this.confirm.open = false;

            return;
        }

        this.confirm.busy = true;

        buttonLoading(button, true);

        try
        {
            await action();
        }
        finally
        {
            buttonLoading(button, false);

            if (this.confirm.action === action)
            {
                this.confirm.busy = false;
                this.confirm.open = false;
                this.confirm.action = null;
            }
        }
    },

    notify(title, message = '', tone = 'success')
    {
        this.result = { open: true, title, message, tone };
    },

    dismiss()
    {
        this.result.open = false;
    },
});

const FOLD_MAP = {
    'ı': 'i', 'İ': 'i', 'ş': 's', 'Ş': 's', 'ğ': 'g', 'Ğ': 'g',
    'ü': 'u', 'Ü': 'u', 'ö': 'o', 'Ö': 'o', 'ç': 'c', 'Ç': 'c',
};

function fold(value)
{
    return String(value ?? '')
        .replace(/[ıİşŞğĞüÜöÖçÇ]/g, character => FOLD_MAP[character])
        .toLowerCase();
}

window.fold = fold;

Alpine.data('searchSelect', (config = {}) => (
{
    items:       config.items || [],
    multiple:    config.multiple === true,
    value:       config.multiple === true ? (config.value || []) : (config.value ?? ''),
    placeholder: config.placeholder || 'Seçin',
    searchable:  config.searchable !== false,
    floating:    config.floating !== false,
    locked:      config.locked === true,
    width:       config.width || 0,
    open:        false,
    query:       '',
    active:      -1,
    alignRight:  false,
    dropUp:      false,
    anchored:    config.floating !== false ? { position: 'fixed', left: '-9999px', top: '-9999px' } : {},
    reposition:  null,

    get filtered()
    {
        if (! this.query)
        {
            return this.items;
        }

        const needle = fold(this.query);

        return this.items.filter(item => fold(item.label).includes(needle));
    },

    get current()
    {
        return this.items.find(item => String(item.value) === String(this.value)) || null;
    },

    get empty()
    {
        return this.multiple ? this.value.length === 0 : ! this.value;
    },

    chosen(value)
    {
        return this.multiple
            ? this.value.map(String).includes(String(value))
            : String(this.value) === String(value);
    },

    get label()
    {
        if (! this.multiple)
        {
            return this.current ? this.current.label : '';
        }

        const picked = this.items.filter(item => this.chosen(item.value));

        if (picked.length === 0)
        {
            return '';
        }

        return picked.length === 1 ? picked[0].label : picked[0].label + ' +' + (picked.length - 1);
    },

    escape(value)
    {
        const holder = document.createElement('span');

        holder.textContent = String(value ?? '');

        return holder.innerHTML;
    },

    marked(label)
    {
        const text = String(label ?? '');

        if (! this.query)
        {
            return this.escape(text);
        }

        const index = fold(text).indexOf(fold(this.query));

        if (index === -1)
        {
            return this.escape(text);
        }

        const end = index + this.query.length;

        return this.escape(text.slice(0, index))
            + '<span class="bg-tint-accent-2 rounded-[3px]">' + this.escape(text.slice(index, end)) + '</span>'
            + this.escape(text.slice(end));
    },

    show()
    {
        if (this.open || this.locked)
        {
            return;
        }

        this.open = true;
        this.query = '';
        this.active = this.filtered.findIndex(item => this.chosen(item.value));

        this.$nextTick(() =>
        {
            this.place();
            this.track(true);
            this.$refs.search?.focus();
            this.scrollToActive();
        });
    },

    place()
    {
        const panel = this.$refs.panel;

        if (! panel)
        {
            return;
        }

        const anchor = this.$el.getBoundingClientRect();
        const margin = 12;

        const width = this.floating ? (this.width || anchor.width) : panel.offsetWidth;

        this.alignRight = anchor.left + width > window.innerWidth - margin;

        const below = window.innerHeight - anchor.bottom;

        this.dropUp = below < panel.offsetHeight + margin && anchor.top > below;

        if (! this.floating)
        {
            return;
        }

        const left = this.alignRight ? anchor.right - width : anchor.left;

        this.anchored = {
            position: 'fixed',
            width:    width + 'px',
            left:     Math.max(margin, Math.min(left, window.innerWidth - margin - width)) + 'px',
            top:      (this.dropUp ? anchor.top - panel.offsetHeight - 6 : anchor.bottom + 6) + 'px',
        };
    },

    track(state)
    {
        if (! this.floating)
        {
            return;
        }

        if (! state)
        {
            document.removeEventListener('scroll', this.reposition, true);
            window.removeEventListener('resize', this.reposition);

            return;
        }

        this.reposition = this.reposition || (() => this.open && this.place());

        document.addEventListener('scroll', this.reposition, true);
        window.addEventListener('resize', this.reposition);
    },

    hide()
    {
        if (! this.open)
        {
            return;
        }

        this.open = false;
        this.query = '';
        this.active = -1;

        this.track(false);
    },

    toggle()
    {
        this.open ? this.hide() : this.show();
    },

    search(term)
    {
        this.query = term;
        this.active = this.filtered.length > 0 ? 0 : -1;
    },

    move(step)
    {
        if (! this.open)
        {
            this.show();

            return;
        }

        const total = this.filtered.length;

        if (total === 0)
        {
            return;
        }

        this.active = (this.active + step + total) % total;

        this.scrollToActive();
    },

    scrollToActive()
    {
        const list = this.$refs.list;
        const row = list?.children[this.active];

        if (! list || ! row)
        {
            return;
        }

        const bottom = row.offsetTop + row.offsetHeight;

        if (row.offsetTop < list.scrollTop)
        {
            list.scrollTop = row.offsetTop;
        }
        else if (bottom > list.scrollTop + list.clientHeight)
        {
            list.scrollTop = bottom - list.clientHeight;
        }
    },

    pick()
    {
        if (! this.open)
        {
            this.show();

            return;
        }

        const item = this.filtered[this.active];

        if (item)
        {
            this.choose(item);
        }
    },

    choose(item)
    {
        if (! this.multiple)
        {
            this.commit(item ? item.value : '');
            this.hide();

            this.$refs.search?.blur();

            return;
        }

        if (! item)
        {
            this.commit([]);

            return;
        }

        const picked = this.value.map(String);
        const index  = picked.indexOf(String(item.value));

        index === -1 ? picked.push(String(item.value)) : picked.splice(index, 1);

        this.commit(picked);
    },

    clear()
    {
        this.commit('');
    },

    apply(value)
    {
        if (value !== '' && ! this.items.some(item => String(item.value) === String(value)))
        {
            return;
        }

        this.commit(value);
    },

    commit(value)
    {
        this.value = value;

        this.$nextTick(() => this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true })));
    },

    load(items)
    {
        this.items = items || [];

        if (! this.current)
        {
            this.commit('');
        }

        this.hide();
    },
}));

const SECRET_HEADER = 'X-Secret-Key';

let secretWaiters = [];
let secretExpiry  = null;

Alpine.data('copyText', (text, message = 'Kopyalandı') => (
{
    async copy()
    {
        try
        {
            await navigator.clipboard.writeText(text);
        }
        catch (error)
        {
            const holder = document.createElement('textarea');

            holder.value = text;
            holder.style.position = 'fixed';
            holder.style.opacity = '0';

            document.body.appendChild(holder);
            holder.select();

            try { document.execCommand('copy'); } catch (fallbackError) {}

            holder.remove();
        }

        Alpine.store('toast').show(message);
    },
}));

Alpine.data('inlineField', (url, column) => (
{
    async save(target)
    {
        if (! url || ! target.name)
        {
            return;
        }

        const toggle = target.type === 'checkbox';
        const value  = toggle ? (target.checked ? 1 : 0) : target.value;

        if (! toggle)
        {
            clearField(target);
        }

        try
        {
            const payload = await postJson(url, { column, value });

            Alpine.store('toast').show(payload.status, 'success');
        }
        catch (error)
        {
            const message = error.data?.errors?.value?.[0] || error.data?.message || 'Kaydedilemedi, lütfen tekrar deneyin.';

            Alpine.store('toast').show(message, 'danger');

            if (toggle)
            {
                await refreshContent();

                return;
            }

            markField(target, message);
        }
    },
}));

Alpine.data('jsonField', () => (
{
    invalid: false,

    init()
    {
        this.format(false);

        const watcher = new IntersectionObserver((entries) =>
        {
            if (entries.some(entry => entry.isIntersecting))
            {
                this.resize();
            }
        });

        watcher.observe(this.$refs.input);
    },

    resize()
    {
        const input = this.$refs.input;

        input.style.height = 'auto';
        input.style.height = input.scrollHeight + 'px';
    },

    format(notify)
    {
        const input = this.$refs.input;
        const value = input.value.trim();

        if (value === '' && ! notify)
        {
            this.$nextTick(() => this.resize());

            return;
        }

        try
        {
            input.value  = JSON.stringify(JSON.parse(value === '' ? '{}' : value), null, 2);
            this.invalid = false;
        }
        catch (error)
        {
            this.invalid = notify;

            if (notify)
            {
                Alpine.store('toast').show('Hatalı JSON, düzenlenemiyor.', 'danger');
            }
        }

        this.$nextTick(() => this.resize());
    },

    indent()
    {
        const input = this.$refs.input;
        const start = input.selectionStart;

        input.setRangeText('  ', start, input.selectionEnd, 'end');
        this.resize();
    },
}));

Alpine.data('editorField', (config = {}) => (
{
    init()
    {
        this.start();
    },

    async start()
    {
        const [module, translations] = await Promise.all([
            import('ckeditor5'),
            import('ckeditor5/translations/tr.js'),
            import('ckeditor5/ckeditor5.css'),
        ]);

        const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        const editor = await module.ClassicEditor.create(this.$refs.editor,
        {
            licenseKey:   'GPL',
            language:     'tr',
            translations: [translations.default],
            initialData:  this.$refs.source.value,

            plugins: [
                module.Alignment,
                module.Autoformat,
                module.BlockQuote,
                module.Bold,
                module.Essentials,
                module.Heading,
                module.HorizontalLine,
                module.Image,
                module.ImageCaption,
                module.ImageResize,
                module.ImageStyle,
                module.ImageToolbar,
                module.ImageUpload,
                module.Indent,
                module.Italic,
                module.Link,
                module.List,
                module.Paragraph,
                module.PasteFromOffice,
                module.RemoveFormat,
                module.SimpleUploadAdapter,
                module.SourceEditing,
                module.Table,
                module.TableToolbar,
                module.Underline,
            ],

            toolbar: [
                'heading',
                '|', 'bold', 'italic', 'underline', 'link',
                '|', 'alignment',
                '|', 'bulletedList', 'numberedList', 'blockQuote',
                '|', 'uploadImage', 'insertTable', 'horizontalLine',
                '|', 'removeFormat', 'undo', 'redo',
                '|', 'sourceEditing',
            ],

            image:
            {
                toolbar: ['imageTextAlternative', 'toggleImageCaption', '|', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'],
            },

            table:
            {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
            },

            simpleUpload:
            {
                uploadUrl:       config.upload,
                withCredentials: true,

                headers:
                {
                    'X-CSRF-TOKEN':     token,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept:             'application/json',
                },
            },
        });

        this.$el.editorInstance = editor;

        editor.model.document.on('change:data', () =>
        {
            this.$refs.source.value = editor.getData();
        });
    },

    destroy()
    {
        this.$el.editorInstance?.destroy();

        this.$el.editorInstance = null;
    },
}));

Alpine.data('mediaPreview', () => (
{
    items: [],
    index: 0,

    get current()
    {
        return this.items[this.index] || null;
    },

    show(trigger)
    {
        const scope = trigger.closest('[data-media-group]') || document;
        const cards = [...scope.querySelectorAll('[data-media-file]')];

        this.items = cards.map(card => JSON.parse(card.dataset.mediaFile));
        this.index = Math.max(0, cards.indexOf(trigger.closest('[data-media-file]')));

        this.$dispatch('modal-open', 'media-preview');
    },

    move(step)
    {
        if (this.items.length < 2)
        {
            return;
        }

        this.index = (this.index + step + this.items.length) % this.items.length;
    },
}));

Alpine.data('relationshipForm', (config = {}) => (
{
    relation: config.relation || '',

    init()
    {
        if (config.table)
        {
            this.columns(config.table, config.picked || {});
        }
    },

    pick(event)
    {
        if (event.target.name === 'relationship')
        {
            this.relation = event.target.value;
        }

        if (event.target.name === 'relationship_table_name')
        {
            this.columns(event.target.value);
        }
    },

    async columns(table, picked = {})
    {
        const targets = ['show_column', 'match_column'].map(name => this.$root.querySelector(`[data-select="${name}"]`)).filter(Boolean);

        let items = [];

        if (table)
        {
            try
            {
                const payload = await postJson(config.url, { table_name: table });

                items = (payload.response || []).map(column => ({ value: column.name, label: column.name }));
            }
            catch (error)
            {
                Alpine.store('toast').show('Tablo alanları alınamadı.', 'danger');
            }
        }

        targets.forEach(target =>
        {
            target.dispatchEvent(new CustomEvent('options-set', { detail: items }));

            if (picked[target.dataset.select])
            {
                target.dispatchEvent(new CustomEvent('option-pick', { detail: picked[target.dataset.select] }));
            }
        });
    },
}));

Alpine.data('formDrawer', () => (
{
    open: false,
    loading: false,
    saving: false,
    title: '',
    html: '',
    url: '',

    async load(url, title)
    {
        this.url = url;
        this.title = title || '';
        this.html = '';
        this.loading = true;
        this.open = true;

        try
        {
            const response = await fetch(url,
            {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (! response.ok)
            {
                throw new Error(response.status);
            }

            this.html = await response.text();
        }
        catch (error)
        {
            this.html = '<div class="rounded-alert bg-tint-danger text-danger px-5 py-4 text-sm">İçerik yüklenemedi. Sayfayı yenileyip tekrar deneyin.</div>';
        }

        this.loading = false;

        this.$nextTick(() => Alpine.initTree(this.$refs.body));
    },

    async submit(event)
    {
        const form = event.target;

        if (! form.matches('form'))
        {
            return;
        }

        event.preventDefault();

        this.clearErrors(form);

        const body = new FormData(form);

        this.setSaving(form, true);

        try
        {
            const response = await fetch(form.action,
            {
                method: 'POST',
                headers:
                {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: body,
            });

            const payload = await response.json().catch(() => ({}));

            if (response.status === 422)
            {
                this.showErrors(form, payload.errors || {});
                this.setSaving(form, false);

                return;
            }

            if (! response.ok)
            {
                this.setSaving(form, false);

                Alpine.store('dialog').notify(
                    'İşlem tamamlanamadı',
                    payload.message || 'Sunucu ' + response.status + ' hatası döndürdü.',
                    'danger',
                );

                return;
            }

            if (payload.error && ! payload.reload)
            {
                this.setSaving(form, false);

                Alpine.store('dialog').notify('İşlem tamamlanamadı', payload.error, 'danger');

                return;
            }

            this.setSaving(form, false);
            this.close();

            const target = payload.url ? new URL(payload.url, window.location.origin) : null;

            if (target && target.pathname !== window.location.pathname)
            {
                window.location.assign(target.href);

                return;
            }

            if (payload.reload)
            {
                window.location.reload();

                return;
            }

            if (payload.status)
            {
                Alpine.store('dialog').notify(payload.status);
            }

            await refreshContent();
        }
        catch (error)
        {
            this.setSaving(form, false);

            Alpine.store('dialog').notify('İşlem tamamlanamadı', 'Sunucuya ulaşılamadı, lütfen tekrar deneyin.', 'danger');
        }
    },

    setSaving(form, state)
    {
        this.saving = state;

        form.classList.toggle('is-saving', state);
        form.querySelectorAll('input, select, textarea').forEach(field => field.disabled = state);

        buttonLoading(form.querySelector('button[type="submit"]'), state);
    },

    clearErrors(form)
    {
        clearErrors(form);
    },

    showErrors(form, errors)
    {
        showErrors(form, errors);
    },

    requestClose()
    {
        if (! this.open || this.saving)
        {
            return false;
        }

        Alpine.store('dialog').ask(
        {
            title:        'Vazgeçilsin mi?',
            message:      'Girdiğiniz bilgiler kaydedilmeyecek.',
            tone:         'warning',
            confirmLabel: 'Evet, vazgeç',
            action:       () => this.close(),
        });

        return false;
    },

    close()
    {
        this.open = false;
    },
}));

let refreshCounter = 0;

async function refreshContent()
{
    const active = target =>
    {
        const drawer = target.closest('[data-form-drawer]');

        return ! drawer || Alpine.$data(drawer).open;
    };

    const blocks  = [...document.querySelectorAll('[data-refresh]')].filter(active);
    const targets = blocks.length ? blocks : [...document.querySelectorAll('[data-content]')];

    if (! targets.length)
    {
        window.location.reload();

        return;
    }

    const selector = target => target.dataset.refresh === undefined ? '[data-content]' : '[data-refresh="' + target.dataset.refresh + '"]';
    const token    = ++refreshCounter;
    const sources = new Map();

    targets.forEach(target =>
    {
        const source = target.closest('[data-refresh-source]')?.dataset.refreshSource || window.location.href;

        sources.set(source, [...(sources.get(source) || []), target]);
    });

    tableBusy(true);

    try
    {
        const markups = await Promise.all([...sources.keys()].map(source =>
        {
            const headers = source === window.location.href
                ? { 'X-Panel-Refresh': '1' }
                : { 'X-Panel-Refresh': '1', 'X-Requested-With': 'XMLHttpRequest' };

            return fetch(source, { headers }).then(response => response.text());
        }));

        if (token !== refreshCounter)
        {
            return;
        }

        [...sources.values()].forEach((group, position) =>
        {
            const buffer = document.createElement('div');

            buffer.innerHTML = markups[position];

            group.forEach(current =>
            {
                const match = selector(current);
                const index = group.filter(target => selector(target) === match).indexOf(current);
                const fresh = buffer.querySelectorAll(match)[index];

                if (! fresh)
                {
                    return;
                }

                if (current.matches('[data-content]'))
                {
                    current.replaceChildren(...fresh.cloneNode(true).childNodes);

                    Alpine.initTree(current);

                    return;
                }

                const replacement = fresh.cloneNode(true);

                current.replaceWith(replacement);

                Alpine.initTree(replacement);
            });
        });
    }
    finally
    {
        if (token === refreshCounter)
        {
            tableBusy(false);
        }
    }

    window.dispatchEvent(new CustomEvent('content-refreshed'));
}

function tableBusy(state)
{
    document.querySelectorAll('[data-datatable]').forEach(table =>
    {
        table.querySelector(':scope > [data-table-loader]')?.remove();

        if (! state)
        {
            table.removeAttribute('aria-busy');

            return;
        }

        const rows   = [...table.querySelectorAll('[data-refresh="rows"] > *')].find(row => row.getClientRects().length);
        const loader = document.createElement('div');
        const mark   = document.createElement('span');

        loader.setAttribute('data-table-loader', '');
        loader.style.top = (rows ? rows.getBoundingClientRect().top - table.getBoundingClientRect().top : 0) + 'px';

        mark.append(loaderMark(44));
        loader.append(mark);

        table.setAttribute('aria-busy', 'true');
        table.append(loader);
    });
}

window.tableBusy = tableBusy;

window.refreshContent = refreshContent;

function applyParams(params)
{
    const url = new URL(window.location.href);

    Object.entries(params).forEach(([key, value]) =>
    {
        if (value === null || value === '')
        {
            url.searchParams.delete(key);

            return;
        }

        url.searchParams.set(key, value);
    });

    url.searchParams.delete('page');

    if (url.toString() === window.location.href)
    {
        return Promise.resolve();
    }

    window.history.replaceState({}, '', url);

    return refreshContent();
}

function filterContent(name, value)
{
    const term = value.trim();

    return applyParams({ [name]: term.length >= 2 ? term : null });
}

function clearFilters(names)
{
    document.querySelectorAll('[data-datatable] input[type="search"]').forEach(input =>
    {
        if (names.includes(input.name))
        {
            input.value = '';
        }
    });

    names.forEach(name =>
    {
        document.querySelectorAll('[data-datatable] [data-select="filter-' + name + '"]').forEach(select =>
        {
            const state = Alpine.$data(select);

            state.value = state.multiple ? [] : '';
        });
    });

    return applyParams(Object.fromEntries(names.map(name => [name, null])));
}

window.applyParams = applyParams;
window.filterContent = filterContent;
window.clearFilters = clearFilters;

function initSorting(root, url, model)
{
    root.querySelectorAll('[data-row]').forEach(row =>
    {
        const handle = row.querySelector('[data-drag]');

        if (! handle || handle.dataset.bound)
        {
            return;
        }

        handle.dataset.bound = '1';

        handle.addEventListener('pointerdown', event =>
        {
            if (event.button > 0)
            {
                return;
            }

            event.preventDefault();

            const order = () => [...root.querySelectorAll('[data-row]')].map(item => item.dataset.row).join();
            const before = order();

            const scroller = root.closest('[data-content]');

            let startY = event.clientY;
            let pointerY = event.clientY;
            let frame = null;

            row.style.opacity = '0.6';
            row.style.position = 'relative';
            row.style.zIndex = '20';
            row.style.pointerEvents = 'none';

            const move = pointer =>
            {
                pointerY = pointer.clientY;

                row.style.transform = `translateY(${pointer.clientY - startY}px)`;

                const target = [...root.querySelectorAll('[data-row]')].find(other =>
                {
                    if (other === row)
                    {
                        return false;
                    }

                    const bounds = other.getBoundingClientRect();

                    return pointer.clientY >= bounds.top && pointer.clientY <= bounds.bottom;
                });

                if (! target)
                {
                    return;
                }

                const rect = target.getBoundingClientRect();
                const above = pointer.clientY < rect.top + rect.height / 2;
                const from = row.getBoundingClientRect().top;

                target.parentNode.insertBefore(row, above ? target : target.nextSibling);

                startY += row.getBoundingClientRect().top - from;

                row.style.transform = `translateY(${pointer.clientY - startY}px)`;
            };

            const scroll = () =>
            {
                const bounds = scroller.getBoundingClientRect();
                const step = pointerY < bounds.top + 80 ? -14 : (pointerY > bounds.bottom - 80 ? 14 : 0);
                const from = scroller.scrollTop;

                if (step)
                {
                    scroller.scrollTop += step;
                }

                if (scroller.scrollTop !== from)
                {
                    startY -= scroller.scrollTop - from;

                    move({ clientY: pointerY });
                }

                frame = requestAnimationFrame(scroll);
            };

            const end = async () =>
            {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', end);
                window.removeEventListener('pointercancel', end);

                cancelAnimationFrame(frame);

                row.style.opacity = '';
                row.style.position = '';
                row.style.zIndex = '';
                row.style.pointerEvents = '';
                row.style.transform = '';

                if (order() === before || ! url)
                {
                    return;
                }

                await submitSorting(root, url, model);
            };

            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', end);
            window.addEventListener('pointercancel', end);

            if (scroller)
            {
                frame = requestAnimationFrame(scroll);
            }
        });
    });
}

async function submitSorting(root, url, model)
{
    const order = [...root.querySelectorAll('[data-row]')].map(row => row.dataset.row);

    try
    {
        const response = await fetch(url,
        {
            method: 'POST',
            headers:
            {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: JSON.stringify({ model, order }),
        });

        const payload = await response.json().catch(() => ({}));

        if (! response.ok)
        {
            Alpine.store('toast').show(payload.message || 'Sıralama kaydedilemedi.', 'danger');

            return;
        }

        if (payload.status)
        {
            Alpine.store('toast').show(payload.status, 'success');
        }

        await refreshContent();
    }
    catch (error)
    {
        Alpine.store('toast').show('Sıralama kaydedilemedi. Sunucuya ulaşılamadı.', 'danger');
    }
}

window.initSorting = initSorting;

function initMenuTree(root, url)
{
    const structure = () => [...root.querySelectorAll(':scope > [data-node]')].map(node => (
    {
        id:       Number(node.dataset.node),
        children: [...node.querySelectorAll(':scope > [data-children] > [data-node]')].map(child => Number(child.dataset.node)),
    }));

    root.querySelectorAll('[data-drag]').forEach(handle =>
    {
        if (handle.dataset.bound)
        {
            return;
        }

        handle.dataset.bound = '1';

        handle.addEventListener('pointerdown', event =>
        {
            if (event.button > 0)
            {
                return;
            }

            event.preventDefault();

            const node     = handle.closest('[data-node]');
            const origin   = { parent: node.parentNode, next: node.nextSibling };
            const before   = JSON.stringify(structure());
            const scroller = root.closest('[data-content]');
            const startX   = event.clientX;

            let startY = event.clientY;
            let pointerX = event.clientX;
            let pointerY = event.clientY;
            let frame = null;

            node.style.opacity = '0.6';
            node.style.position = 'relative';
            node.style.zIndex = '20';
            node.style.pointerEvents = 'none';

            const place = pointer =>
            {
                const row = [...root.querySelectorAll('[data-node-row]')].find(other =>
                {
                    if (node.contains(other))
                    {
                        return false;
                    }

                    const bounds = other.getBoundingClientRect();

                    return pointer.clientY >= bounds.top && pointer.clientY <= bounds.bottom;
                });

                if (! row)
                {
                    return;
                }

                const target   = row.closest('[data-node]');
                const nested   = target.parentElement.matches('[data-children]');
                const parent   = nested ? target.parentElement.closest('[data-node]') : null;
                const branch   = node.querySelector(':scope > [data-children] > [data-node]') !== null;
                const rect     = row.getBoundingClientRect();
                const above    = pointer.clientY < rect.top + rect.height / 2;
                const shift    = pointer.clientX - startX;
                const children = target.querySelector(':scope > [data-children]');

                if (nested && branch)
                {
                    parent.parentNode.insertBefore(node, above ? parent : parent.nextSibling);
                }
                else if (nested && ! above && shift < -40 && target.nextElementSibling === null)
                {
                    parent.parentNode.insertBefore(node, parent.nextSibling);
                }
                else if (nested)
                {
                    target.parentNode.insertBefore(node, above ? target : target.nextSibling);
                }
                else if (above)
                {
                    root.insertBefore(node, target);
                }
                else if (! branch && (shift > 40 || children.children.length > 0))
                {
                    children.insertBefore(node, children.firstChild);
                }
                else
                {
                    root.insertBefore(node, target.nextSibling);
                }

                node.querySelector(':scope > [data-children]') || node.insertAdjacentHTML('beforeend', '<div data-children></div>');
            };

            const move = pointer =>
            {
                pointerX = pointer.clientX;
                pointerY = pointer.clientY;

                node.style.transform = `translateY(${pointer.clientY - startY}px)`;

                const from = node.getBoundingClientRect().top;

                place(pointer);

                startY += node.getBoundingClientRect().top - from;

                node.style.transform = `translateY(${pointer.clientY - startY}px)`;
            };

            const scroll = () =>
            {
                const bounds = scroller.getBoundingClientRect();
                const step = pointerY < bounds.top + 80 ? -14 : (pointerY > bounds.bottom - 80 ? 14 : 0);
                const from = scroller.scrollTop;

                if (step)
                {
                    scroller.scrollTop += step;
                }

                if (scroller.scrollTop !== from)
                {
                    startY -= scroller.scrollTop - from;

                    move({ clientX: pointerX, clientY: pointerY });
                }

                frame = requestAnimationFrame(scroll);
            };

            const end = async () =>
            {
                window.removeEventListener('pointermove', move);
                window.removeEventListener('pointerup', end);
                window.removeEventListener('pointercancel', end);

                cancelAnimationFrame(frame);

                node.style.opacity = '';
                node.style.position = '';
                node.style.zIndex = '';
                node.style.pointerEvents = '';
                node.style.transform = '';

                if (JSON.stringify(structure()) === before)
                {
                    return;
                }

                if (! await submitTree(url, structure()))
                {
                    origin.parent.insertBefore(node, origin.next);
                }
            };

            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', end);
            window.addEventListener('pointercancel', end);

            if (scroller)
            {
                frame = requestAnimationFrame(scroll);
            }
        });
    });
}

async function submitTree(url, tree)
{
    try
    {
        const payload = await postJson(url, { tree });

        if (payload.reload)
        {
            window.location.reload();

            return true;
        }

        Alpine.store('toast').show(payload.status, 'success');
    }
    catch (error)
    {
        Alpine.store('dialog').notify('Sıralama kaydedilemedi', error.data?.message || 'Öğe eski yerine alındı, lütfen tekrar deneyin.', 'danger');

        return false;
    }

    return true;
}

window.initMenuTree = initMenuTree;

const SHEET_EASE      = 'cubic-bezier(0.32, 0.72, 0, 1)';
const SHEET_DURATION  = 320;
const SHEET_THRESHOLD = 0.35;
const SHEET_FLICK     = 0.6;

function settled(element)
{
    element.offsetHeight;

    return Promise.all(element.getAnimations().map(animation => animation.finished)).catch(() => {});
}

function scrollerFor(target, panel)
{
    for (let node = target; node && node !== panel.parentElement; node = node.parentElement)
    {
        const overflow = getComputedStyle(node).overflowY;

        if ((overflow === 'auto' || overflow === 'scroll') && node.scrollHeight > node.clientHeight)
        {
            return node;
        }
    }

    return null;
}

function initSheetDrag(panel, dismiss)
{
    let gesture = null;

    const reset = () =>
    {
        panel.style.transition = '';
        panel.style.transform  = '';
    };

    const snapBack = async () =>
    {
        panel.style.transition = `transform ${SHEET_DURATION}ms ${SHEET_EASE}`;
        panel.style.transform  = '';

        await settled(panel);

        panel.style.transition = '';
    };

    const slideOut = async current =>
    {
        const height    = panel.offsetHeight;
        const remaining = height - current.offset;
        const duration  = Math.round(Math.min(SHEET_DURATION, Math.max(160, remaining / Math.max(current.velocity, 1.2))));

        panel.style.transition = `transform ${duration}ms ${SHEET_EASE}`;
        panel.style.transform  = 'translateY(100%)';

        await settled(panel);

        if (dismiss() === false)
        {
            snapBack();
        }
    };

    const start = event =>
    {
        if (window.innerWidth >= 1024 || event.touches.length !== 1)
        {
            gesture = null;

            return;
        }

        const touch = event.touches[0];

        gesture = {
            x        : touch.clientX,
            y        : touch.clientY,
            lastY    : touch.clientY,
            lastTime : event.timeStamp,
            velocity : 0,
            offset   : 0,
            mode     : 'pending',
            scroller : scrollerFor(event.target, panel),
        };
    };

    const move = event =>
    {
        if (! gesture || event.touches.length !== 1)
        {
            return;
        }

        const touch = event.touches[0];
        const dx    = touch.clientX - gesture.x;
        const dy    = touch.clientY - gesture.y;

        if (gesture.mode === 'pending')
        {
            if (dx === 0 && dy === 0)
            {
                return;
            }

            const scrolling = gesture.scroller !== null && (gesture.scroller.scrollTop > 0 || dy <= 0);

            gesture.mode = ! scrolling && dy > Math.abs(dx) ? 'drag' : 'scroll';

            if (gesture.mode === 'drag')
            {
                panel.style.transition = 'none';
            }
        }

        if (gesture.mode !== 'drag')
        {
            return;
        }

        if (! event.cancelable)
        {
            gesture = null;
            snapBack();

            return;
        }

        event.preventDefault();

        const elapsed = Math.max(1, event.timeStamp - gesture.lastTime);

        gesture.velocity = (touch.clientY - gesture.lastY) / elapsed;
        gesture.lastY    = touch.clientY;
        gesture.lastTime = event.timeStamp;
        gesture.offset   = Math.max(0, dy);

        panel.style.transform = `translateY(${gesture.offset}px)`;
    };

    const end = () =>
    {
        const current = gesture;

        gesture = null;

        if (! current || current.mode !== 'drag')
        {
            return;
        }

        const flicked = current.velocity > SHEET_FLICK && current.offset > 20;

        if (flicked || current.offset > panel.offsetHeight * SHEET_THRESHOLD)
        {
            slideOut(current);

            return;
        }

        snapBack();
    };

    panel.addEventListener('touchstart', start, { passive: true });
    panel.addEventListener('touchmove', move, { passive: false });
    panel.addEventListener('touchend', end);
    panel.addEventListener('touchcancel', end);

    new MutationObserver(() =>
    {
        if (panel.style.display === 'none' && panel.style.transform !== '')
        {
            reset();
        }
    }).observe(panel, { attributes: true, attributeFilter: ['style'] });
}

window.initSheetDrag = initSheetDrag;

async function submitForm(form, extra = {})
{
    const trigger = form.querySelector('button[type="submit"]') || form.querySelector('button');

    buttonLoading(trigger, true);
    clearErrors(form);

    try
    {
        const body = new FormData(form);

        Object.entries(extra).forEach(([field, value]) => body.append(field, value));

        const response = await fetch(form.action,
        {
            method: 'POST',
            headers:
            {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body,
        });

        const payload = await response.json();

        if (response.status === 422)
        {
            showErrors(form, payload.errors || {});

            return;
        }

        if (! response.ok)
        {
            Alpine.store('dialog').notify('İşlem tamamlanamadı', payload.message || 'Bilinmeyen bir hata oluştu.', 'danger');

            return;
        }

        if (payload.error && ! payload.reload)
        {
            Alpine.store('dialog').notify('İşlem tamamlanamadı', payload.error, 'danger');

            return;
        }

        if (payload.reload)
        {
            window.location.assign(payload.url || window.location.href);

            return;
        }

        if (payload.status)
        {
            Alpine.store('dialog').notify(payload.status);
        }

        if (payload.redirect)
        {
            window.location.assign(payload.redirect);

            return;
        }

        await refreshContent();
    }
    catch (error)
    {
        Alpine.store('dialog').notify('İşlem tamamlanamadı', 'Sunucuya ulaşılamadı, lütfen tekrar deneyin.', 'danger');
    }
    finally
    {
        buttonLoading(trigger, false);
    }
}

window.submitForm = submitForm;

function clearErrors(scope)
{
    scope.querySelectorAll('[data-field-error]').forEach(node => node.remove());
    scope.querySelectorAll('[data-field-invalid]').forEach(node =>
    {
        node.classList.remove('!border-danger', '!bg-tint-danger');
        node.removeAttribute('data-field-invalid');
    });
}

function bracketed(field)
{
    const parts = field.split('.');

    return parts.length === 1 ? field : parts[0] + parts.slice(1).map(part => '[' + part + ']').join('');
}

function markField(input, message)
{
    const wrapper = input.closest('[data-field]');
    const box = input.closest('[data-box]') || wrapper?.querySelector('[data-box]') || input.closest('div');

    if (box)
    {
        box.classList.add('!border-danger', '!bg-tint-danger');
        box.setAttribute('data-field-invalid', '');
    }

    const node = document.createElement('span');

    node.className = 'text-xs text-danger';
    node.textContent = message;
    node.setAttribute('data-field-error', '');

    (wrapper || box || input.parentNode).appendChild(node);
}

function clearField(input)
{
    const wrapper = input.closest('[data-field]') || input.parentNode;

    clearErrors(wrapper);

    const box = input.closest('[data-box]') || input.closest('div');

    if (box)
    {
        box.classList.remove('!border-danger', '!bg-tint-danger');
        box.removeAttribute('data-field-invalid');
    }
}

function limitField(input)
{
    clearField(input);

    const value = input.value.trim().replace(/\s/g, '');

    if (value === '')
    {
        return;
    }

    const min = input.dataset.min === undefined ? null : Number(input.dataset.min);
    const max = input.dataset.max === undefined ? null : Number(input.dataset.max);

    const amount = Number(value);

    const broken = Number.isNaN(amount)
        || (min !== null && amount < min)
        || (max !== null && amount > max);

    if (broken)
    {
        markField(input, input.dataset.limit);
    }
}

function showErrors(scope, errors)
{
    Object.entries(errors).forEach(([field, messages]) =>
    {
        const name = bracketed(field);

        const input = scope.querySelector('[name="' + field + '"], [name="' + field + '[]"], [name="' + name + '"]');

        if (! input)
        {
            return;
        }

        markField(input, messages[0]);
    });

    const invalid = scope.querySelector('[data-field-invalid]');

    if (! invalid)
    {
        return;
    }

    invalid.dispatchEvent(new CustomEvent('field-invalid', { bubbles: true }));

    requestAnimationFrame(() => invalid.scrollIntoView({ behavior: 'smooth', block: 'center' }));
}

window.clearErrors = clearErrors;
window.showErrors = showErrors;
window.markField = markField;
window.clearField = clearField;
window.limitField = limitField;

function removeRow(trigger, selector = '[data-import-row]', bag = '[data-removed]')
{
    const row = trigger.closest(selector);

    if (! row)
    {
        return;
    }

    const holder = bag ? document.querySelector(bag) : null;

    if (holder)
    {
        const ids = holder.value ? holder.value.split(',') : [];

        ids.push(row.dataset.importRow);

        holder.value = ids.join();
    }

    row.remove();

    const remaining = document.querySelectorAll(selector).length;

    document.querySelectorAll('[data-row-count]').forEach(counter => counter.textContent = remaining);
}

window.removeRow = removeRow;

function addRow(trigger, container = '[data-rows]')
{
    const holder = trigger.closest('[data-row-group]')?.querySelector(container) || document.querySelector(container);
    const source = holder?.parentElement.querySelector('template[data-row-template]');

    if (! holder || ! source)
    {
        return;
    }

    const index = Number(holder.dataset.rowIndex || holder.children.length);
    const markup = source.innerHTML.replaceAll('__index__', index);
    const buffer = document.createElement('div');

    buffer.innerHTML = markup;

    const row = buffer.firstElementChild;

    holder.append(row);
    holder.dataset.rowIndex = index + 1;

    if (holder.hasAttribute('data-sortable'))
    {
        initSorting(holder);
    }

    row.querySelector('input, textarea')?.focus();
}

window.addRow = addRow;

function deleteRequest(url, method = 'DELETE')
{
    const form  = document.createElement('form');
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    form.action = url;
    form.method = 'POST';
    form.hidden = true;
    form.innerHTML = '<input type="hidden" name="_token"><input type="hidden" name="_method">';
    form.elements._token.value = token;
    form.elements._method.value = method;

    document.body.append(form);

    return window.submitForm(form).finally(() => form.remove());
}

window.deleteRequest = deleteRequest;

function slugify(text)
{
    return fold(String(text ?? ''))
        .trim()
        .replace(/&/g, '-ve-')
        .replace(/\s+/g, '-')
        .replace(/[^\w-]+/g, '')
        .replace(/-{2,}/g, '-');
}

window.slugify = slugify;

function buttonLoading(button, state)
{
    if (! button)
    {
        return;
    }

    if (state)
    {
        if (button.dataset.loading !== undefined)
        {
            return;
        }

        const rect = button.getBoundingClientRect();
        const mark = loaderMark(Math.max(rect.height - 8, 20));

        button.dataset.loading = '';
        button.dataset.label   = button.innerHTML;

        button.style.setProperty('flex', '0 0 auto', 'important');
        button.style.setProperty('width', rect.width + 'px', 'important');
        button.style.setProperty('min-width', rect.width + 'px', 'important');
        button.style.height = rect.height + 'px';
        button.style.setProperty('background', 'transparent', 'important');
        button.style.setProperty('border', 'none', 'important');
        button.style.setProperty('box-shadow', 'none', 'important');
        button.style.pointerEvents = 'none';

        button.replaceChildren(mark);

        return;
    }

    if (button.dataset.loading === undefined)
    {
        return;
    }

    button.innerHTML = button.dataset.label ?? '';

    button.style.removeProperty('flex');
    button.style.removeProperty('width');
    button.style.removeProperty('min-width');
    button.style.removeProperty('height');
    button.style.removeProperty('background');
    button.style.removeProperty('border');
    button.style.removeProperty('box-shadow');
    button.style.removeProperty('pointer-events');

    delete button.dataset.label;
    delete button.dataset.loading;
}

window.buttonLoading = buttonLoading;

function loaderMark(size)
{
    const mark = document.querySelector('template[data-loader-template]').content.firstElementChild.cloneNode(true);
    const suffix = Math.random().toString(36).slice(2, 10);

    mark.querySelectorAll('clipPath').forEach(clip =>
    {
        const previous = clip.id;

        clip.id = previous.replace(/[^-]+$/, suffix);

        mark.querySelector('[clip-path="url(#' + previous + ')"]').setAttribute('clip-path', 'url(#' + clip.id + ')');
    });

    mark.setAttribute('data-button-loader', '');
    mark.style.height = size + 'px';
    mark.style.width = mark.matches('svg') ? size + 'px' : 'auto';

    return mark;
}

const preloadedLoaders = new Map();

function preloadLoader(source)
{
    if (! source || preloadedLoaders.has(source))
    {
        return;
    }

    preloadedLoaders.set(source, source);

    fetch(source)
        .then(response => response.ok ? response.blob() : null)
        .then(blob =>
        {
            if (blob)
            {
                preloadedLoaders.set(source, URL.createObjectURL(blob));
            }
        })
        .catch(() => {});
}

function loaderSource(source)
{
    preloadLoader(source);

    return preloadedLoaders.get(source) ?? source;
}

function loaderImages(root)
{
    const found = [...root.querySelectorAll('[data-loader-image]')];

    root.querySelectorAll('template').forEach(template => found.push(...loaderImages(template.content)));

    return found;
}

function preloadLoaders()
{
    document.querySelectorAll('link[rel="preload"][as="image"]').forEach(link => preloadLoader(link.href));

    loaderImages(document).forEach(image => preloadLoader(image.dataset.loaderImage));
}

window.loaderSource = loaderSource;

if (document.readyState === 'loading')
{
    document.addEventListener('DOMContentLoaded', preloadLoaders);
}
else
{
    preloadLoaders();
}

document.addEventListener('change', async event =>
{
    const field = event.target;
    const form  = field.closest?.('form');

    if (! form || ! field.name)
    {
        return;
    }

    const name     = field.name.replace(/\[\]$/, '');
    const children = form.querySelectorAll(`[data-depends-field="${CSS.escape(name)}"]`);

    for (const child of children)
    {
        let items = [];

        if (field.value)
        {
            const url = new URL(child.dataset.optionsUrl, window.location.origin);

            url.searchParams.set('parent', field.value);

            try
            {
                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const payload  = await response.json();

                items = (payload.options || []).map(option => ({ value: String(option.value), label: String(option.text) }));
            }
            catch (error)
            {
                Alpine.store('toast').show('Seçenekler alınamadı.', 'danger');
            }
        }

        child.dispatchEvent(new CustomEvent('options-set', { detail: items }));
    }
});

document.addEventListener('submit', event =>
{
    const form = event.target;

    if (event.defaultPrevented || form.dataset.noLoader !== undefined)
    {
        return;
    }

    buttonLoading(form.querySelector('button[type="submit"]'), true);
});

document.addEventListener('click', async event =>
{
    const trigger = event.target.closest('[data-refresh-trigger]');

    if (! trigger || event.defaultPrevented)
    {
        return;
    }

    event.preventDefault();

    buttonLoading(trigger, true);

    try
    {
        await refreshContent();
    }
    finally
    {
        buttonLoading(trigger, false);
    }
});

document.addEventListener('click', async event =>
{
    const link = event.target.closest('[data-pagination] a[href]');

    if (! link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || link.getAttribute('href') === '#')
    {
        return;
    }

    event.preventDefault();

    window.history.pushState({}, '', link.href);

    await refreshContent();

    const table   = document.querySelector('[data-datatable]');
    const content = document.querySelector('[data-content]');

    if (table && content && table.getBoundingClientRect().top < content.getBoundingClientRect().top)
    {
        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

window.addEventListener('popstate', () =>
{
    if (document.querySelector('[data-datatable]'))
    {
        refreshContent();
    }
});

window.addEventListener('pageshow', () =>
{
    document.querySelectorAll('button[data-loading]').forEach(button => buttonLoading(button, false));

    tableBusy(false);
});

function attachmentName(header)
{
    const encoded = /filename\*=UTF-8''([^;]+)/i.exec(header || '');

    if (encoded)
    {
        return decodeURIComponent(encoded[1]);
    }

    const plain = /filename="?([^";]+)"?/i.exec(header || '');

    return plain ? plain[1] : null;
}

async function downloadFile(link)
{
    const root    = link.closest('[x-data]');
    const trigger = root?.querySelector('button');
    const state   = root ? Alpine.$data(root) : null;

    if (state && state.open !== undefined)
    {
        state.open = false;
    }

    buttonLoading(trigger, true);

    try
    {
        const response = await fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        if (! response.ok)
        {
            throw new Error(response.status);
        }

        const blob   = await response.blob();
        const name   = attachmentName(response.headers.get('content-disposition')) || 'dosya';
        const source = URL.createObjectURL(blob);
        const anchor = document.createElement('a');

        anchor.href     = source;
        anchor.download = name;

        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();

        URL.revokeObjectURL(source);

        Alpine.store('toast').show(name + ' indirildi');
    }
    catch (error)
    {
        Alpine.store('dialog').notify('Dosya indirilemedi', 'Dışa aktarma tamamlanamadı, lütfen tekrar deneyin.', 'danger');
    }
    finally
    {
        buttonLoading(trigger, false);
    }
}

document.addEventListener('click', event =>
{
    const link = event.target.closest('a[data-download]');

    if (! link || event.defaultPrevented)
    {
        return;
    }

    event.preventDefault();

    downloadFile(link);
});

function revealMenuCurrent(scope = document)
{
    scope.querySelectorAll('[data-menu-scroll]').forEach(scroller =>
    {
        const current = scroller.querySelector('[data-menu-current]');

        if (! current || scroller.scrollHeight <= scroller.clientHeight)
        {
            return;
        }

        const scrollerBox = scroller.getBoundingClientRect();
        const currentBox  = current.getBoundingClientRect();

        const delta = (currentBox.top - scrollerBox.top) - (scroller.clientHeight - currentBox.height) / 2;

        scroller.scrollTop = Math.max(0, scroller.scrollTop + delta);
    });
}

window.revealMenuCurrent = revealMenuCurrent;

function placeDropdown(root)
{
    const panel = root.querySelector('[data-dropdown-panel]');

    if (! panel)
    {
        return;
    }

    panel.style.translate = '';

    const box    = panel.getBoundingClientRect();
    const margin = 12;

    if (box.width === 0)
    {
        return;
    }

    if (box.left < margin)
    {
        panel.style.translate = Math.round(margin - box.left) + 'px 0';
    }
    else if (box.right > window.innerWidth - margin)
    {
        panel.style.translate = Math.round(window.innerWidth - margin - box.right) + 'px 0';
    }
}

window.placeDropdown = placeDropdown;

const fieldGrids = new ResizeObserver(entries => entries.forEach(entry => widenLoneFields(entry.target)));

function widenLoneFields(grid)
{
    grid.querySelectorAll(':scope > [data-widened]').forEach(cell =>
    {
        cell.style.gridColumn = '';
        cell.removeAttribute('data-widened');
    });

    if (getComputedStyle(grid).gridTemplateColumns.split(' ').length < 2)
    {
        return;
    }

    const visible = [...grid.children].filter(cell => cell.offsetParent !== null);

    const lone = visible.filter(cell =>
        cell.matches('[data-form-cell]')
        && cell.offsetWidth < grid.clientWidth - 1
        && visible.every(other => other === cell || other.offsetTop !== cell.offsetTop));

    lone.forEach(cell =>
    {
        cell.style.gridColumn = '1 / -1';
        cell.setAttribute('data-widened', '');
    });
}

function watchFieldGrids(root)
{
    root.querySelectorAll('[data-form-cell]').forEach(cell =>
    {
        const grid = cell.parentElement;

        if (grid?.classList.contains('grid') && ! grid.hasAttribute('data-field-grid'))
        {
            grid.setAttribute('data-field-grid', '');
            fieldGrids.observe(grid);
        }
    });
}

new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(node =>
{
    if (node.nodeType === Node.ELEMENT_NODE)
    {
        watchFieldGrids(node.parentElement || node);
    }
}))).observe(document.documentElement, { childList: true, subtree: true });

watchFieldGrids(document);

window.Alpine = Alpine;

Alpine.start();

document.getElementById('menu-preset')?.remove();

Alpine.nextTick(() => revealMenuCurrent());
