<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public static function record(string $action, Model $auditable, string $description, array $metadata = []): void
    {
        self::create([
            'user_id' => request()->user()?->id,
            'action' => $action,
            'auditable_type' => $auditable::class,
            'auditable_id' => $auditable->getKey(),
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
