<?php

/**
 * カテゴリがn個あります
 * 各カテゴリはn個の商品を持ちます
 * 商品は月ごとに在庫を確保します
 * 2017/11月 item_a の在庫は100個、とか
 * 商品は日々n個売れたり売れなかったりします
 * 次月の初日、以下のとおりに在庫を確保します
 *   前月に90%以上売れた場合、120%にする
 *   前月に80%以上売れた場合、100%にする（前月の在庫数が100個の場合は100個にする）
 *   前月に60%以上売れた場合、前月頭の20%分を仕入れる
 *   前月に40%以上売れた場合、前月頭の12%分を仕入れる
 *   前月に20%以上売れた場合、前月頭の5%分を仕入れる
 *
 * 結果表示分
 *   4ヶ月分
 *   月ごと
 *     カテゴリごと
 *       商品ごと
 *         在庫数（繰越数と新規確保数）と
 *         1日ごとの売れた数
 *         その月に何個・何%売れたか
 *       何円売れたか
 *     何円売れたか
 *
 * 前月の初期在庫量 = 当初の在庫 + 入荷量
 *  新規入荷量 = 前月の初期在庫量 * 消化率に応じた%
 */

function get_month_initial_stock($stocks, $year, $month, $item_id) {
    return $stocks[$year][$month][$item_id]['over_month_stock'] + $stocks[$year][$month][$item_id]['additional_stock'];
}

function get_additional_stock($rate, $initial_stock, $over_month_stock) {
    if ($rate >= 0.9) {
        return (int) round($initial_stock * 1.2) - $over_month_stock;
    } elseif ($rate >= 0.8) {
        return $initial_stock * 1.0 - $over_month_stock;
    } elseif ($rate >= 0.6) {
        return (int) round($initial_stock * 0.2);
    } elseif ($rate >= 0.4) {
        return (int) round($initial_stock * 0.12);
    } elseif ($rate >= 0.2) {
        return (int) round($initial_stock * 0.05);
    } else {
        return 0;
    }
}

$categories = [
    1 => ['name' => '食料品'],
    2 => ['name' => '飲料品'],
];

$items = [
    1 => ['category_id' => 1, 'name' => '肉',     'stock' => 300, 'price'=> 240,],
    2 => ['category_id' => 1, 'name' => '魚',     'stock' => 300, 'price'=> 240,],
    3 => ['category_id' => 2, 'name' => 'お茶',   'stock' => 300, 'price'=> 240,],
    4 => ['category_id' => 2, 'name' => 'コーラ', 'stock' => 300, 'price'=> 240,],
    5 => ['category_id' => 2, 'name' => '水',     'stock' => 300, 'price'=> 100,],
];

$start_year      = 2018;
$start_month     = 11;
$business_period = 4;

$year                   = $start_year;
$month                  = $start_month;
$business_months        = [];
$business_months[$year] = [];
for ($i = 1; $i <= $business_period; $i++, $month++) {
    if ($month > 12) {
        $month = 1;
        $year++;
        $business_months[$year] = [];
    }
    $business_months[$year][] = $month;
}

$stocks                 = [];
$daily_sales_numbers    = [];
$monthly_sales          = [];
$category_sales_amounts = [];
$monthly_sales_amounts  = [];
foreach ($business_months as $this_year => $months) {
    $stocks[$this_year]                 = [];
    $daily_sales_numbers[$this_year]    = [];
    $monthly_sales[$this_year]          = [];
    $category_sales_amounts[$this_year] = [];
    $monthly_sales_amounts[$this_year]  = [];

    foreach ($months as $this_month) {
        $stocks[$this_year][$this_month]                 = [];
        $daily_sales_numbers[$this_year][$this_month]    = [];
        $monthly_sales[$this_year][$this_month]          = [];
        $category_sales_amounts[$this_year][$this_month] = [];

        $month_last_day = (int) date('j', strtotime("last day of {$this_year}-{$this_month}"));
        for ($day = 1; $day <= $month_last_day; $day++) {
            if ($day === 1) {
                if ($this_year === $start_year && $this_month === $start_month) {
                    foreach ($items as $item_id => $item) {
                        $stocks[$this_year][$this_month][$item_id] = [];

                        $stocks[$this_year][$this_month][$item_id]['over_month_stock'] = $item['stock'];
                        $stocks[$this_year][$this_month][$item_id]['additional_stock'] = 0;
                    }
                } else {
                    if ($this_month === 1) {
                        $year  = $this_year - 1;
                        $month = 12;
                    } else {
                        $year  = $this_year;
                        $month = $this_month - 1;
                    }
                    foreach (array_keys($items) as $item_id) {
                        $over_month_stock         = $items[$item_id]['stock'];
                        $last_month_initial_stock = get_month_initial_stock($stocks, $year, $month, $item_id);

                        $additional_stock = get_additional_stock(
                            $monthly_sales[$year][$month][$item_id]['rate'],
                            $last_month_initial_stock,
                            $over_month_stock
                        );

                        $stocks[$this_year][$this_month][$item_id]['over_month_stock'] = $over_month_stock;
                        $stocks[$this_year][$this_month][$item_id]['additional_stock'] = $additional_stock;
                        $items[$item_id]['stock']                                     += $additional_stock;
                    }
                }
            }

            foreach ($items as $item_id => $item) {
                $item_bought_number = rand(0, (int) round($items[$item_id]['stock'] * 0.09));
                $day_total_price    = $item_bought_number * $item['price'];

                $items[$item_id]['stock']                                    -= $item_bought_number;
                $daily_sales_numbers[$this_year][$this_month][$item_id][$day] = $item_bought_number;
            }

            if ($day === $month_last_day) {
                foreach ($items as $item_index => $item) {
                    $month_sales_number =
                        array_sum($daily_sales_numbers[$this_year][$this_month][$item_index]);
                    $month_sales_price  = $month_sales_number * $item['price'];

                    $monthly_sales[$this_year][$this_month][$item_index]['number'] = $month_sales_number;
                    $monthly_sales[$this_year][$this_month][$item_index]['rate']   =
                        round($month_sales_number / get_month_initial_stock($stocks, $this_year, $this_month, $item_index), 2);

                    $category_id = $item['category_id'];
                    if (isset($category_sales_amounts[$this_year][$this_month][$category_id]) === false) {
                        $category_sales_amounts[$this_year][$this_month][$category_id] = 0;
                    }
                    $category_sales_amounts[$this_year][$this_month][$category_id] += $month_sales_price;
                }
                $monthly_sales_amounts[$this_year][$this_month] = array_sum($category_sales_amounts[$this_year][$this_month]);
            }
        }
    }
}

?>

<html>
  <body>
    <?php foreach ($business_months as $year => $months) : ?>
      <?php foreach ($months as $month) : ?>
        <table border="1" cellspacing="5">
          <tr><th colspan="6" bgcolor="#808080"><?php echo "{$year}年 {$month}月" ?></th></tr>
          <?php foreach ($categories as $category_id => $category) : ?>
            <tr><th width="300" colspan="6" bgcolor="#a9a9a9"><?php echo $category['name'] ?></th></tr>
            <?php foreach ($items as $item_id => $item) : ?>
              <?php if ($category_id === $item['category_id']) : ?>
                <tr bgcolor="#dcdcdc">
                  <th bgcolor="#a9a9a9">商品名</th>
                  <th>在庫繰越数</th>
                  <th>在庫新規確保数</th>
                  <th>月に売れた個数</th>
                  <th>月に売れた％</th>
                </tr>
                <tr>
                  <th><?php echo $item['name'] ?></th>
                  <td align="center"><?php echo $stocks[$year][$month][$item_id]['over_month_stock'] ?></td>
                  <td align="center"><?php echo $stocks[$year][$month][$item_id]['additional_stock'] ?></td>
                  <td align="center"><?php echo $monthly_sales[$year][$month][$item_id]['number'] ?></td>
                  <td align="center"><?php echo $monthly_sales[$year][$month][$item_id]['rate'] * 100 ?></td>
                </tr>
                <tr><th colspan="5" bgcolor="#dcdcdc">一日ごとに売れた数</th></tr>
                <tr>
                  <?php foreach ($daily_sales_numbers[$year][$month][$item_id] as $day => $number) : ?>
                    <td align="center"><?php echo $day ?>日 / <?php echo $number ?>個</td>
                    <?php if ($day % 5 === 0) : ?>
                      </tr><tr>
                    <?php endif ?>
                  <?php endforeach ?>
                </tr>
              <?php endif ?>
            <?php endforeach ?>
            <tr><th colspan="5" bgcolor="#dcdcdc"><?php echo $category['name'] ?>の合計売り上げ</th></tr>
            <tr><td colspan="5" align="center"><?php echo $category_sales_amounts[$year][$month][$category_id] ?>円</td></tr>
          <?php endforeach ?>
          <tr><th colspan="5" bgcolor="#a9a9a9">月の合計売り上げ</th></tr>
          <tr><td colspan="5" align="center"><?php echo $monthly_sales_amounts[$year][$month] ?>円</td></tr>
        </table>
        <br>
      <?php endforeach ?>
    <?php endforeach ?>
  </body>
</html>