<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\TestAttempt;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function show(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result');

        return Inertia::render('Candidate/ResultsShow', [
            'attempt' => $attempt,
            'result'  => $attempt->result,
            'ai_pending' => !$attempt->result?->ai_synthesis,
        ]);
    }

    public function pdf(TestAttempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);
        $attempt->load('test', 'result', 'user.profile');

        $pdf = Pdf::loadView('pdf.results', ['attempt' => $attempt]);
        return $pdf->download("praxitests-results-{$attempt->id}.pdf");
    }
}
