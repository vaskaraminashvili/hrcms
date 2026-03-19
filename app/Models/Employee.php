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

    public function academicPositions(): HasMany
    {
        return $this->hasMany(AcademicPosition::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function academicDegrees(): HasMany
    {
        return $this->hasMany(AcademicDegree::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function scientificProjects(): HasMany
    {
        return $this->hasMany(ScientificProject::class);
    }

    public function trainingsSeminars(): HasMany
    {
        return $this->hasMany(TrainingSeminar::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function textbooks(): HasMany
    {
        return $this->hasMany(Textbook::class);
    }

    public function scientificForums(): HasMany
    {
        return $this->hasMany(ScientificForum::class);
    }

    public function scholarshipsAwards(): HasMany
    {
        return $this->hasMany(ScholarshipAward::class);
    }

    public function foreignLanguages(): HasMany
    {
        return $this->hasMany(ForeignLanguage::class);
    }

    public function computerSkills(): HasMany
    {
        return $this->hasMany(ComputerSkill::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
}
