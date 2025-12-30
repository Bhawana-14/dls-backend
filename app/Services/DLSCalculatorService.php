<?php

namespace App\Services;

/**
 * =========================================================
 * DLS 6.0 Calculator Service (Java-Accurate)
 * =========================================================
 * ✔ Par Score included
 * ✔ Target = Par + 1 (+ penalties)
 * ✔ Segment-wise rounding (Java trgfun)
 * ✔ Floating-point safe
 * =========================================================
 */
class DLSCalculatorService
{
    /* ============================
     * CONSTANTS (FROM JAVA)
     * ============================ */

    private const BB = 0.0365;
    private const G50_STD = 251.25;
    private const NW0 = 10.0;

    private const LMB_350 = 1.0526;
    private const LMB_450 = 1.1115;
    private const LMB_600 = 1.2294;

    private const S_CAP = 1639.9;

    private const P350_ANCHOR = [2.33, 62.0, 110.0, 150.0, 185.0, 217.0, 247.0, 275.0, 301.0, 326.0, 350.0];
    private const P450_ANCHOR = [2.56, 70.0, 127.0, 175.0, 221.0, 263.0, 303.0, 341.0, 378.0, 414.0, 450.0];
    private const P600_ANCHOR = [2.97, 83.0, 153.0, 216.0, 275.0, 332.0, 387.0, 441.0, 494.0, 547.0, 600.0];

    private const FW = [1.0, 0.8725, 0.74, 0.605, 0.4725, 0.35, 0.2425, 0.1525, 0.08, 0.0225];

    private float $Z0;

    public function __construct()
    {
        $this->Z0 = self::G50_STD / (1.0 - exp(-50.0 * self::BB));
    }

    /* ============================
     * OVERS.BALLS → DECIMAL
     * ============================ */

    private function odbToDec($val): float
    {
        $num = is_numeric($val) ? (float) $val : 0.0;
        $whole = floor($num);
        $fraction = $num - $whole;
        $balls = round($fraction * 10);
        return $whole + (min($balls, 6) / 6.0);
    }

    /* ============================
     * STANDARD RESOURCE AT 50
     * ============================ */

    private function stdResource50(float $lambda): float
    {
        return $this->Z0
            * pow($lambda, self::NW0 + 1.0)
            * (1.0 - exp(-50.0 * self::BB / pow($lambda, self::NW0)));
    }

    /* ============================
     * RESOURCE FUNCTION (Gfun)
     * ============================ */

    private function resourceValue(float $overs, float $lambda, int $wickets): float
    {
        if ($overs <= 0) return 0.0;

        $fw = self::FW[$wickets] ?? 1.0;
        $o = $overs;

        if ($wickets === 9 && $o > 5) $o = 5.0;
        if ($wickets === 8 && $o >= 12 && $o < 18) $o = 11.833333333333334;

        $r_o = $this->Z0 * (1.0 - exp(-$o * self::BB));

        $idx = max(1, min((int) ceil($o / 5.0), 10));

        $rL = ($idx === 1)
            ? $this->Z0 * (1.0 - exp(- (1.0 / 6.0) * self::BB))
            : $this->Z0 * (1.0 - exp(- ($idx - 1) * 5 * self::BB));

        $rU = $this->Z0 * (1.0 - exp(-$idx * 5 * self::BB));

        $wgt = ($rU - $rL) != 0 ? (($r_o - $rL) / ($rU - $rL)) : 1.0;

        $anchor = function ($arr) use ($idx, $wgt, $o) {
            if ($o > 50) return $arr[10] + (($arr[10] - $arr[9]) / 5.0) * ($o - 50);
            return $arr[$idx] * $wgt + $arr[$idx - 1] * (1.0 - $wgt);
        };

        $p350 = $anchor(self::P350_ANCHOR);
        $p450 = $anchor(self::P450_ANCHOR);
        $p600 = $anchor(self::P600_ANCHOR);
        $pScap = self::S_CAP * ($o / 50.0);

        $rLmb = $this->stdResource50($lambda);
        $r350 = $this->stdResource50(self::LMB_350);
        $r450 = $this->stdResource50(self::LMB_450);
        $r600 = $this->stdResource50(self::LMB_600);

        if ($lambda <= 1.0) {
            $interp = $r_o;
        } elseif ($lambda < self::LMB_350) {
            $interp = $r_o * (($r350 - $rLmb) / ($r350 - self::G50_STD))
                + $p350 * (($rLmb - self::G50_STD) / ($r350 - self::G50_STD));
        } elseif ($lambda < self::LMB_450) {
            $interp = $p350 * (($r450 - $rLmb) / ($r450 - $r350))
                + $p450 * (($rLmb - $r350) / ($r450 - $r350));
        } elseif ($lambda < self::LMB_600) {
            $interp = $p450 * (($r600 - $rLmb) / ($r600 - $r450))
                + $p600 * (($rLmb - $r450) / ($r600 - $r450));
        } elseif ($lambda < 3.0) {
            $interp = $p600 * ((self::S_CAP - $rLmb) / (self::S_CAP - $r600))
                + $pScap * (($rLmb - $r600) / (self::S_CAP - $r600));
        } else {
            $interp = $rLmb * ($o / 50.0);
        }

        $ratio = $fw * (1.0 - exp(-$o * self::BB / $fw))
            / (1.0 - exp(-$o * self::BB));

        return $interp * $ratio;
    }

    /* ============================
     * LAMBDA SOLVER
     * ============================ */

    private function calculateLambda(float $overs, float $runs, array $stops): float
    {
        $lambda = 1.0;

        $res = function ($lmb) use ($overs, $stops) {
            $r = $this->resourceValue($overs, $lmb, 0);
            $cur = $overs;

            foreach ($stops as $s) {
                $b = $this->odbToDec($s['oversBowled']);
                $l = $this->odbToDec($s['oversLost']);
                $w = min(max((int) $s['wicketsLost'], 0), 9);

                $r -= $this->resourceValue($cur - $b, $lmb, $w);
                $r += $this->resourceValue($cur - $b - $l, $lmb, $w);
                $cur -= $l;
            }
            return $r;
        };

        if ($res(1.0) >= $runs) return 1.0;

        while ($lambda <= 10.0) {
            if ($res($lambda) >= $runs) break;
            $lambda += 0.0001;
        }

        return $lambda;
    }

    /* ============================
     * FINAL TARGET + PAR SCORE
     * ============================ */

    public function calculateTarget(
        float $t1Overs,
        int $t1Runs,
        array $t1Stops,
        float $t2Overs,
        array $t2Stops,
        int $penaltyRuns = 0
    ): array {
        $lambda = $this->calculateLambda($t1Overs, $t1Runs, $t1Stops);

        $calcFloat = function ($initial, $stops) use ($lambda) {
            $r = $this->resourceValue($initial, $lambda, 0);
            $cur = $initial;

            foreach ($stops as $s) {
                $b = $this->odbToDec($s['oversBowled']);
                $l = $this->odbToDec($s['oversLost']);
                $w = min(max((int) $s['wicketsLost'], 0), 9);

                $r -= $this->resourceValue($cur - $b, $lambda, $w);
                $r += $this->resourceValue($cur - $b - $l, $lambda, $w);
                $cur -= $l;
            }
            return $r;
        };

        $r1 = $calcFloat($t1Overs, $t1Stops);
        $adj = $r1 == 0 ? 1.0 : $t1Runs / $r1;

        $par = round($adj * $this->resourceValue($t2Overs, $lambda, 0));
        $cur = $t2Overs;

        foreach ($t2Stops as $s) {
            $b = $this->odbToDec($s['oversBowled']);
            $l = $this->odbToDec($s['oversLost']);
            $w = min(max((int) $s['wicketsLost'], 0), 9);

            $par -= round($adj * $this->resourceValue($cur - $b, $lambda, $w));
            $par += round($adj * $this->resourceValue($cur - $b - $l, $lambda, $w));
            $cur -= $l;
        }

        $par += $penaltyRuns;
        $target = $par + 1;
        $resourceAtLambda1 =
            $this->resourceValue($t2Overs, 1.0, 0);

        $resourcePct = ($resourceAtLambda1 / self::G50_STD) * 100;

        return [
            'par_score' => $par,
            'target' => $target,
            'lambda' => $lambda,
            'adjFactor' => $adj,
            'resource_percentage' => round($resourcePct, 2),
            // 'resource_percentage' => round(($calcFloat($t2Overs, $t2Stops) / self::G50_STD) * 100, 2),
        ];
    }

    private function decToOdb(float $val): string
    {
        $whole = floor($val);
        $fraction = $val - $whole;
        $balls = round($fraction * 6);

        if ($balls === 6) {
            return (string) ($whole + 1);
        }

        return $balls === 0
            ? (string) $whole
            : $whole . '.' . $balls;
    }

    /**
     * Generate DLS Par Score Table
     *
     * @param float $t2OversTotal  Total overs available to Team 2
     * @param float $lambda        DLS lambda
     * @param float $adjFactor     Adjustment factor
     * @param int   $target        Target score
     * @param bool  $isBallByBall  True = per ball, False = per over
     *
     * @return array
     */
    public function generateParScoreTable(
        float $t2OversTotal,
        float $lambda,
        float $adjFactor,
        int $target,
        bool $isBallByBall = false
    ): array {
        $table = [];

        // Number of steps (avoids floating point drift)
        $steps = $isBallByBall
            ? (int) round($t2OversTotal * 6)
            : (int) round($t2OversTotal);

        for ($i = 0; $i <= $steps; $i++) {
            $remainingOvers = $isBallByBall
                ? ($steps - $i) / 6.0
                : ($steps - $i);

            // Fix floating precision (Java-style)
            $remFixed = round($remainingOvers * 60) / 60;

            $row = [
                'oversBowled' => $this->decToOdb($t2OversTotal - $remFixed),
                'oversRemaining' => $this->decToOdb($remFixed),
                'scores' => [],
            ];

            // Par score for wickets 0–9
            for ($w = 0; $w < 10; $w++) {
                // IMPORTANT: round AFTER subtraction (Java trgfun behavior)
                $resRemaining =
                    $adjFactor * $this->resourceValue($remFixed, $lambda, $w);

                $par = round($target - 1 - $resRemaining);

                $row['scores'][] = $par < 0 ? 0 : $par;
            }

            $table[] = $row;
        }

        return $table;
    }
}
