<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute ველი უნდა იყოს მიღებული.',
    'accepted_if' => ':attribute ველი უნდა იყოს მიღებული, როდესაც :other არის :value.',
    'active_url' => ':attribute ველი უნდა იყოს სწორი URL მისამართი.',
    'after' => ':attribute ველი უნდა იყოს :date-ის შემდეგ თარიღი.',
    'after_or_equal' => ':attribute ველი უნდა იყოს :date-ის ტოლი ან შემდეგი თარიღი.',
    'alpha' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებს.',
    'alpha_dash' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებს, ციფრებს, დეფისებს და ხაზგასმებს.',
    'alpha_num' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ასოებს და ციფრებს.',
    'any_of' => ':attribute ველი არასწორია.',
    'array' => ':attribute ველი უნდა იყოს მასივი.',
    'ascii' => ':attribute ველი უნდა შეიცავდეს მხოლოდ ერთბაიტიან ალფანუმერულ სიმბოლოებს.',
    'before' => ':attribute ველი უნდა იყოს :date-მდე თარიღი.',
    'before_or_equal' => ':attribute ველი უნდა იყოს :date-ის ტოლი ან წინა თარიღი.',
    'between' => [
        'array' => ':attribute ველს უნდა ჰქონდეს :min-დან :max-მდე ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს :min-დან :max კილობაიტამდე.',
        'numeric' => ':attribute ველი უნდა იყოს :min-დან :max-მდე.',
        'string' => ':attribute ველი უნდა შეიცავდეს :min-დან :max სიმბოლომდე.',
    ],
    'boolean' => ':attribute ველი უნდა იყოს true ან false.',
    'can' => ':attribute ველი შეიცავს არაავტორიზებულ მნიშვნელობას.',
    'confirmed' => ':attribute ველის დადასტურება არ ემთხვევა.',
    'contains' => ':attribute ველში აკლია საჭირო მნიშვნელობა.',
    'current_password' => 'პაროლი არასწორია.',
    'date' => ':attribute ველი უნდა იყოს სწორი თარიღი.',
    'date_equals' => ':attribute ველი უნდა იყოს :date-ის ტოლი თარიღი.',
    'date_format' => ':attribute ველი უნდა შეესაბამებოდეს :format ფორმატს.',
    'decimal' => ':attribute ველს უნდა ჰქონდეს :decimal ათობითი ნიშანი.',
    'declined' => ':attribute ველი უნდა იყოს უარყოფილი.',
    'declined_if' => ':attribute ველი უნდა იყოს უარყოფილი, როდესაც :other არის :value.',
    'different' => ':attribute ველი და :other უნდა იყოს განსხვავებული.',
    'digits' => ':attribute ველი უნდა შეიცავდეს :digits ციფრს.',
    'digits_between' => ':attribute ველი უნდა შეიცავდეს :min-დან :max ციფრამდე.',
    'dimensions' => ':attribute ველს აქვს სურათის არასწორი განზომილებები.',
    'distinct' => ':attribute ველს აქვს დუბლირებული მნიშვნელობა.',
    'doesnt_contain' => ':attribute ველი არ უნდა შეიცავდეს შემდეგს: :values.',
    'doesnt_end_with' => ':attribute ველი არ უნდა მთავრდებოდეს შემდეგით: :values.',
    'doesnt_start_with' => ':attribute ველი არ უნდა იწყებოდეს შემდეგით: :values.',
    'email' => ':attribute ველი უნდა იყოს სწორი ელ-ფოსტის მისამართი.',
    'encoding' => ':attribute ველი კოდირებული უნდა იყოს :encoding-ში.',
    'ends_with' => ':attribute ველი უნდა მთავრდებოდეს შემდეგიდან ერთ-ერთით: :values.',
    'enum' => 'არჩეული :attribute არასწორია.',
    'exists' => 'არჩეული :attribute არასწორია.',
    'extensions' => ':attribute ველს უნდა ჰქონდეს შემდეგი გაფართოებებიდან ერთ-ერთი: :values.',
    'file' => ':attribute ველი უნდა იყოს ფაილი.',
    'filled' => ':attribute ველს უნდა ჰქონდეს მნიშვნელობა.',
    'gt' => [
        'array' => ':attribute ველს უნდა ჰქონდეს :value-ზე მეტი ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს :value კილობაიტზე მეტი.',
        'numeric' => ':attribute ველი უნდა იყოს :value-ზე მეტი.',
        'string' => ':attribute ველი უნდა შეიცავდეს :value სიმბოლოზე მეტს.',
    ],
    'gte' => [
        'array' => ':attribute ველს უნდა ჰქონდეს :value ან მეტი ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს :value კილობაიტის ტოლი ან მეტი.',
        'numeric' => ':attribute ველი უნდა იყოს :value-ის ტოლი ან მეტი.',
        'string' => ':attribute ველი უნდა შეიცავდეს :value ან მეტ სიმბოლოს.',
    ],
    'hex_color' => ':attribute ველი უნდა იყოს სწორი თექვსმეტობითი ფერის კოდი.',
    'image' => ':attribute ველი უნდა იყოს სურათი.',
    'in' => 'არჩეული :attribute არასწორია.',
    'in_array' => ':attribute ველი უნდა არსებობდეს :other-ში.',
    'in_array_keys' => ':attribute ველი უნდა შეიცავდეს შემდეგი გასაღებებიდან ერთ-ერთს: :values.',
    'integer' => ':attribute ველი უნდა იყოს მთელი რიცხვი.',
    'ip' => ':attribute ველი უნდა იყოს სწორი IP მისამართი.',
    'ipv4' => ':attribute ველი უნდა იყოს სწორი IPv4 მისამართი.',
    'ipv6' => ':attribute ველი უნდა იყოს სწორი IPv6 მისამართი.',
    'json' => ':attribute ველი უნდა იყოს სწორი JSON სტრიქონი.',
    'list' => ':attribute ველი უნდა იყოს სია.',
    'lowercase' => ':attribute ველი უნდა იყოს მხოლოდ პატარა ასოებით.',
    'lt' => [
        'array' => ':attribute ველს უნდა ჰქონდეს :value-ზე ნაკლები ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს :value კილობაიტზე ნაკლები.',
        'numeric' => ':attribute ველი უნდა იყოს :value-ზე ნაკლები.',
        'string' => ':attribute ველი უნდა შეიცავდეს :value სიმბოლოზე ნაკლებს.',
    ],
    'lte' => [
        'array' => ':attribute ველს არ უნდა ჰქონდეს :value-ზე მეტი ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს :value კილობაიტის ტოლი ან ნაკლები.',
        'numeric' => ':attribute ველი უნდა იყოს :value-ის ტოლი ან ნაკლები.',
        'string' => ':attribute ველი უნდა შეიცავდეს :value ან ნაკლებ სიმბოლოს.',
    ],
    'mac_address' => ':attribute ველი უნდა იყოს სწორი MAC მისამართი.',
    'max' => [
        'array' => ':attribute ველს არ უნდა ჰქონდეს :max-ზე მეტი ელემენტი.',
        'file' => ':attribute ველი არ უნდა იყოს :max კილობაიტზე მეტი.',
        'numeric' => ':attribute ველი არ უნდა იყოს :max-ზე მეტი.',
        'string' => ':attribute ველი არ უნდა შეიცავდეს :max სიმბოლოზე მეტს.',
    ],
    'max_digits' => ':attribute ველს არ უნდა ჰქონდეს :max-ზე მეტი ციფრი.',
    'mimes' => ':attribute ველი უნდა იყოს შემდეგი ტიპის ფაილი: :values.',
    'mimetypes' => ':attribute ველი უნდა იყოს შემდეგი ტიპის ფაილი: :values.',
    'min' => [
        'array' => ':attribute ველს უნდა ჰქონდეს სულ მცირე :min ელემენტი.',
        'file' => ':attribute ველი უნდა იყოს სულ მცირე :min კილობაიტი.',
        'numeric' => ':attribute ველი უნდა იყოს სულ მცირე :min.',
        'string' => ':attribute ველი უნდა შეიცავდეს სულ მცირე :min სიმბოლოს.',
    ],
    'min_digits' => ':attribute ველს უნდა ჰქონდეს სულ მცირე :min ციფრი.',
    'missing' => ':attribute ველი უნდა აკლდეს.',
    'missing_if' => ':attribute ველი უნდა აკლდეს, როდესაც :other არის :value.',
    'missing_unless' => ':attribute ველი უნდა აკლდეს, გარდა იმ შემთხვევისა, როდესაც :other არის :value.',
    'missing_with' => ':attribute ველი უნდა აკლდეს, როდესაც :values არის მითითებული.',
    'missing_with_all' => ':attribute ველი უნდა აკლდეს, როდესაც :values მითითებულია.',
    'multiple_of' => ':attribute ველი უნდა იყოს :value-ის ჯერადი.',
    'not_in' => 'არჩეული :attribute არასწორია.',
    'not_regex' => ':attribute ველის ფორმატი არასწორია.',
    'numeric' => ':attribute ველი უნდა იყოს რიცხვი.',
    'password' => [
        'letters' => ':attribute ველი უნდა შეიცავდეს სულ მცირე ერთ ასოს.',
        'mixed' => ':attribute ველი უნდა შეიცავდეს სულ მცირე ერთ დიდ და ერთ პატარა ასოს.',
        'numbers' => ':attribute ველი უნდა შეიცავდეს სულ მცირე ერთ ციფრს.',
        'symbols' => ':attribute ველი უნდა შეიცავდეს სულ მცირე ერთ სიმბოლოს.',
        'uncompromised' => 'მითითებული :attribute გამოჩნდა მონაცემთა გაჟონვაში. გთხოვთ აირჩიოთ სხვა :attribute.',
    ],
    'present' => ':attribute ველი უნდა იყოს მითითებული.',
    'present_if' => ':attribute ველი უნდა იყოს მითითებული, როდესაც :other არის :value.',
    'present_unless' => ':attribute ველი უნდა იყოს მითითებული, გარდა იმ შემთხვევისა, როდესაც :other არის :value.',
    'present_with' => ':attribute ველი უნდა იყოს მითითებული, როდესაც :values არის მითითებული.',
    'present_with_all' => ':attribute ველი უნდა იყოს მითითებული, როდესაც :values მითითებულია.',
    'prohibited' => ':attribute ველი აკრძალულია.',
    'prohibited_if' => ':attribute ველი აკრძალულია, როდესაც :other არის :value.',
    'prohibited_if_accepted' => ':attribute ველი აკრძალულია, როდესაც :other მიღებულია.',
    'prohibited_if_declined' => ':attribute ველი აკრძალულია, როდესაც :other უარყოფილია.',
    'prohibited_unless' => ':attribute ველი აკრძალულია, გარდა იმ შემთხვევისა, როდესაც :other არის :values-ში.',
    'prohibits' => ':attribute ველი კრძალავს :other-ის არსებობას.',
    'regex' => ':attribute ველის ფორმატი არასწორია.',
    'required' => ':attribute ველი სავალდებულოა.',
    'required_array_keys' => ':attribute ველი უნდა შეიცავდეს ჩანაწერებს: :values.',
    'required_if' => ':attribute ველი სავალდებულოა, როდესაც :other არის :value.',
    'required_if_accepted' => ':attribute ველი სავალდებულოა, როდესაც :other მიღებულია.',
    'required_if_declined' => ':attribute ველი სავალდებულოა, როდესაც :other უარყოფილია.',
    'required_unless' => ':attribute ველი სავალდებულოა, გარდა იმ შემთხვევისა, როდესაც :other არის :values-ში.',
    'required_with' => ':attribute ველი სავალდებულოა, როდესაც :values მითითებულია.',
    'required_with_all' => ':attribute ველი სავალდებულოა, როდესაც :values მითითებულია.',
    'required_without' => ':attribute ველი სავალდებულოა, როდესაც :values არ არის მითითებული.',
    'required_without_all' => ':attribute ველი სავალდებულოა, როდესაც :values-დან არცერთია მითითებული.',
    'same' => ':attribute ველი უნდა ემთხვეოდეს :other-ს.',
    'size' => [
        'array' => ':attribute ველი უნდა შეიცავდეს :size ელემენტს.',
        'file' => ':attribute ველი უნდა იყოს :size კილობაიტი.',
        'numeric' => ':attribute ველი უნდა იყოს :size.',
        'string' => ':attribute ველი უნდა შეიცავდეს :size სიმბოლოს.',
    ],
    'starts_with' => ':attribute ველი უნდა იწყებოდეს შემდეგიდან ერთ-ერთით: :values.',
    'string' => ':attribute ველი უნდა იყოს სტრიქონი.',
    'timezone' => ':attribute ველი უნდა იყოს სწორი დროის სარტყელი.',
    'unique' => ':attribute უკვე გამოყენებულია.',
    'uploaded' => ':attribute ატვირთვა ვერ მოხერხდა.',
    'uppercase' => ':attribute ველი უნდა იყოს მხოლოდ დიდი ასოებით.',
    'url' => ':attribute ველი უნდა იყოს სწორი URL მისამართი.',
    'ulid' => ':attribute ველი უნდა იყოს სწორი ULID.',
    'uuid' => ':attribute ველი უნდა იყოს სწორი UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
