<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { background: #E52716; color: white; padding: 20px 30px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 4px; }
        .header p { font-size: 11px; opacity: 0.9; }
        .content { padding: 0 30px 30px; }
        h2 { font-size: 14px; color: #E52716; border-bottom: 2px solid #E52716; padding-bottom: 4px; margin: 16px 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table th, table td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #eee; font-size: 11px; }
        table th { background: #f5f5f5; font-weight: bold; width: 35%; }
        .score-badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-weight: bold; color: white; font-size: 13px; }
        .score-high { background: #22c55e; }
        .score-mid { background: #f59e0b; }
        .score-low { background: #ef4444; }
        .list { margin: 0; padding-left: 18px; }
        .list li { margin-bottom: 3px; font-size: 11px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчёт по кандидату</h1>
        <p>{{ config('app.name') }} | Сформирован: {{ now()->format('d.m.Y H:i') }}</p>
    </div>

    <div class="content">
        <h2>Основная информация</h2>
        <table>
            <tr><th>ФИО</th><td>{{ $application->candidate?->name ?? '-' }}</td></tr>
            <tr><th>Email</th><td>{{ $application->candidate?->email ?? '-' }}</td></tr>
            <tr><th>Телефон</th><td>{{ $application->candidate?->phone ?? '-' }}</td></tr>
            <tr><th>Вакансия</th><td>{{ $application->vacancy?->title ?? '-' }}</td></tr>
            <tr><th>Дата подачи</th><td>{{ $application->created_at?->format('d.m.Y H:i') }}</td></tr>
            <tr><th>Статус</th><td>{{ $application->status?->label() ?? $application->status }}</td></tr>
            <tr>
                <th>Match Score</th>
                <td>
                    @if($application->match_score)
                        <span class="score-badge {{ $application->match_score >= 70 ? 'score-high' : ($application->match_score >= 40 ? 'score-mid' : 'score-low') }}">
                            {{ $application->match_score }}%
                        </span>
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        @if($application->candidateTest)
            <h2>Результаты теста</h2>
            <table>
                <tr><th>Балл</th><td>{{ $application->candidateTest->score }}%</td></tr>
                <tr><th>Правильных ответов</th><td>{{ $application->candidateTest->correct_answers ?? '-' }} из {{ $application->candidateTest->total_questions }}</td></tr>
                <tr><th>Время</th><td>{{ $application->candidateTest->time_spent ? gmdate('i:s', $application->candidateTest->time_spent) : '-' }}</td></tr>
            </table>
        @endif

        @if($analysis)
            <h2>AI-анализ</h2>

            @if(!empty($analysis->strengths))
                <p><strong>Сильные стороны:</strong></p>
                <ul class="list">
                    @foreach($analysis->strengths as $s)
                        <li>{{ $s }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($analysis->weaknesses))
                <p><strong>Слабые стороны:</strong></p>
                <ul class="list">
                    @foreach($analysis->weaknesses as $w)
                        <li>{{ $w }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($analysis->risks))
                <p><strong>Риски:</strong></p>
                <ul class="list">
                    @foreach($analysis->risks as $r)
                        <li>{{ $r }}</li>
                    @endforeach
                </ul>
            @endif

            @if($analysis->recommendation)
                <p style="margin-top: 10px;"><strong>Рекомендация:</strong> {{ $analysis->recommendation }}</p>
            @endif

            @if(!empty($analysis->suggested_questions))
                <h2>Рекомендуемые вопросы для интервью</h2>
                <ol class="list">
                    @foreach($analysis->suggested_questions as $q)
                        <li>{{ $q }}</li>
                    @endforeach
                </ol>
            @endif
        @endif

        @if($profile)
            <h2>Профиль кандидата</h2>
            <table>
                @if($profile->position_title)
                    <tr><th>Желаемая должность</th><td>{{ $profile->position_title }}</td></tr>
                @endif
                @if($profile->years_of_experience)
                    <tr><th>Опыт (лет)</th><td>{{ $profile->years_of_experience }}</td></tr>
                @endif
                @if($profile->education)
                    <tr><th>Образование</th><td>{{ is_array($profile->education) ? implode(', ', array_column($profile->education, 'institution')) : $profile->education }}</td></tr>
                @endif
                @if($profile->skills)
                    <tr><th>Навыки</th><td>{{ is_array($profile->skills) ? implode(', ', array_column($profile->skills, 'name')) : $profile->skills }}</td></tr>
                @endif
                @if($profile->languages)
                    <tr><th>Языки</th><td>{{ is_array($profile->languages) ? implode(', ', array_map(fn($l) => ($l['name'] ?? '') . ' (' . ($l['level'] ?? '') . ')', $profile->languages)) : $profile->languages }}</td></tr>
                @endif
            </table>
        @endif

        <div class="footer">
            {{ config('app.name') }} &middot; Конфиденциальный документ &middot; {{ now()->format('d.m.Y') }}
        </div>
    </div>
</body>
</html>
