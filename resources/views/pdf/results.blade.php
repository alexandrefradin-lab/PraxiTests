<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $attempt->test->name }} — Résultats</title>
<style>
body{font-family:DejaVu Sans,sans-serif;color:#0f172a;font-size:12px;line-height:1.55;margin:0;padding:30px}
h1{font-size:22px;margin:0 0 6px}
h2{font-size:15px;margin:24px 0 10px;color:#4f46e5;border-bottom:1px solid #e2e8f0;padding-bottom:4px}
.meta{color:#64748b;font-size:11px}
.dim{display:flex;justify-content:space-between;margin:6px 0}
.bar{background:#e2e8f0;height:6px;border-radius:3px;overflow:hidden;width:200px}
.fill{background:#4f46e5;height:6px}
.job{padding:10px 0;border-bottom:1px solid #f1f5f9}
.job .title{font-weight:600}
.job .sector{color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:.5px}
.job .why{margin-top:4px}
.fit{display:inline-block;background:#ecfdf5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600}
</style>
</head>
<body>
<h1>{{ $attempt->test->name }}</h1>
<p class="meta">{{ $attempt->user->name }} — {{ $attempt->completed_at?->format('d/m/Y') }}</p>

<h2>Synthèse</h2>
<div style="white-space:pre-line">{{ $attempt->result?->ai_synthesis }}</div>

@if($attempt->result?->scoring['dimensions'] ?? null)
<h2>Dimensions</h2>
@foreach($attempt->result->scoring['dimensions'] as $name => $score)
<div class="dim">
    <span style="width:140px;text-transform:capitalize">{{ $name }}</span>
    <div class="bar"><div class="fill" style="width: {{ $score }}%"></div></div>
    <span style="width:40px;text-align:right">{{ $score }}</span>
</div>
@endforeach
@endif

@if($attempt->result?->suggested_jobs)
<h2>{{ count($attempt->result->suggested_jobs) }} métiers à explorer</h2>
@foreach($attempt->result->suggested_jobs as $job)
<div class="job">
    <div style="display:flex;justify-content:space-between">
        <span class="title">{{ $job['titre'] ?? $job['title'] ?? '' }}</span>
        <span class="fit">{{ $job['fit_score'] ?? '' }}%</span>
    </div>
    <div class="sector">{{ $job['secteur'] ?? $job['sector'] ?? '' }}</div>
    <div class="why">{{ $job['pourquoi'] ?? $job['why'] ?? '' }}</div>
    @if($job['prochaine_étape'] ?? $job['next_step'] ?? null)
    <div style="color:#4f46e5;font-size:11px;margin-top:3px">→ {{ $job['prochaine_étape'] ?? $job['next_step'] }}</div>
    @endif
</div>
@endforeach
@endif

<p class="meta" style="margin-top:30px">Généré par PraxiQuest le {{ now()->format('d/m/Y H:i') }}</p>
</body>
</html>
