<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogObserver
{
    private string $table = 'activity_log';

    public function created(Model $model): void
    {
        DB::table($this->table)->insert([
            'id' => (string) Str::uuid(),
            'log_name' => 'default',
            'description' => $this->getModelName($model) . ' was created.',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => $this->causerType(),
            'causer_id' => $this->causerId(),
            'properties' => json_encode(['attributes' => $model->getAttributes()]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $original = array_intersect_key($model->getOriginal(), $changes);

        if (empty($changes)) {
            return;
        }

        DB::table($this->table)->insert([
            'id' => (string) Str::uuid(),
            'log_name' => 'default',
            'description' => $this->getModelName($model) . ' was updated.',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => $this->causerType(),
            'causer_id' => $this->causerId(),
            'properties' => json_encode([
                'old' => $original,
                'attributes' => $changes,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function deleted(Model $model): void
    {
        DB::table($this->table)->insert([
            'id' => (string) Str::uuid(),
            'log_name' => 'default',
            'description' => $this->getModelName($model) . ' was deleted.',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => $this->causerType(),
            'causer_id' => $this->causerId(),
            'properties' => json_encode(['attributes' => $model->getAttributes()]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getModelName(Model $model): string
    {
        $class = class_basename($model);
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $class);
    }

    private function causerType(): ?string
    {
        if (auth()->check()) {
            return get_class(auth()->user());
        }
        return null;
    }

    private function causerId(): ?string
    {
        if (auth()->check()) {
            return auth()->user()->getAuthIdentifier();
        }
        return null;
    }
}
