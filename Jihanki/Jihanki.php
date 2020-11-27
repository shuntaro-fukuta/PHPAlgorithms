<?php

/**
 * エナジードリンク 150円
 * 炭酸飲料水 140円
 * スポーツドリンク 130円
 * 缶コーヒー 120円
 * ミネラルウォーター 110円
 *
 * 投入できるのは1000円札、500円硬貨、100円硬貨、50円硬貨、10円硬貨のみ
 * 10000円札、5000円札、2000円札、5円硬貨、1円硬貨は使用不可
 * 紙幣、硬貨の最大数はX枚とする(X > 0)
 *
 * ランダムで飲料を購入する
 * ただし、飲料の合計金額がNを超えてはならない
 * 各飲料の在庫数はY本とする(Y> 0)
 *
 * 任意の金額N円(1000,500,100,50,10円(の組み合わせで成立する額))を
 * 1回のみ自販機に投入して、
 * ランダムに何か買ってゆく。
 * それが何本でもいいし、何を買ってもいい。
 * まだ何か買えたとしても、どこで打ち切るかもランダム。
 *
 * 購入した場合、投入金額、各飲料の本数とその合計金額、全飲料の合計金額、おつりを表示する
 */

$drinks = [
    "エナジードリンク"   => ["price" => 150, "stock" => mt_rand(1, 10)],
    "炭酸飲料"          => ["price" => 140, "stock" => mt_rand(1, 10)],
    "スポーツドリンク"   => ["price" => 130, "stock" => mt_rand(1, 10)],
    "コーヒー"          => ["price" => 120, "stock" => mt_rand(1, 10)],
    "ミネラルウォーター" => ["price" => 110, "stock" => mt_rand(1, 10)],
];

$input_money_counts = [
    1000 => 4,
    100 => 3,
    10 => 2,
    1 => 1,
];
$input_amount = 0;
foreach ($input_money_counts as $money => $count) {
    $input_amount += $money * $count;
}
$balance = $input_amount;

$bought_drink_counts = [];
do {
    $buyable_drink_names = [];
    foreach ($drinks as $name => $specs) {
        if ($specs['stock'] > 0 && $specs['price'] <= $balance) {
            $buyable_drink_names[] = $name;
        }
    }
    if (empty($buyable_drink_names)) break;

    $bought_drink_name = $buyable_drink_names[array_rand($buyable_drink_names)];
    $drinks[$bought_drink_name]['stock']--;
    $balance -= $drinks[$bought_drink_name]['price'];

    if (!isset($bought_drink_counts[$bought_drink_name])) {
        $bought_drink_counts[$bought_drink_name] = 0;
    }
    $bought_drink_counts[$bought_drink_name]++;

    $do_buy_again = mt_rand(0, 1);
} while ($do_buy_again);

echo '結果:', PHP_EOL;
echo '投入金額：' . $input_amount . '円', PHP_EOL;
$total_price = 0;
foreach ($bought_drink_counts as $name => $count) {
    $total_price_per_drink = $drinks[$name]['price'] * $count;
    echo $name . ':' . $count . '本 ' . $total_price_per_drink . '円', PHP_EOL;
    $total_price += $total_price_per_drink;
};
echo '合計金額:' . $total_price . '円';
