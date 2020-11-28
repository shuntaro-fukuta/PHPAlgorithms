<?php

/**
 * n人の生徒がいます
 * 年にn回(3回)テストが開催されます
 * 教科はn個(算数、国語、理科、社会、英語)あります
 * 点数はランダムで0〜100点
 * ランクを適当に定義する（300〜250=A,249〜200=B,...など）
 *
 * 結果を表示してください
 * 各テストごとの生徒ごとの教科ごとの点数を表で表示
 * 生徒ごとの教科ごとの年間合計点数を表示
 * 上記年間合計点数によるランク表示も合わせて表示
 *
 * 表の形式はいい感じで（見たときに分かりやすく）
 */

$students = ['一郎', '二郎', '三郎'];

$exam_number = 3;

$subjects = ['算数', '国語', '理科', '社会', '英語'];

$subject_min_score      = 0;
$subject_max_score      = 100;
$year_subject_max_score = $subject_max_score * $exam_number;

$ranks             = range('A', 'F');
$rank_change_score = round($year_subject_max_score / count($ranks));
$base_score        = $year_subject_max_score - $rank_change_score;
$rank_base_scores  = [];
foreach ($ranks as $rank) {
    $rank_base_scores[$rank] = $base_score;
    $base_score             -= $rank_change_score;

    if ($rank === $ranks[array_key_last($ranks)]) {
        $rank_base_scores[$rank] = $subject_min_score;
    }
}

$each_exam_scores = [];
for ($i = 1; $i <= $exam_number; $i++) {
    $each_exam_scores[$i] = [];
    foreach ($students as $student) {
        $each_exam_scores[$i][$student] = [];
        foreach ($subjects as $subject) {
            $each_exam_scores[$i][$student][$subject] = rand($subject_min_score, $subject_max_score);
        }
    }
}

$year_grades = [];
foreach ($students as $student) {
    $year_grades[$student] = [];
    foreach ($subjects as $subject) {
        $year_grades[$student][$subject] = [];

        $total_score = 0;
        foreach ($each_exam_scores as $scores) {
            $total_score += $scores[$student][$subject];
        }

        $year_grades[$student][$subject]['rank'] = null;
        foreach ($rank_base_scores as $rank => $base_score) {
            if ($total_score >= $base_score) {
                $year_grades[$student][$subject] = [
                    'total_score' => $total_score,
                    'rank'        => $rank,
                ];
                break;
            }
        }
    }
}

?>

<html>
  <body>
    <table border="1" cellpadding="3">
        <tr>
          <th colspan="<?php echo count($subjects) + 1 ?>" bgcolor="#AAA">各テストごと</th>
        </tr>
      <?php foreach ($each_exam_scores as $exam_number => $results) : ?>
        <tr>
          <th colspan="<?php echo count($subjects) + 1 ?>" bgcolor="#DDD">第<?php echo $exam_number ?>回</th>
        </tr>
        <tr align="center">
          <th>点数</th>
          <?php foreach ($subjects as $subject) : ?>
            <th><?php echo $subject ?></th>
          <?php endforeach ?>
        </tr>
        <?php foreach ($results as $student => $scores) : ?>
          <tr align="center">
            <th>
              <?php echo $student ?>
            </th>
            <?php foreach ($scores as $score) : ?>
              <td><?php echo $score ?></td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
      <?php endforeach ?>
    </table>
    <br>
    <table border="1">
      <tr>
        <th colspan="<?php echo count($subjects) + 1 ?>" bgcolor="#AAA">年間合計</th>
      </tr>
      <tr>
        <th>点数/ランク</th>
        <?php foreach ($subjects as $subject) : ?>
          <th><?php echo $subject ?></th>
        <?php endforeach ?>
      </tr>
      <?php foreach ($year_grades as $student => $subject_grades) : ?>
        <tr align="center">
          <th><?php echo $student ?></th>
          <?php foreach ($subject_grades as $grade) : ?>
            <td>
              <?php echo $grade['total_score'] ?>
              (<?php echo $grade['rank'] ?>)
            </td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </table>
  </body>
</html>
