<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class TrainingSeminar extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'trainings_seminars';

    protected $fillable = [
        'employee_id',
        'institution',
        'topic',
        'started_at',
        'ended_at',
    ];

    public array $translatable = ['institution', 'topic'];

    protected function casts(): array
    {
        return [
            'institution' => 'array',
            'topic' => 'array',
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
