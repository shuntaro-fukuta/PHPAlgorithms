<?php
/**
 * 箱
 *  大きさ　10~20
 *  空き容量1未満で発送
 *  100個
 *
 * 荷物
 *  大きさ　1~5
 *  無限で出てくる
 *
 * ストック場
 *  荷物が入らない場合、ここに置く
 *  箱に入れられる荷物がある場合は、最初にここから入れられるだけ入れる
 *
 * 終了条件
 * 　発送数100
 *
 */
$boxes = [];
for ($i = 1; $i <= 100; $i++) {
    $boxes[$i] = [
        'capacity' => rand(10, 20),
        'baggages' => [],
    ];
}

$shipped_boxes  = [];
$stock_baggages = [];

foreach ($boxes as $box_number => $box) {
    $space = $box['capacity'];

    foreach ($stock_baggages as $index => $baggage) {
        if ($space >= $baggage['size']) {
            $box['baggages'][] = $baggage;
            $space            -= $baggage['size'];
            unset($stock_baggages[$index]);
        }
    }

    while ($space >= 1) {
        $baggage['size'] = rand(1, 5);

        if ($space >= $baggage['size']) {
            $box['baggages'][] = $baggage;
            $space            -= $baggage['size'];
        } else {
            $stock_baggages[] = $baggage;
        }
    }

    $shipped_boxes[$box_number] = $box;
}

?>

<html>
  <body>
    <table border="1">
      <tr>
        <th>No</th>
        <th>容量</th>
        <th>荷物</th>
      </tr>
      <?php foreach ($shipped_boxes as $box_number => $box) : ?>
        <tr align="center">
          <td><?php echo $box_number ?></td>
          <td><?php echo $box['capacity'] ?></td>
          <td>
            <?php foreach ($box['baggages'] as $baggage) : ?>
              <?php echo $baggage['size'] ?>
            <?php endforeach ?>
          </td>
        </tr>
      <?php endforeach ?>
    </table>
    <?php if (count($stock_baggages)) : ?>
      <table border="1">
        <tr>
          <th>残ったストック場の荷物</th>
        </tr>
        <tr>
          <td>
            <?php foreach ($stock_baggages as $baggage) : ?>
              <?php echo $baggage['size'] ?>
            <?php endforeach ?>
          </td>
        </tr>
      </table>
    <?php endif ?>
    </p>
  </body>
</html>
