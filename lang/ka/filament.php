<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package / panel defaults (e.g. activity log, shield)
    |--------------------------------------------------------------------------
    */
    'navigation_label' => 'მომხმარებლის აქტივობები',
    'title' => 'მომხმარებლის აქტივობები',
    'model_label' => 'აქტივობა',
    'plural_model_label' => 'აქტივობები',

    /*
    |--------------------------------------------------------------------------
    | Activity log (filament-activity-log)
    |--------------------------------------------------------------------------
    */
    'event' => 'მოქმედება',
    'subject_type' => 'ობიექტის ტიპი',
    'causer' => [
        'name' => 'ინიციატორი',
    ],

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
    'guard_name' => 'გარდის სახელი',
    'permissions_count' => 'უფლებების რაოდენობა',
    'employee_id' => 'თანამშრომელი',
    'department_id' => 'დეპარტამენტი',
    'cancel' => 'გაუქმება',
    'mobile_number' => 'ტელეფონის ნომერი',
    'account_number' => 'ანგარიშის ნომერი',
    'address_jurisdiction' => 'იურიდიული მისამართი',
    'en_address_jurisdiction' => 'იურიდიული მისამართი (ინგლისურად)',
    'address_physical' => 'ფაქტობრივი მისამართი',
    'en_address_physical' => 'ფაქტობრივი მისამართი (ინგლისურად)',
    'employee_image' => 'თანამშრომლის სურათი',
    'save' => 'შენახვა',
    'save_history' => 'შენახვა ისტორიით',
    'position_edit' => [
        'modal_save_history_heading' => 'შენახვა პოზიციის ისტორიით?',
        'modal_save_history_description' => 'შენახული ცვლილებები ჩაიწერება თანამდებობის ისტორიაში აუდიტისთვის.',
        'modal_save_history_submit' => 'შენახვა ისტორიით',
    ],
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
    'vacation' => 'შვებულება',
    'vacation_policy' => 'შვებულების პოლიტიკა',
    'vacation_policies' => 'შვებულების პოლიტიკები',
    'vacation_edit' => 'შვებულების რედაქტირება',
    'vacation_type' => 'შვებულების ტიპი',
    'vacation_status' => 'შვებულების სტატუსი',
    'vacation_reason' => 'შვებულების მიზეზი',
    'vacation_notes' => 'შვებულების შენიშვნები',
    'vacation_start_date' => 'შვებულების დაწყების თარიღი',
    'vacation_end_date' => 'შვებულების დასრულების თარიღი',
    'vacation_working_days_count' => 'შვებულების მუშაობის დღეები',
    'vacation_days' => 'შვებულების დღეები',
    'used_vacation_days' => 'გამოყენებული  დღეები',
    'available_vacation_days' => 'ხელმისაწვდომი  დღეები',
    'total_vacation_days' => 'საერთო  დღეები',
    'transferred_days' => 'გადმოტანილი  დღეები',
    'vacation_insufficient_balance' => 'შვებულების დღეები საკმარისი არ არის',
    'vacation_insufficient_balance_body' => 'დარჩენილი: :available , მოთხოვნილი: :requested .',
    'working_days_count_helper_text' => 'შაბათი: :saturday, კვირა: :sunday.:public_holidays_line',
    'working_days_count_helper_public_holidays_none' => ' საჯარო დასვენების დღეები ამ პერიოდში სამუშაო დღეებში არ არის.',
    'working_days_count_helper_public_holidays_some' => ' საჯარო დასვენების :count დღე არ ითვლება შვებულების ბალანსში.',
    'changes' => 'ცვლილებები',
    'with_image' => 'სურათით',
    'without_image' => 'სურათის გარეშე',
    'employee_image' => 'თანამშრომლის სურათი',
    'vacation_policy_settings' => [
        'days' => 'დღეები',
        'saturday_allowed' => 'შაბათი ითვლება',
        'sunday_allowed' => 'კვირა ითვლება',
        'yes' => 'კი',
        'no' => 'არა',
    ],
    'comment' => 'კომენტარი',
    'created_at' => 'შექმნის თარიღი',
    'updated_at' => 'განახლების თარიღი',
    'deleted_at' => 'წაშლის თარიღი',
    'from_year' => 'წლიდან',
    'to_year' => 'წლამდე',
    'days_count' => 'დღეების რაოდენობა',
    'position' => 'თანამდებობა',
    'working_days_count' => 'სამუშაო დღეების რაოდენობა',
    'type' => 'ტიპი',
    'reason' => 'მიზეზი',
    'notes' => 'შენიშვნები',
    'start_date' => 'დაწყების თარიღი',
    'end_date' => 'დასრულების თარიღი',
    'place' => 'ადგილი',
    'start_date' => 'დაწყების თარიღი',
    'end_date' => 'დასრულების თარიღი',
    'working_days_count' => 'სამუშაო დღეების რაოდენობა',
    'type' => 'ტიპი',
    'status' => 'სტატუსი',
    'active' => 'აქტიური',
    'archived' => 'დაარქივირებული',
    'created_at' => 'შექმნის თარიღი',
    'updated_at' => 'განახლების თარიღი',
    'search_by_name' => 'ძებნა სახელით ან გვარით',
    'name' => 'სახელი',
    'surname' => 'გვარი',
    'name_eng' => 'სახელი ინგლისურად',
    'surrname_eng' => 'გვარი ინგლისურად',
    'add_record' => 'ჩანაწერის დამატება',
    'range_selected' => 'ცვლილების პერიოდი: :from - :to',
    'range_not_selected' => 'ცვლილების პერიოდი არ აირჩიოს',
    'position_history_title' => 'თანამდებობის ისტორია',
    'changed_by' => 'ვინ შეცვალა',
    'changed_fields' => [
        'salary' => 'ხელფასი',
        'position_type' => 'თანამდებობის ტიპი',
        'status' => 'თანამდებობის სტატუსი',
        'date_start' => 'დაწყების თარიღი',
        'date_end' => 'დასრულების თარიღი',
        'vacation_policy' => 'შვებულების პოლიტიკა',
        'clinical_text' => 'კლინიკური დეტალები',
        'automative_renewal' => 'ავტომატური გახანგრძლივება',
        'act_number' => 'აქტის ნომერი',
        'act_date' => 'აქტის თარიღი',
        'updated_at' => 'განახლების თარიღი',
        'created_at' => 'შექმნის თარიღი',
        'deleted_at' => 'წაშლის თარიღი',
        'comment' => 'კომენტარი',
        'place_id' => 'ადგილი',
        'staff_type' => 'შტატი',
        'clinical' => 'კლინიკური',
    ],

    'position_history_affects' => [
        'Salary' => 'ხელფასი',
        'Status' => 'სტატუსი',
        'PositionType' => 'თანამდებობის ტიპი',
        'StaffType' => 'საშტატო / არა საშტატო',
        'DateStart' => 'დაწყების თარიღი',
        'DateEnd' => 'დასრულების თარიღი',
        'Clinical' => 'კლინიკური',
        'Place' => 'ადგილი',
        'ActNumber' => 'აქტის ნომერი',
        'ClinicalText' => 'კლინიკური დეტალები',
    ],

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
        'public_holidays' => [
            'navigation_label' => 'სახელმწიფო დღესასწაულები',
            'model_label' => 'სახელმწიფო დღესასწაული',
            'plural_model_label' => 'სახელმწიფო დღესასწაულები',
        ],
        'vacations' => [
            'navigation_label' => 'შვებულებები',
            'model_label' => 'შვებულება',
            'plural_model_label' => 'შვებულებები',
        ],
        'vacation_policies' => [
            'navigation_label' => 'შვებულების დროცულებები',
            'model_label' => 'შვებულების დროცულება',
            'plural_model_label' => 'შვებულების დროცულებები',
        ],
        'position_histories' => [
            'navigation_label' => 'თანამდებობის ისტორია',
            'model_label' => 'თანამდებობის ისტორია',
            'plural_model_label' => 'თანამდებობის ისტორია',
        ],
    ],

    'date' => 'თარიღი',

    'public_holiday_kind' => [
        'title' => 'სახელმწიფო დღესასწაულების ტიპი',
        'regular' => 'რეგულარული (სავალდებულო)',
        'exceptional' => 'გამონაკლისი (მაგ. თოვლი, სტიქია)',
        'yearly_planned' => 'დაგეგმილი წლიური დასვენება',
    ],

    'public_holiday_start_date' => 'დიაპაზონის დასაწყისი',
    'public_holiday_end_date' => 'დიაპაზონის დასასრული',
    'public_holiday_name' => 'დასახელება',
    'public_holiday_name_placeholder' => 'არასავალდებულო აღწერა',
    'public_holiday_series_id' => 'პარტიის ID',
    'public_holiday_invalid_range_title' => 'არასწორი თარიღების დიაპაზონი',
    'public_holiday_invalid_range_body' => 'დასასრული თარიღი უნდა იყოს დასაწყისის ტოლი ან მის შემდეგ.',
    'public_holiday_duplicate_days_title' => 'ზოგიერთი დღე უკვე არსებობს',
    'public_holiday_duplicate_days_body' => 'ამ დიაპაზონში ერთი ან მეტი თარიღი უკვე დარეგისტრირებულია. წაშალეთ ან შეცვალეთ არსებული ჩანაწერები.',

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
        'list_roles' => [
            'title' => 'როლები',
        ],
        'position_resource' => [
            'department_vacancy_limit' => 'ამ დეპარტამენტში აქტიური თანამდებობების მაქსიმუმია :max.',
            'duplicate_employee_department_title' => 'თანამდებობა უკვე არსებობს',
            'duplicate_employee_department_body' => 'ამ თანამშრომელს უკვე აქვს თანამდებობა არჩეულ დეპარტამენტში. აირჩიეთ სხვა დეპარტამენტი ან რედაქტირება გაუკეთეთ არსებულ თანამდებობას.',
        ],
    ],

    'department' => [
        'name' => 'დეპარტამენტი',
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
        'show_archived' => 'არქივის ჩვენება',
        'hide_archived' => 'არქივის დამალვა',
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
        'vacations' => [
            'title' => 'შვებულებები',
            'add_new_vacation' => 'შვებულების დამატება',
        ],
        'vacation_transfers' => [
            'title' => 'შვებულების გადაცემები',
            'add_new_vacation_transfer' => 'შვებულების გადაცემის დამატება',
        ],
    ],

    'infolist' => [
        'department' => 'დეპარტამენტი',
    ],

    'personal_file' => [
        'attachments' => 'დანართები',

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
            'other' => 'სხვა',
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
            'download_template' => 'Excel შაბლონის ჩამოტვირთვა',
            'import' => 'იმპორტი Excel-იდან',
            'import_modal_heading' => 'პუბლიკაციების იმპორტი',
            'import_submit' => 'იმპორტი',
            'import_file_label' => 'Excel ფაილი',
            'import_success' => 'პუბლიკაციები წარმატებით იქნა იმპორტირებული.',
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
