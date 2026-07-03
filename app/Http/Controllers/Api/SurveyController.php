<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * GET /api/survey/stats
     */
    public function stats()
    {
        $global = Survey::select(
            DB::raw('ROUND(AVG(doctor_rating), 2) as avg_doc'),
            DB::raw('ROUND(AVG(service_rating), 2) as avg_svc'),
            DB::raw('ROUND(AVG(facility_rating), 2) as avg_fac'),
            DB::raw('ROUND(AVG(overall_rating), 2) as avg_all'),
            DB::raw('COUNT(*) as total')
        )->first();

        $perPoly = Poly::leftJoin('queues', 'polies.id', '=', 'queues.poly_id')
            ->leftJoin('surveys', 'queues.id', '=', 'surveys.queue_id')
            ->select(
                'polies.id',
                'polies.name',
                'polies.color',
                DB::raw('ROUND(AVG(surveys.overall_rating), 2) as avg'),
                DB::raw('COUNT(surveys.id) as cnt')
            )
            ->groupBy('polies.id')
            ->orderBy('avg', 'desc')
            ->get();

        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $distData = Survey::select('overall_rating', DB::raw('COUNT(*) as n'))
            ->groupBy('overall_rating')
            ->pluck('n', 'overall_rating')
            ->toArray();
        foreach ($distData as $rating => $count) {
            $dist[(int) $rating] = $count;
        }

        return response()->json([
            'success' => true,
            'global' => $global,
            'per_poly' => $perPoly,
            'distribution' => $dist,
        ]);
    }

    /**
     * GET /api/survey/trend?months=6
     */
    public function trend(Request $request)
    {
        $months = (int) ($request->months ?? 6);

        $trend = Survey::select(
            DB::raw("DATE_FORMAT(created_at, '%b %Y') as mon"),
            DB::raw('ROUND(AVG(overall_rating), 2) as avg'),
            DB::raw('COUNT(*) as cnt')
        )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw('MIN(created_at)'), 'desc')
            ->limit($months)
            ->get()
            ->reverse()
            ->values();

        return response()->json(['success' => true, 'trend' => $trend]);
    }

    /**
     * GET /api/survey/list (admin only)
     */
    public function list(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $limit = (int) ($request->limit ?? 20);
        $offset = (int) ($request->offset ?? 0);
        $polyId = (int) ($request->poly_id ?? 0);

        $query = Survey::with(['patient', 'queue.poly'])->orderBy('created_at', 'desc');

        if ($polyId) {
            $query->whereHas('queue', function ($q) use ($polyId) {
                $q->where('poly_id', $polyId);
            });
        }

        $surveys = $query->limit($limit)->offset($offset)->get();

        return response()->json(['success' => true, 'surveys' => $surveys, 'count' => $surveys->count()]);
    }
}