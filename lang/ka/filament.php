<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package / panel defaults (e.g. activity log, shield)
    |--------------------------------------------------------------------------
    */
    'navigation_label' => 'მომხმარებლის აქტივობები',
    'title' => 'მომხმარებლის აქტივობები',

    /*
    |--------------------------------------------------------------------------
    | Shared field & UI labels
    |--------------------------------------------------------------------------
    */
    'name' => 'სახელი',
    'surname' => 'გვარი',
    'name_eng' => 'სახელი (ინგლისურად)',
    'surrname_eng' => 'გვარი (ინგლისურად)',
    'personal_number' => 'პირადი ნომერი',
    'email' => 'ელფოსტა',
    'birth_date' => 'დაბადების თარიღი',
    'gender' => 'სქესი',
    'gender_default' => 'male',
    'education' => 'განათლება',
    'degree' => 'ხარისხი',
    'citizenship' => 'მოქალაქეობა',
    'address' => 'მისამართი',
    'pysical_address' => 'ფაქტობრივი მისამართი',
    'parent_id' => 'მშობელი დეპარტამენტი',
    'vacancy_count' => 'ვაკანსიების რაოდენობა',
    'status' => 'სტატუსი',
    'status_default' => 'active',
    'is_active' => 'აქტიური',
    'positions_count' => 'თანამდებობები',
    'employee_id' => 'თანამშრომელი',
    'department_id' => 'დეპარტამენტი',
    'place_id' => 'ადგილი',
    'position_type' => 'თანამდებობის ტიპი',
    'staff_type' => 'შტატი',
    'date_start' => 'დაწყების თარიღი',
    'date_end' => 'დასრულების თარიღი',
    'act_number' => 'აქტის ნომერი',
    'act_date' => 'აქტის თარიღი',
    'clinical' => 'კლინიკური',
    'clinical_text' => 'კლინიკური დეტალები',
    'automative_renewal' => 'ავტომატური გახანგრძლივება',
    'salary' => 'ხელფასი',
    'comment' => 'კომენტარი',
    'created_at' => 'შექმნის თარიღი',
    'updated_at' => 'განახლების თარიღი',
    'deleted_at' => 'წაშლის თარიღი',

    'employee' => [
        'name' => 'თანამშრომელი',
    ],

    'department_parent_hint_level' => 'დონე :level',
    'department_parent_hint_root' => 'დონე 1 (ძირი)',

    'education_level' => [
        'secondary' => 'საშუალო',
        'higher' => 'უმაღლესი',
    ],

    'staff_type_option' => [
        'established' => 'საშტატო',
        'non_established' => 'არა საშტატო',
    ],

    'clinical_option' => [
        'clinical' => 'კლინიკური',
        'non_clinical' => 'არაკლინიკური',
    ],

    'tabs' => [
        'container' => 'ჩანართები',
        'basic_information' => 'ძირითადი ინფორმაცია',
    ],

    'department_status' => [
        'active' => 'აქტიური',
        'archived' => 'არქივირებული',
        'inactive' => 'არააქტიური',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource navigation & model labels
    |--------------------------------------------------------------------------
    */
    'resources' => [
        'employees' => [
            'navigation_label' => 'თანამშრომლები',
            'model_label' => 'თანამშრომელი',
            'plural_model_label' => 'თანამშრომლები',
        ],
        'places' => [
            'navigation_label' => 'ადგილები',
            'model_label' => 'ადგილი',
            'plural_model_label' => 'ადგილები',
        ],
        'positions' => [
            'navigation_label' => 'თანამდებობები',
            'model_label' => 'თანამდებობა',
            'plural_model_label' => 'თანამდებობები',
        ],
        'departments' => [
            'navigation_label' => 'დეპარტამენტები',
            'model_label' => 'დეპარტამენტი',
            'plural_model_label' => 'დეპარტამენტები',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nested admin pages (use filament.admin.* in code)
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'list_places' => [
            'title' => 'ადგილები',
        ],
        'edit_place' => [
            'title' => 'ადგილის რედაქტირება',
        ],
        'list_employees' => [
            'title' => 'თანამშრომლები',
        ],
        'edit_employee' => [
            'title' => 'თანამშრომლის რედაქტირება',
        ],
        'view_employee' => [
            'title' => 'თანამშრომლის ნახვა',
        ],
        'list_positions' => [
            'title' => 'თანამდებობები',
        ],
        'edit_position' => [
            'title' => 'თანამდებობის რედაქტირება',
        ],
        'view_position' => [
            'title' => 'თანამდებობის ნახვა',
        ],
        'position_resource' => [
            'department_vacancy_limit' => 'ამ დეპარტამენტში აქტიური თანამდებობების მაქსიმუმია :max.',
        ],
    ],

    'department' => [
        'save_and_archive' => 'შენახვა და არქივში გადატანა',
        'modal_archive_heading' => 'დეპარტამენტის არქივირება და დუბლირება?',
        'modal_archive_description' => 'სახელის ან მშობლის შეცვლისას ეს დეპარტამენტი და მისი თანამდებობები არქივში გადავა, შემდეგ კი შეიქმნება ახალი ასლი თქვენი ცვლილებებით.',
        'modal_submit' => 'დიახ, გაგრძელება',
        'cancel' => 'გაუქმება',
        'cannot_delete_title' => 'დეპარტამენტის წაშლა შეუძლებელია',
        'cannot_delete_body' => 'ამ დეპარტამენტს აქვს მიბმული :count თანამდებობა(ები). ჯერ გადაანაწილეთ ან წაშალეთ ისინი.',
        'cannot_move_title' => 'დედეპარტამენტის გადატანა შეუძლებელია',
        'cannot_move_body' => 'ეს გადატანა აღემატება მაქსიმალურ სიღრმეს :max_depth დონით (ყველაზე ღრმა კვანძი მიაღწევს დონეს :required_depth).',
    ],

    'tree_departments' => [
        'new_department' => 'ახალი დეპარტამენტი',
    ],

    'create_department' => [
        'back_to_tree' => 'ხეზე დაბრუნება',
    ],

    'relation_managers' => [
        'positions' => [
            'title' => 'თანამდებობები',
            'add_new_position' => 'ახალი თანამდებობის დამატება',
            'renewal' => 'გახანგრძლივება',
        ],
    ],

    'infolist' => [
        'department' => 'დეპარტამენტი',
    ],

    'personal_file' => [
        'locale_georgian' => 'ქართული',
        'locale_english' => 'ინგლისური',
        'field_locale_georgian' => ':field (ქართული)',
        'field_locale_english' => ':field (ინგლისური)',

        'dates' => [
            'started_at' => 'დაწყების თარიღი',
            'ended_at' => 'დასრულების თარიღი',
            'published_at' => 'გამოქვეყნების თარიღი',
            'held_at' => 'ჩატარების თარიღი',
            'issued_at' => 'გაცემის თარიღი',
        ],

        'page_count' => 'გვერდების რაოდენობა',

        'academic_degrees' => [
            'degree' => 'ხარისხი',
            'other' => 'სხვა (ხარისხი = სხვა)',
        ],
        'academic_position' => [
            'title' => 'თანამდებობა',
        ],
        'computer_skills' => [
            'title' => 'პროგრამა',
            'level' => 'ფლობის ხარისხი',
        ],
        'foreign_languages' => [
            'language' => 'ენა',
            'level' => 'ფლობის ხარისხი',
        ],
        'scientific_projects' => [
            'project_name' => 'პროექტის სახელწოდება',
            'institution' => 'დაწესებულება',
            'position' => 'თანამდებობა',
        ],

        'education' => [
            'institution' => 'დაწესებულება',
            'program' => 'პროგრამა',
            'specialty' => 'სპეციალობა',
        ],

        'work_experience' => [
            'institution' => 'დაწესებულება',
            'position' => 'თანამდებობა',
        ],

        'trainings_seminars' => [
            'institution' => 'დაწესებულება',
            'topic' => 'თემა',
        ],

        'publications' => [
            'title' => 'სახელწოდება',
            'place' => 'გამოქვეყნების ადგილი',
            'co_authors' => 'თანაავტორები',
        ],

        'textbooks' => [
            'title' => 'სახელწოდება',
            'publisher' => 'გამომცემელი',
            'co_authors' => 'თანაავტორები',
        ],

        'scientific_forums' => [
            'title' => 'სახელწოდება',
            'participation_form' => 'მონაწილეობის ფორმა',
        ],

        'scholarships_awards' => [
            'title' => 'სახელწოდება',
            'issuer' => 'გამცემელი',
        ],

        'tabs' => [
            'academic_position' => 'აკადემიური თანამდებობა',
            'education' => 'განათლება',
            'academic_degrees' => 'აკადემიური ხარისხები',
            'work_experience' => 'სამუშაო გამოცდილება',
            'scientific_projects' => 'სამეცნიერო პროექტები',
            'trainings_seminars' => 'ტრენინგები, სემინარები',
            'publications' => 'პუბლიკაციები',
            'textbooks' => 'სახელმძღვანელოები',
            'scientific_forums' => 'სამეცნიერო ფორუმებში მონაწილეობა',
            'scholarships_awards' => 'სტიპენდიები / ჯილდოები / სახელმწიფო პრემიები',
            'foreign_languages' => 'უცხოური ენების ფლობის ხარისხი',
            'computer_skills' => 'კომპიუტერული პროგრამების ფლობის ხარისხი',
        ],
    ],

];
