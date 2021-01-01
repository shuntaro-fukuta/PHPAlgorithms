<?php

/**
 * 別ファイルにある map.txt から迷路データを読み込んで迷路を解いてください
 * map.txt は課題一覧から取得してください
 *
 * 「開」から「終」に向かって探索し、通ったマスは「＋」で埋めて、結果を出力してください
 *
 * なお、行き止まりの道へ進んだ場合に、その通ったマスが「＋」になっているのは構いません
 * また、「開」から「終」への経路が複数ある場合に、最短経路を求める必要はありません
 */

function get_movable_coordinates($map, $directions, $squares, $coordinates) {
    $movable_coordinates = [];
    foreach ($directions as $direction) {
        $x_coordinate = $coordinates['x'];
        $y_coordinate = $coordinates['y'];

        switch ($direction) {
            case 'up':
                $y_coordinate -= 1;
                break;
            case 'right':
                $x_coordinate += 1;
                break;
            case 'down':
                $y_coordinate += 1;
                break;
            case 'left':
                $x_coordinate -= 1;
                break;
            default:
                continue 2;
        }

        if ($x_coordinate >= 0 && $y_coordinate >= 0) {
            $square = $map[$y_coordinate][$x_coordinate];
            if ($square === $squares['space'] || $square === $squares['end']) {
                $movable_coordinates[] = [
                    'x' => $x_coordinate,
                    'y' => $y_coordinate,
                ];
            }
        }
    }
    return $movable_coordinates;
}

$map = file('map.txt', FILE_IGNORE_NEW_LINES);

$directions = ['up', 'right', 'down', 'left'];

$squares = [
    'start' => '開',
    'end'   => '終',
    'space' => '　',
    'moved' => '＋',
];

foreach ($map as $index => $strings) {
    $map[$index] = preg_split('//u', $strings, null, PREG_SPLIT_NO_EMPTY);
}

foreach ($map as $y_coordinate => $line_squares) {
    $start_square = $squares['start'];
    if (in_array($start_square, $line_squares)) {
        $x_coordinate = array_search($start_square, $line_squares);

        $current_coordinates = [
            'x' => $x_coordinate,
            'y' => $y_coordinate,
        ];
    }

    $end_square = $squares['end'];
    if (in_array($end_square, $line_squares)) {
        $x_coordinate = array_search($end_square, $line_squares);

        $end_coordinates = [
            'x' => $x_coordinate,
            'y' => $y_coordinate,
        ];
    }

    if (isset($current_coordinates) && isset($end_coordinates)) break;
}

$branch_point_coordinates = [];
while (true) {
    $movable_coordinates = get_movable_coordinates($map, $directions, $squares, $current_coordinates);

    if (empty($movable_coordinates)) {
        $current_coordinates = array_pop($branch_point_coordinates);
    } else {
        if (in_array($end_coordinates, $movable_coordinates)) break;

        if (count($movable_coordinates) >= 2) {
            $branch_point_coordinates[] = $current_coordinates;
        }
        $current_coordinates = $movable_coordinates[array_rand($movable_coordinates)];
    }
    $map[$current_coordinates['y']][$current_coordinates['x']] = $squares['moved'];
}

?>

<html>
  <body>
    <table>
      <?php foreach ($map as $y_coordinate => $squares) : ?>
        <tr>
          <?php foreach ($squares as $square) : ?>
            <td>
              <?php echo $square ?>
            </td>
           <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    </table>
  </body>
</html>