<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Education extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'institution',
        'program',
        'specialty',
        'started_at',
        'ended_at',
    ];

    public array $translatable = ['institution', 'program', 'specialty'];

    protected function casts(): array
    {
        return [
            'institution' => 'array',
            'program'     => 'array',
            'specialty'   => 'array',
            'started_at'  => 'date',
            'ended_at'    => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
