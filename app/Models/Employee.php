<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'name_eng',
        'surrname_eng',
        'personal_number',
        'email',
        'birth_date',
        'gender',
        'citizenship',
        'education',
        'degree',
        'address',
        'pysical_address',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'education' => 'integer',
        ];
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
}
