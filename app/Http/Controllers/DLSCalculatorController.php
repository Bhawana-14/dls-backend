<?php

namespace App\Http\Controllers;

use App\Services\DLSCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DLSCalculatorController extends Controller
{
    protected $calculator;

    public function __construct(DLSCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }


    public function calculateTarget(Request $request, DlsCalculatorService $dls)
    {
        $validated = $request->validate([
            'team1.overs' => 'required|numeric|min:0',
            'team1.runs' => 'required|numeric|min:0',
            'team1.stoppages' => 'nullable|array',
            'team2.overs_start' => 'required|numeric|min:0',
            'team2.stoppages' => 'nullable|array',
            'penalty_runs' => 'nullable|integer|min:0',
            'team1.stoppages.*.oversBowled' => 'sometimes|required|numeric|min:0',
            'team1.stoppages.*.oversLost' => 'sometimes|required|numeric|min:0',
            'team1.stoppages.*.wicketsLost' => 'sometimes|required|integer|min:0|max:9',
            'team2.stoppages.*.oversBowled' => 'sometimes|required|numeric|min:0',
            'team2.stoppages.*.oversLost' => 'sometimes|required|numeric|min:0',
            'team2.stoppages.*.wicketsLost' => 'sometimes|required|integer|min:0|max:9',
        ]);

        $team1Overs = (float) $validated['team1']['overs'];
        $team1Runs = (float) $validated['team1']['runs'];
        $team1Stops = $validated['team1']['stoppages'] ?? [];

        $team2OversStart = (float) $validated['team2']['overs_start'];
        $team2Stops = $validated['team2']['stoppages'] ?? [];

        $penaltyRuns = (int) ($validated['penalty_runs'] ?? 0);

        // Cricket sanity checks
        foreach ($team2Stops as $stop) {
            if ($stop['oversBowled'] > $team2OversStart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Overs bowled cannot exceed available overs'
                ], 422);
            }
        }

        $result = $dls->calculateTarget(
            $team1Overs,
            $team1Runs,
            $team1Stops,
            $team2OversStart,
            $team2Stops,
            $penaltyRuns
        );

        return response()->json([
            'success' => true,
            'data' => [
                'over' => $team1Overs,
                'par_score' => $result['par_score'],
                'target' => $result['target'],
                'lambda' => round($result['lambda'], 4),
                'adjustment_factor' => round($result['adjFactor'], 6),
                'resource_percentage' => $result['resource_percentage']
            ],
        ]);
    }




    /**
     * Generate over-by-over par scores table
     */
    public function overByOverTable(Request $request): JsonResponse
    {
        $request->validate([
            'max_overs' => 'required|integer|min:1|max:50',
        ]);

        try {
            $table = $this->calculator->generateOverByOverTable($request->max_overs);
            return response()->json([
                'success' => true,
                'data' => $table
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate ball-by-ball par scores table
     */
    public function ballByBallTable(Request $request): JsonResponse
    {
        $request->validate([
            'max_overs' => 'required|integer|min:1|max:50',
        ]);

        try {
            $table = $this->calculator->generateBallByBallTable($request->max_overs);
            return response()->json([
                'success' => true,
                'data' => $table
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate match report
     */
    public function generateReport(Request $request): JsonResponse
    {
        $request->validate([
            'match_type' => 'required|string',
            'team1_name' => 'required|string',
            'team2_name' => 'required|string',
            'first_innings_score' => 'required|integer',
            'max_overs' => 'required|integer',
            'overs_bowled' => 'required|numeric',
            'wickets_down' => 'required|integer',
            'overs_lost' => 'required|numeric',
            'scorer_name' => 'nullable|string',
            'scorer_email' => 'nullable|email',
            'scorer_phone' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        try {
            $matchData = $request->all();
            $result = $this->calculator->calculateTarget($matchData);

            $report = [
                'match_type' => $matchData['match_type'],
                'team1_name' => $matchData['team1_name'],
                'team2_name' => $matchData['team2_name'],
                'first_innings_score' => $matchData['first_innings_score'],
                'target' => $result['target'],
                'par_score' => $result['par_score'],
                'resource_percentage' => round($result['resource_percentage'], 2),
                'scorer_details' => [
                    'name' => $matchData['scorer_name'] ?? '',
                    'email' => $matchData['scorer_email'] ?? '',
                    'phone' => $matchData['scorer_phone'] ?? '',
                    'comments' => $matchData['comments'] ?? '',
                ],
                'generated_at' => now()->toDateTimeString(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function parScoreTable(Request $request, DLSCalculatorService $dls)
    {
        // -----------------------------
        // 1. VALIDATION
        // -----------------------------
        $validated = $request->validate([
            't2_overs_total' => 'required|numeric|min:0',
            'lambda' => 'required|numeric|min:0',
            'adj_factor' => 'required|numeric|min:0',
            'target' => 'required|integer|min:0',
            'ball_by_ball' => 'nullable|boolean',
        ]);

        // -----------------------------
        // 2. NORMALIZE INPUT
        // -----------------------------
        $t2OversTotal = (float) $validated['t2_overs_total'];
        $lambda = (float) $validated['lambda'];
        $adjFactor = (float) $validated['adj_factor'];
        $target = (int) $validated['target'];
        $isBallByBall = (bool) ($validated['ball_by_ball'] ?? false);

        // -----------------------------
        // 3. GENERATE PAR SCORE TABLE
        // -----------------------------
        $table = $dls->generateParScoreTable(
            $t2OversTotal,
            $lambda,
            $adjFactor,
            $target,
            $isBallByBall
        );

        // -----------------------------
        // 4. RESPONSE
        // -----------------------------
        return response()->json([
            'success' => true,
            'data' => [
                't2_overs_total' => $t2OversTotal,
                'lambda' => round($lambda, 4),
                'adj_factor' => round($adjFactor, 6),
                'target' => $target,
                'ball_by_ball' => $isBallByBall,
                'table' => $table,
            ],
        ]);
    }
}
