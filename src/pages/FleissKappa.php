<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);


class FleissKappa {
    
    /**
     * حساب معامل فليس كابا
     * @param array $data مصفوفة تحتوي على عدد الإجابات لكل خيار لكل سؤال
     * الشكل المتوقع: [question_id => ['A' => count, 'B' => count, ...]]
     */
    public static function calculate($data) {
        if (empty($data)) return 0;

        $N = count($data); // عدد الأسئلة (Subjects)
        $n = 0; // عدد المقيمين (Students) - نأخذه من أول سؤال كمتوسط
        $k = 0; // عدد الفئات (Options A, B, C, D...)

        // 1. تجهيز الفئات (Categories)
        $categories = [];
        foreach ($data as $row) {
            $row_n = array_sum($row);
            if ($row_n > $n) $n = $row_n; // نأخذ أكبر عدد مشاركين
            foreach (array_keys($row) as $cat) {
                $categories[$cat] = true;
            }
        }
        $k = count($categories);
        if ($n < 2) return 0; // لا يمكن الحساب بمشارك واحد

        // 2. حساب P_i (مدى الاتفاق لكل سؤال)
        $P_i_sum = 0;
        $pj_sums = array_fill_keys(array_keys($categories), 0);
        
        foreach ($data as $row) {
            $sum_sq = 0;
            $row_total = array_sum($row);
            
            foreach ($categories as $cat => $val) {
                $count = $row[$cat] ?? 0;
                $sum_sq += $count * $count;
                $pj_sums[$cat] += $count;
            }
            
            // معادلة P_i
            // (1 / (n(n-1))) * (SUM(nij^2) - n)
            if ($row_total > 1) {
                $P_i = (1 / ($row_total * ($row_total - 1))) * ($sum_sq - $row_total);
                $P_i_sum += $P_i;
            }
        }

        // 3. حساب P_bar (متوسط الاتفاق الملاحظ)
        $P_bar = $P_i_sum / $N;

        // 4. حساب P_e_bar (الاتفاق بالصدفة)
        $P_e_sum_sq = 0;
        $total_assignments = $N * $n;
        
        foreach ($pj_sums as $pj) {
            $prob = $pj / $total_assignments;
            $P_e_sum_sq += $prob * $prob;
        }
        $P_e_bar = $P_e_sum_sq;

        // 5. حساب الكابا النهائية
        // Kappa = (P_bar - P_e_bar) / (1 - P_e_bar)
        if ($P_e_bar == 1) return 1; // اتفاق تام
        
        $kappa = ($P_bar - $P_e_bar) / (1 - $P_e_bar);

        return round($kappa, 3);
    }

    public static function interpret($kappa) {
        if ($kappa < 0) return ["اتفاق ضعيف جداً", "#ef4444"];
        if ($kappa <= 0.20) return ["اتفاق طفيف", "#f97316"];
        if ($kappa <= 0.40) return ["اتفاق مقبول", "#eab308"];
        if ($kappa <= 0.60) return ["اتفاق متوسط", "#3b82f6"];
        if ($kappa <= 0.80) return ["اتفاق قوي", "#10b981"];
        return ["اتفاق شبه تام", "#059669"];
    }
}
?>