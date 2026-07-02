<?php

namespace App\Models;

use App\Enums\EmployeeStatusEnum;
use App\Enums\Gender;
use App\Enums\PersonalFile;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
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
        'mobile_number',
        'account_number',
        'address_details',
        'status',
        'photo',
    ];

    /**
     * Localized given name plus family name for display (breadcrumbs, record titles).
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            return trim(sprintf('%s %s', $this->name ?? '', $this->surname ?? ''));
        });
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:d.m.Y',
            'education' => 'integer',
            'gender' => Gender::class,
            'address_details' => 'array',
            'status' => EmployeeStatusEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function appointmentPositions(): HasMany
    {
        return $this->hasMany(Position::class)
            ->excludeScheduledDismissals();
    }

    public function academicPositions(): HasMany
    {
        return $this->hasMany(AcademicPosition::class)->orderBy('sort');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('sort');
    }

    public function academicDegrees(): HasMany
    {
        return $this->hasMany(AcademicDegree::class)->orderBy('sort');
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class)
            ->orderByDesc('ended_at')
            ->orderByDesc('started_at');
    }

    public function scientificProjects(): HasMany
    {
        return $this->hasMany(ScientificProject::class)->orderBy('sort');
    }

    public function trainingsSeminars(): HasMany
    {
        return $this->hasMany(TrainingSeminar::class)->orderBy('sort');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class)->orderBy('sort');
    }

    public function textbooks(): HasMany
    {
        return $this->hasMany(Textbook::class)->orderBy('sort');
    }

    public function scientificForums(): HasMany
    {
        return $this->hasMany(ScientificForum::class)->orderBy('sort');
    }

    public function scholarshipsAwards(): HasMany
    {
        return $this->hasMany(ScholarshipAward::class)->orderBy('sort');
    }

    public function foreignLanguages(): HasMany
    {
        return $this->hasMany(ForeignLanguage::class)->orderBy('sort');
    }

    public function computerSkills(): HasMany
    {
        return $this->hasMany(ComputerSkill::class)->orderBy('sort');
    }

    public function other(): HasMany
    {
        return $this->hasMany(OtherDocument::class)->orderBy('sort');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function registerMediaCollections(): void
    {
        $this->addLocalMediaCollection('employee_image');
        $this->addLocalMediaCollection('basic_information_attachments');

        foreach (PersonalFile::cases() as $personalFile) {
            $this->addLocalMediaCollection($personalFile->mediaCollectionName());
        }
    }

    private function addLocalMediaCollection(string $name): void
    {
        $this->addMediaCollection($name)
            ->useDisk('local')
            ->storeConversionsOnDisk('local');
    }

    public function employeeImageUrl(): ?string
    {
        $mediaImageUrl = $this->getFirstMediaUrl('employee_image');
        if ($mediaImageUrl !== '') {
            return $mediaImageUrl;
        }

        $photo = trim((string) $this->photo);
        if ($photo === '') {
            return null;
        }

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $this->encodeUrlPath($photo);
        }

        return $this->encodeUrlPath('https://sms.tsmu.edu/hr/img/'.$photo);
    }

    private function encodeUrlPath(string $url): string
    {
        $parts = parse_url($url);

        if (! is_array($parts)) {
            return $url;
        }

        $path = $parts['path'] ?? '';
        $encodedPath = Collection::make(explode('/', $path))
            ->map(fn (string $segment): string => rawurlencode(rawurldecode($segment)))
            ->implode('/');

        $scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $auth = $user !== '' ? $user.($pass !== '' ? ':'.$pass : '').'@' : '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $scheme.$auth.$host.$port.$encodedPath.$query.$fragment;
    }
}
