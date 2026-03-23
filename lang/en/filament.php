<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Package / panel defaults (e.g. activity log, shield)
    |--------------------------------------------------------------------------
    */
    'navigation_label' => 'User Activities',
    'title' => 'User Activities',
    /*
     * Used by filament-activity-log and filament-shield vendor resources (same keys).
     * Prefer “Activity” here to match navigation above; publish RoleResource if roles need different labels.
     */
    'model_label' => 'Activity',
    'plural_model_label' => 'Activities',

    /*
    |--------------------------------------------------------------------------
    | Activity log (filament-activity-log)
    |--------------------------------------------------------------------------
    */
    'event' => 'Event',
    'subject_type' => 'Subject type',
    'causer' => [
        'name' => 'Causer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared field & UI labels
    |--------------------------------------------------------------------------
    */
    'name' => 'Name',
    'surname' => 'Surname',
    'name_eng' => 'Name (English)',
    'surrname_eng' => 'Surname (English)',
    'personal_number' => 'Personal number',
    'email' => 'Email address',
    'birth_date' => 'Birth date',
    'gender' => 'Gender',
    'gender_default' => 'male',
    'education' => 'Education',
    'degree' => 'Degree',
    'citizenship' => 'Citizenship',
    'address' => 'Address',
    'pysical_address' => 'Physical address',
    'parent_id' => 'Parent department',
    'vacancy_count' => 'Vacancy count',
    'status' => 'Status',
    'status_default' => 'active',
    'is_active' => 'Active',
    'positions_count' => 'Positions',
    'guard_name' => 'Guard name',
    'permissions_count' => 'Permissions',
    'employee_id' => 'Employee',
    'department_id' => 'Department',
    'place_id' => 'Place',
    'position_type' => 'Position type',
    'staff_type' => 'Staff type',
    'date_start' => 'Start date',
    'date_end' => 'End date',
    'act_number' => 'Act number',
    'act_date' => 'Act date',
    'clinical' => 'Clinical',
    'clinical_text' => 'Clinical details',
    'automative_renewal' => 'Automatic renewal',
    'salary' => 'Salary',
    'comment' => 'Comment',
    'created_at' => 'Created at',
    'updated_at' => 'Updated at',
    'deleted_at' => 'Deleted at',

    'employee' => [
        'name' => 'Employee',
    ],

    'department_parent_hint_level' => 'Level :level',
    'department_parent_hint_root' => 'Level 1 (Root)',

    'education_level' => [
        'secondary' => 'Secondary',
        'higher' => 'Higher',
    ],

    'staff_type_option' => [
        'established' => 'Established (staff)',
        'non_established' => 'Non-established',
    ],

    'clinical_option' => [
        'clinical' => 'Clinical',
        'non_clinical' => 'Non-clinical',
    ],

    'tabs' => [
        'container' => 'Tabs',
        'basic_information' => 'Basic information',
    ],

    'department_status' => [
        'active' => 'Active',
        'archived' => 'Archived',
        'inactive' => 'Inactive',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource navigation & model labels
    |--------------------------------------------------------------------------
    */
    'resources' => [
        'employees' => [
            'navigation_label' => 'Employees',
            'model_label' => 'Employee',
            'plural_model_label' => 'Employees',
        ],
        'places' => [
            'navigation_label' => 'Places',
            'model_label' => 'Place',
            'plural_model_label' => 'Places',
        ],
        'positions' => [
            'navigation_label' => 'Positions',
            'model_label' => 'Position',
            'plural_model_label' => 'Positions',
        ],
        'departments' => [
            'navigation_label' => 'Departments',
            'model_label' => 'Department',
            'plural_model_label' => 'Departments',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nested admin pages (use filament.admin.* in code)
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'list_places' => [
            'title' => 'Places',
        ],
        'edit_place' => [
            'title' => 'Edit place',
        ],
        'list_employees' => [
            'title' => 'Employees',
        ],
        'edit_employee' => [
            'title' => 'Edit employee',
        ],
        'view_employee' => [
            'title' => 'View employee',
        ],
        'list_positions' => [
            'title' => 'Positions',
        ],
        'edit_position' => [
            'title' => 'Edit position',
        ],
        'view_position' => [
            'title' => 'View position',
        ],
        'list_roles' => [
            'title' => 'Roles',
        ],
        'position_resource' => [
            'department_vacancy_limit' => 'This department allows at most :max active position(s).',
        ],
    ],

    'department' => [
        'save_and_archive' => 'Save & archive',
        'modal_archive_heading' => 'Archive & duplicate department?',
        'modal_archive_description' => 'Changing the name or parent will archive this department and its positions, then create a new duplicate with your changes.',
        'modal_submit' => 'Yes, proceed',
        'cancel' => 'Cancel',
        'cannot_delete_title' => 'Cannot delete department',
        'cannot_delete_body' => 'This department has :count position(s) assigned. Reassign or remove them first.',
        'cannot_move_title' => 'Cannot move department',
        'cannot_move_body' => 'This move would exceed the maximum depth of :max_depth levels (deepest node would reach level :required_depth).',
    ],

    'tree_departments' => [
        'new_department' => 'New department',
    ],

    'create_department' => [
        'back_to_tree' => 'Back to tree',
    ],

    'relation_managers' => [
        'positions' => [
            'title' => 'Positions',
            'add_new_position' => 'Add new position',
            'renewal' => 'Renewal',
        ],
    ],

    'infolist' => [
        'department' => 'Department',
    ],

    'personal_file' => [
        'locale_georgian' => 'Georgian',
        'locale_english' => 'English',
        'field_locale_georgian' => ':field (Georgian)',
        'field_locale_english' => ':field (English)',

        'dates' => [
            'started_at' => 'Start date',
            'ended_at' => 'End date',
            'published_at' => 'Publication date',
            'held_at' => 'Date held',
            'issued_at' => 'Issue date',
        ],

        'page_count' => 'Page count',

        'academic_degrees' => [
            'degree' => 'Degree',
            'other' => 'Other (when degree is other)',
        ],
        'academic_position' => [
            'title' => 'Position title',
        ],
        'computer_skills' => [
            'title' => 'Program',
            'level' => 'Proficiency',
        ],
        'foreign_languages' => [
            'language' => 'Language',
            'level' => 'Proficiency',
        ],
        'scientific_projects' => [
            'project_name' => 'Project name',
            'institution' => 'Institution',
            'position' => 'Position',
        ],

        'education' => [
            'institution' => 'Institution',
            'program' => 'Program',
            'specialty' => 'Specialty',
        ],

        'work_experience' => [
            'institution' => 'Institution',
            'position' => 'Position',
        ],

        'trainings_seminars' => [
            'institution' => 'Institution',
            'topic' => 'Topic',
        ],

        'publications' => [
            'title' => 'Title',
            'place' => 'Publication venue',
            'co_authors' => 'Co-authors',
        ],

        'textbooks' => [
            'title' => 'Title',
            'publisher' => 'Publisher',
            'co_authors' => 'Co-authors',
        ],

        'scientific_forums' => [
            'title' => 'Title',
            'participation_form' => 'Form of participation',
        ],

        'scholarships_awards' => [
            'title' => 'Title',
            'issuer' => 'Issuing body',
        ],

        'tabs' => [
            'academic_position' => 'Academic position',
            'education' => 'Education',
            'academic_degrees' => 'Academic degrees',
            'work_experience' => 'Work experience',
            'scientific_projects' => 'Scientific projects',
            'trainings_seminars' => 'Trainings & seminars',
            'publications' => 'Publications',
            'textbooks' => 'Textbooks',
            'scientific_forums' => 'Scientific forums participation',
            'scholarships_awards' => 'Scholarships / awards / state prizes',
            'foreign_languages' => 'Foreign language proficiency',
            'computer_skills' => 'Computer software proficiency',
        ],
    ],

];
