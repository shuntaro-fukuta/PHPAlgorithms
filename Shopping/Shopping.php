<?php

/**
 * ある商品群があります
 * 商品は名前と金額
 *
 * ある買う人がいます
 * 名前と都道府県
 *
 * その人が商品をN個買います
 * 買う商品はランダムで購入数もランダム 1個以上
 *
 * 送料は500円です
 * ただ、購入数が5以上の場合は1000円になります
 * また、都道府県が沖縄県と北海道はプラス1000円になります
 *
 * 買った商品ごとの商品名、個数と、金額
 * 小計、消費税、送料（消費税かからない）、合計金額を表示してください
 *
 * 消費税が変わる事を考慮しましょう
 */

date_default_timezone_set('Asia/Tokyo');
$sales_tax_list = [
    [
        'start_date' => '1989/04/01',
        'rate'       => 0.03,
    ],
    [
        'start_date' => '1997/04/01',
        'rate'       => 0.05,
    ],
    [
        'start_date' => '2014/04/01',
        'rate'       => 0.08,
    ],
    [
        'start_date' => '2019/10/01',
        'rate'       => 0.1,
    ],
];

$sales_tax_list_count = count($sales_tax_list);
$sales_tax_rate       = 0;
$current_date         = strtotime('now');
for ($i = $sales_tax_list_count - 1; $i >= 0; $i--) {
    if ($current_date >= strtotime($sales_tax_list[$i]['start_date'])) {
        $sales_tax_rate = $sales_tax_list[$i]['rate'];
        break;
    }
}

$products = [
    'Tシャツ'    => ['price' => 1500],
    'ポロシャツ' => ['price' => 2000],
    'チノパン'   => ['price' => 3500],
    'ジーンズ'   => ['price' => 4000],
];

$customer = [
    'name'       => 'fukuta',
    'prefecture' => '北海道',
];

$shopping_cart = [];

$do_buy = true;
while ($do_buy) {
    $product_name = array_rand($products);

    if (!isset($shopping_cart[$product_name])) {
        $shopping_cart[$product_name]['count'] = 1;
    } else {
        $shopping_cart[$product_name]['count']++;
    }

    $do_buy = (rand(0, 2) !== 0);
}

$total_product_count  = 0;
$subtotal             = 0;
foreach ($shopping_cart as $product_name => $order_details) {
    $total_product_count += $order_details['count'];

    $shopping_cart[$product_name]['each_total_price'] =
        $products[$product_name]['price'] * $order_details['count'];

    $subtotal += $shopping_cart[$product_name]['each_total_price'];
}

if ($total_product_count < 5) {
    $shipping_fee = 500;
} else {
    $shipping_fee = 1000;
}

$additional_shipping_fee_prefectures = ['沖縄県', '北海道'];
if (in_array($customer['prefecture'], $additional_shipping_fee_prefectures)) {
    $shipping_fee += 1000;
}

$sales_tax   = $subtotal * $sales_tax_rate;
$total_price = $subtotal + $shipping_fee + $sales_tax;

?>

<html>
  <body>
    <table border="1">
      <tr>
        <th>商品名</th>
        <th>個数</th>
        <th>金額</th>
      </tr>
      <?php foreach ($shopping_cart as $product_name => $order_details) : ?>
        <tr>
          <td><?php echo $product_name ?></td>
          <td><?php echo $order_details['count'] ?></td>
          <td><?php echo $order_details['each_total_price'] ?>円</td>
        </tr>
      <?php endforeach ?>
     </table>
     <br>
     <table border="1">
       <tr>
         <th>小計</th>
         <th>消費税</th>
         <th>送料</th>
         <th bgcolor="#CCC">合計金額</th>
       </tr>
       <tr>
         <td><?php echo $subtotal ?>円</td>
         <td><?php echo $sales_tax ?>円</td>
         <td><?php echo $shipping_fee ?>円</td>
         <td><?php echo $total_price ?>円</td>
       </tr>
     </table>
  </body>
</html>
