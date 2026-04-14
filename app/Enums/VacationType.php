<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum VacationType: string implements HasLabel
{
    case PAID_LEAVE = 'PAID_LEAVE';
    case DAY_OFF = 'DAY_OFF';
    case BUSINESS_TRIP = 'BUSINESS_TRIP';
    case UNPAID_LEAVE = 'UNPAID_LEAVE';
    case SICK_LEAVE = 'SICK_LEAVE';
    case MATERNITY_LEAVE = 'MATERNITY_LEAVE';
    case PATERNITY_LEAVE = 'PATERNITY_LEAVE';
    case ADOPTION_LEAVE = 'ADOPTION_LEAVE';
    case CHILD_CARE_LEAVE = 'CHILD_CARE_LEAVE';
    case STUDY_LEAVE = 'STUDY_LEAVE';
    case MILITARY_SERVICE = 'MILITARY_SERVICE';
    case MILITARY_RESERVE = 'MILITARY_RESERVE';
    case COURT_APPEARANCE = 'COURT_APPEARANCE';
    case ELECTION_DUTY = 'ELECTION_DUTY';
    case DOMESTIC_VIOLENCE_SHELTER = 'DOMESTIC_VIOLENCE_SHELTER';
    case RECALLED_FROM_LEAVE = 'RECALLED_FROM_LEAVE';
    case ACADEMIC_PAID_LEAVE = 'ACADEMIC_PAID_LEAVE';

    public function getLabel(): string
    {
        return match ($this) {
            self::PAID_LEAVE => 'ანაზღაურებადი შვებულება',
            self::DAY_OFF => 'დეიოფი',
            self::BUSINESS_TRIP => 'მივლინება',
            self::UNPAID_LEAVE => 'ანაზღაურების გარეშე შვებულება',
            self::SICK_LEAVE => 'საავადმყოფო ფურცელი',
            self::MATERNITY_LEAVE => 'ორსულობისა და მშობიარობის გამო',
            self::PATERNITY_LEAVE => 'ახალშობილის მოვლის გამო',
            self::ADOPTION_LEAVE => 'ახალშობილის შვილად აყვანის გამო',
            self::CHILD_CARE_LEAVE => 'შვებულება 5 წლამდე ასაკის ბავშვის მოვლის გამო',
            self::STUDY_LEAVE => 'კვალიფიკაციის ამაღლება, პროფესიული გადამზადება ან სწავლა',
            self::MILITARY_SERVICE => 'სამხედრო სავალდებულო სამსახურში გაწვევა',
            self::MILITARY_RESERVE => 'სამხედრო სარეზერვო სამსახურში გაწვევა',
            self::COURT_APPEARANCE => 'საგამოძიებო, პროკურატურის ან სასამართლო ორგანოში გამოცხადება',
            self::ELECTION_DUTY => 'აქტიური საარჩევნო უფლების ან/და პასიური საარჩევნო უფლების განხორციელება',
            self::DOMESTIC_VIOLENCE_SHELTER => 'ქალთა მიმართ ძალადობის ან/და ოჯახში ძალადობის მსხვერპლის თავშესაფარში ან/და კრიზისულ ცენტრში მოთავსება',
            self::RECALLED_FROM_LEAVE => 'ანაზღაურებადი შვებულებიდან გამოძახება',
            self::ACADEMIC_PAID_LEAVE => 'ანაზღაურებადი შვებულება (აკადემიურისთვის)',
        };
    }
}
