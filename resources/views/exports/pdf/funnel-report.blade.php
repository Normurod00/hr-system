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
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table th, table td { padding: 8px 10px; text-align: left; border: 1px solid #ddd; font-size: 11px; }
        table th { background: #f5f5f5; font-weight: bold; }
        .stat-row { display: flex; gap: 20px; margin-bottom: 16px; }
        .stat-card { flex: 1; background: #f9f9f9; padding: 12px; border-radius: 8px; text-align: center; border: 1px solid #eee; }
        .stat-card .value { font-size: 24px; font-weight: bold; color: #E52716; }
        .stat-card .label { font-size: 10px; color: #666; margin-top: 4px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчёт по воронке рекрутинга</h1>
        <p>{{ config('app.name') }} | Период: {{ $dateFrom->format('d.m.Y') }} — {{ $dateTo->format('d.m.Y') }}</p>
    </div>

    <div class="content">
        <h2>Сводка</h2>
        <table>
            <tr>
                <th>Всего заявок</th>
                <th>На рассмотрении</th>
                <th>Приглашены</th>
                <th>Приняты</th>
                <th>Отклонены</th>
                <th>Конверсия</th>
            </tr>
            <tr>
                <td style="text-align:center; font-weight:bold;">{{ $funnel['new'] }}</td>
                <td style="text-align:center;">{{ $funnel['in_review'] }}</td>
                <td style="text-align:center;">{{ $funnel['invited'] }}</td>
                <td style="text-align:center; color:#22c55e; font-weight:bold;">{{ $funnel['hired'] }}</td>
                <td style="text-align:center; color:#ef4444;">{{ $funnel['rejected'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ $funnel['new'] > 0 ? round($funnel['hired'] / $funnel['new'] * 100, 1) : 0 }}%</td>
            </tr>
        </table>

        @if(!empty($vacancyStats))
            <h2>По вакансиям</h2>
            <table>
                <tr>
                    <th>Вакансия</th>
                    <th style="text-align:center;">Заявки</th>
                    <th style="text-align:center;">Приглашены</th>
                    <th style="text-align:center;">Приняты</th>
                    <th style="text-align:center;">Ср. Match Score</th>
                </tr>
                @foreach($vacancyStats as $vs)
                    <tr>
                        <td>{{ $vs['title'] }}</td>
                        <td style="text-align:center;">{{ $vs['total'] }}</td>
                        <td style="text-align:center;">{{ $vs['invited'] }}</td>
                        <td style="text-align:center;">{{ $vs['hired'] }}</td>
                        <td style="text-align:center;">{{ $vs['avg_score'] ? round($vs['avg_score']) . '%' : '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <div class="footer">
            {{ config('app.name') }} &middot; Конфиденциальный документ &middot; Сформирован: {{ now()->format('d.m.Y H:i') }}
        </div>
    </div>
</body>
</html>
