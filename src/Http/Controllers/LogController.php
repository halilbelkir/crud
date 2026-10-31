<?php

namespace crudPackage\Http\Controllers;

use crudPackage\Http\Controllers\Controller;
use crudPackage\Models\User;
use crudPackage\Support\Actions;
use crudPackage\Support\ActivityLog;
use crudPackage\Support\Search;
use crudPackage\Support\Table;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class LogController extends Controller
{
    public function index(Request $request): View|Response
    {
        $records = Activity::query()
            ->with('causer')
            ->when($request->filled('q'), function ($query) use ($request)
            {
                $term   = (string) $request->string('q');
                $like   = Search::pattern($term);
                $folded = Search::fold($term);

                $events = collect(ActivityLog::events())
                    ->filter(fn (array $event) => str_contains(Search::fold($event['label']), $folded))
                    ->keys()
                    ->all();

                $tables = collect(ActivityLog::tables())
                    ->filter(fn (string $label) => str_contains(Search::fold($label), $folded))
                    ->keys()
                    ->all();

                $query->where(function ($inner) use ($like, $events, $tables)
                {
                    $inner->whereIn('description', $events)
                        ->orWhereIn('log_name', $tables)
                        ->orWhere('properties', 'like', $like)
                        ->orWhereHasMorph('causer', [User::class], fn ($causer) => $causer->where('name', 'like', $like));
                });
            })
            ->when($request->filled('baslangic'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('baslangic')))
            ->when($request->filled('bitis'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('bitis')))
            ->when($request->filled('table'), fn ($query) => $query->where('log_name', $request->string('table')))
            ->when($request->filled('event'), fn ($query) => $query->where('description', $request->string('event')))
            ->when($request->filled('user'), fn ($query) => $query->where('causer_id', $request->integer('user')))
            ->orderByDesc('id');

        $columns = $this->columns();
        $records = Table::sort($records, $columns);

        if ($export = Table::export($records, $columns, 'Geçmiş'))
        {
            return $export;
        }

        $logs   = $records->paginate(Table::perPage())->withQueryString();
        $tables = ActivityLog::tables();
        $users  = User::orderBy('name')->pluck('name', 'id')->all();
        $events = collect(ActivityLog::events())->map(fn ($event) => $event['label'])->all();
        $total  = Activity::count();

        return view('crudPackage::logs.index', compact('logs', 'columns', 'tables', 'users', 'events', 'total'));
    }

    public function show(Activity $log): View
    {
        $properties = (array) $log->properties->toArray();
        $before     = ActivityLog::before($properties);
        $after      = ActivityLog::after($properties);
        $changes    = ActivityLog::changes($before, $after);
        $snapshot   = ActivityLog::attributes($after ?? $before);

        return view('crudPackage::logs.detail', compact('log', 'changes', 'snapshot'));
    }

    protected function columns(): array
    {
        return [
            [
                'type'     => 'lead',
                'label'    => 'Kayıt',
                'width'    => '2.2fr',
                'noAvatar' => true,
                'name'     => fn (Activity $log) => ActivityLog::subject($log),
                'sub'      => fn (Activity $log) => ActivityLog::table($log->log_name),
            ],
            [
                'type'     => 'badge',
                'label'    => 'İşlem',
                'width'    => '1.1fr',
                'sortable' => 'description',
                'value'    => fn (Activity $log) => ActivityLog::label($log->description),
                'tone'     => fn (Activity $log) => ActivityLog::tone($log->description),
            ],
            [
                'type'  => 'text',
                'label' => 'Kullanıcı',
                'width' => '1.3fr',
                'value' => fn (Activity $log) => $log->causer?->name ?? 'Sistem',
            ],
            [
                'type'     => 'text',
                'label'    => 'Tarih',
                'width'    => '1.2fr',
                'sortable' => 'created_at',
                'value'    => fn (Activity $log) => $log->created_at?->format('d.m.Y H:i'),
            ],
            [
                'type'   => 'actions',
                'width'  => '60px',
                'status' => false,
                'menu'   => fn (Activity $log) => Actions::menu('logs', $log),
            ],
        ];
    }
}
