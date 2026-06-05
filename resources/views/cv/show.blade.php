<!DOCTYPE html>
<html lang="{{ $localeKey }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fullName }} — CV</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;600;700&display=swap">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans Georgian', 'Sylfaen', serif;
            background: #e8e8e8;
            margin: 0;
            padding: 2rem 1rem;
            font-size: 13px;
        }

        .cv-print-bar {
            max-width: 900px;
            margin: 0 auto 1rem;
            text-align: center;
        }

        .cv-print-bar button {
            border: 0;
            border-radius: 0.375rem;
            background: #2c5275;
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
        }

        .cv-print-bar button:hover {
            background: #1e3a5a;
        }

        .cv-wrapper {
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
            display: flex;
            min-height: 100vh;
            background: #fff;
            position: relative;
        }

        .cv-sidebar {
            width: 260px;
            min-width: 260px;
            background: linear-gradient(180deg, #2c4a6e 0%, #1e3a5a 60%, #162d46 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1.25rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .cv-flag {
            position: absolute;
            top: 12px;
            left: 12px;
            width: 36px;
            height: 24px;
        }

        .cv-flag svg {
            width: 36px;
            height: 24px;
            display: block;
        }

        .cv-photo-wrap {
            width: 140px;
            height: 155px;
            border: 3px solid rgba(255, 255, 255, 0.35);
            overflow: hidden;
            margin: 2rem auto 1rem;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: rgba(255, 255, 255, 0.4);
        }

        .cv-photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #a8c4e0;
            text-align: center;
            margin: 0.5rem 0 0.75rem;
            letter-spacing: 0.5px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.88);
            line-height: 1.4;
            width: 100%;
        }

        .contact-icon {
            width: 16px;
            min-width: 16px;
            margin-top: 1px;
            opacity: 0.75;
            flex-shrink: 0;
        }

        .sidebar-watermark {
            position: absolute;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: auto;
            opacity: 0.18;
            pointer-events: none;
            z-index: 0;
        }

        .cv-content {
            flex: 1;
            padding: 2.25rem 2.5rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .cv-watermark {
            position: absolute !important;
            right: -20px;
            /* top: 50%;
            transform: translateY(-50%); */
            width: 600px;
            opacity: 0.1;
            pointer-events: none;
            z-index: 0;
        }

        .cv-content>* {
            position: relative;
            z-index: 1;
        }

        .cv-logo-top {
            position: absolute;
            top: 1.25rem;
            right: 1.5rem;
            z-index: 2;
        }

        .cv-logo-top img {
            width: 52px;
            opacity: 0.7;
        }

        .cv-name {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
            padding-right: 60px;
        }

        .cv-basic {
            font-size: 13px;
            color: #444;
            margin-bottom: 0.2rem;
            letter-spacing: 0.3px;
        }

        .section-title {
            font-size: 17px;
            font-weight: 700;
            color: #1e3a5a;
            border-bottom: 2px solid #2c5275;
            padding-bottom: 4px;
            margin-top: 1.5rem;
            margin-bottom: 0.6rem;
            letter-spacing: 0.5px;
        }

        .field-row {
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
            padding: 2px 0;
            letter-spacing: 0.2px;
        }

        .field-row strong {
            font-weight: 600;
            color: #111;
        }

        .cv-entry {
            margin-bottom: 0.6rem;
            padding-bottom: 0.6rem;
            border-bottom: 0.5px solid #e8e8e8;
        }

        .cv-entry:last-child {
            border-bottom: none;
        }

        .sidebar-watermark-container {
            position: relative;
        }

        .cv-print-table {
            width: 100%;
            border: none;
        }

        .cv-sidebar-bg {
            display: none;
        }

        .cv-print-table thead {
            display: none;
        }

        .cv-print-table tbody,
        .cv-print-table tr,
        .cv-print-table td {
            display: block;
            padding: 0;
            border: none;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            .cv-print-bar {
                display: none;
            }

            .cv-wrapper {
                box-shadow: none;
                max-width: none;
                min-height: auto;
                display: block;
            }

            .cv-sidebar-bg {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 65mm;
                background: linear-gradient(180deg, #2c4a6e 0%, #1e3a5a 60%, #162d46 100%);
                z-index: 0;
            }

            .cv-sidebar {
                position: absolute;
                top: 0;
                left: 0;
                width: 65mm;
                min-width: 65mm;
                background: transparent;
                overflow: visible;
                z-index: 1;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .cv-content {
                margin-left: 65mm;
                padding: 0 10mm 12mm 8mm;
                overflow: visible;
            }

            .cv-print-table {
                display: table;
                border-collapse: collapse;
            }

            .cv-print-table thead {
                display: table-header-group;
            }

            .cv-print-table tbody {
                display: table-row-group;
            }

            .cv-print-table tr {
                display: table-row;
            }

            .cv-print-table td {
                display: table-cell;
            }

            .cv-print-table thead td {
                height: 12mm;
                line-height: 0;
                font-size: 0;
            }

            .cv-watermark {
                position: fixed !important;
                top: 50%;
                left: 50%;
                transform: translate(-30%, -50%);
                /* top: 50%;
                right: -8mm;
                transform: translateY(-50%); */
                width: 130mm;
                max-width: 55vw;
                opacity: 0.1;
                z-index: 0;
            }

            .section-title {
                break-after: avoid;
                page-break-after: avoid;
            }

            .cv-entry {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .contact-item {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .cv-photo-wrap {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            p,
            .field-row {
                orphans: 3;
                widows: 3;
            }

            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="cv-print-bar">
        <button type="button" onclick="window.print()">{{ __('cv.print') }}</button>
    </div>

    <div class="cv-wrapper">

        <div class="cv-sidebar-bg" aria-hidden="true"></div>

        <aside class="cv-sidebar">
            <div>
                <div class="cv-flag" aria-hidden="true">
                    @if ($localeKey === 'ka')
                        <img src="https://sms.tsmu.edu/hr/cv/img/flag.png" alt="ქართული">
                    @else
                        <img src="https://sms.tsmu.edu/hr/cv/img/br_flag.png" alt="English">
                    @endif

                </div>

                <div class="cv-photo-wrap">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="">
                    @else
                        &#128100;
                    @endif
                </div>

                <div class="sidebar-section-title">{{ __('cv.contact_information') }}</div>

                @if ($contact['phone'])
                    <div class="contact-item">
                        <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.9a16 16 0 0 0 6.16 6.16l.94-.94a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        {{ $contact['phone'] }}
                    </div>
                @endif

                @if ($contact['email'])
                    <div class="contact-item">
                        <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        {{ $contact['email'] }}
                    </div>
                @endif

                @if ($contact['address'])
                    <div class="contact-item">
                        <svg class="contact-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="10" r="3" />
                            <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 14 8 14s8-8.75 8-14a8 8 0 0 0-8-8z" />
                        </svg>
                        {{ $contact['address'] }}
                    </div>
                @endif
                <div class="sidebar-watermark-container">
                    <img class="sidebar-watermark" src="{{ $assets['logo'] }}" onerror="this.style.display='none'"
                        alt="" aria-hidden="true">
                </div>
            </div>
        </aside>

        <main class="cv-content">
            <img class="cv-watermark" src="{{ $assets['logo'] }}" onerror="this.style.display='none'" alt=""
                aria-hidden="true">

            <div class="cv-logo-top">
                <img src="{{ $assets['cvIcon'] }}" onerror="this.style.display='none'" alt="">
            </div>

            <table class="cv-print-table">
                <thead>
                    <tr>
                        <td aria-hidden="true"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="cv-name">{{ $fullName }}</div>

                            @if ($birthDate)
                                <div class="cv-basic">{{ __('cv.birth_date') }}: {{ $birthDate }}</div>
                            @endif

                            @if ($gender)
                                <div class="cv-basic">{{ __('cv.gender_label') }}: {{ $gender }}</div>
                            @endif

                            @foreach ($sections as $section)
                                <div class="section-title">{{ $section['title'] }}</div>

                                @foreach ($section['entries'] as $entry)
                                    <div class="cv-entry">
                                        @foreach ($entry['fields'] as $field)
                                            <div class="field-row">
                                                @if ($field['label'])
                                                    <strong>{{ $field['label'] }}:</strong>
                                                @endif
                                                @if ($field['value'])
                                                    @if ($field['label'])
                                                        {{ ' ' }}
                                                    @endif
                                                    {!! $field['label'] ? e($field['value']) : $field['value'] !!}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>

</body>

</html>
