<?php

/**
 * 製品A
 *   部品A2個と部品B1個からできています。
 * 製品B
 *   部品C3個と部品D2個からできています。
 * 製品C
 *   部品B1個と部品D1個からできています。
 *
 * 製品Aと製品Bと製品Cをランダムで発注します。
 * 部品にはそれぞれ在庫があり製品が作れなくなるまで製造をします。
 *
 * 最後に以下を出力します。
 *
 * 製造前の各部品の在庫数
 * 製品の発注数
 * 製造した製品の個数
 * 製造後の各部品の在庫数
 */

function get_makeable_products($parts_stocks, $product_components) {
    $makeable_products = [];
    foreach ($product_components as $product_name => $required_parts) {
        $can_make = true;
        foreach ($required_parts as $parts_name => $required_number) {
            if ($parts_stocks[$parts_name] < $required_number) {
                $can_make = false;
                break;
            }
        }

        if ($can_make) {
            $makeable_products[] = $product_name;
        }
    }

    return $makeable_products;
}

$product_components = [
    '製品A' => [
        '部品A' => 2,
        '部品B' => 1,
    ],
    '製品B' => [
        '部品C' => 3,
        '部品D' => 2,
    ],
    '製品C' => [
        '部品B' => 1,
        '部品D' => 1,
    ],
];

$products = array_keys($product_components);

$parts_stocks = [
    '部品A' => 11,
    '部品B' => 21,
    '部品C' => 19,
    '部品D' => 15,
];

$remember_parts_stocks = $parts_stocks;

$ordered_products = [];
$made_products    = [];
foreach ($products as $product) {
    $ordered_products[$product]['number'] = 0;
    $made_products[$product]['number']    = 0;
}

$makeable_products = get_makeable_products($parts_stocks, $product_components);
while (count($makeable_products) > 0) {
    $ordered_product_name = $products[array_rand($products)];
    $ordered_products[$ordered_product_name]['number']++;

    if (in_array($ordered_product_name, $makeable_products)) {
        $required_parts = $product_components[$ordered_product_name];
        foreach ($required_parts as $parts_name => $required_number) {
            $parts_stocks[$parts_name] -= $required_number;
        }

        $made_products[$ordered_product_name]['number']++;
    }

    $makeable_products = get_makeable_products($parts_stocks, $product_components);
}

?>

<html>
  <body>
    <table border="1">
      <tr bgcolor="#CCC">
        <th>製造前の部品の個数</th>
        <th>製品の発注数</th>
        <th>製造した製品の個数</th>
        <th>製造後の各部品の在庫数</th>
      </tr>
      <tr>
        <td align="center">
          <?php foreach ($remember_parts_stocks as $parts_name => $stock) : ?>
            <?php echo "{$parts_name} : {$stock}" ?><br>
          <?php endforeach ?>
        </td>
        <td align="center">
          <?php foreach ($ordered_products as $product_name => $ordered_product) : ?>
            <?php echo "{$product_name} : {$ordered_product['number']}" ?><br>
          <?php endforeach ?>
        </td>
        <td align="center">
          <?php foreach ($made_products as $product_name => $made_product) : ?>
            <?php echo "{$product_name} : {$made_product['number']}" ?><br>
          <?php endforeach ?>
        </td>
        <td align="center">
          <?php foreach ($parts_stocks as $parts_name => $stock) : ?>
            <?php echo "{$parts_name} : {$stock}" ?><br>
          <?php endforeach ?>
        </td>
       </tr>
    </table>
  </body>
</html>
