<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyResultController extends Controller
{
    /**
     * Halaman terima kasih setelah survei.
     */
    public function index(Request $request)
    {
        $submitted = (bool) $request->submitted;
        $name = $request->name ?? 'Pasien';

        // Statistik global
        $stats = Survey::select(
            \Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 1) as avg'),
            \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
        )->first();

        return view('survey.results', compact('submitted', 'name', 'stats'));
    }
}