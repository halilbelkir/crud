<?php

namespace crudPackage\Listeners;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class GlobalCrudListener
{
    protected array $exceptTables = [
        'activity_log',
        'failed_jobs',
        'password_reset_tokens',
        'migrations',
    ];

    protected function shouldLog(Model $model): bool
    {
        if ($model instanceof Activity) {
            return false;
        }

        return ! in_array($model->getTable(), $this->exceptTables);
    }

    protected function log(Model $model, string $message, array $properties = []): void
    {
        activity()
            ->useLog($model->getTable())
            ->performedOn($model)
            ->causedBy(auth()->user())
            ->withProperties($properties)
            ->log($message);
    }

    public function created(Model $model): void
    {
        if (! $this->shouldLog($model)) return;

        $this->log($model, 1,
            [
                'attributes' => $model->getAttributes(),
            ]);
    }

    public function updated(Model $model): void
    {
        if (! $this->shouldLog($model)) return;

        $this->log($model, 2,
            [
                'old' => $model->getOriginal(),
                'new' => $model->getAttributes(),
            ]);
    }

    public function deleted(Model $model): void
    {
        if (! $this->shouldLog($model)) return;

        $this->log($model, 3,
            [
                'attributes' => $model->getOriginal(),
            ]);
    }
}
