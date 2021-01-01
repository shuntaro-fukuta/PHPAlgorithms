<?php

/**
 * ワンフロアあたりの部屋数と階数は決める
 * 物件を適当に何件か定義する
 * 適当に入居済みの部屋を決める
 * 物件はランダムで空き室がある
 * 1Fのベースの金額を決めて階数が上がってくごとに家賃が1000円上がる
 *
 * 物件は値引き可能な物件とそうでない物件がある
 * 例えば値引き可能額2000円とか
 * 値引き可能物件はオーナーの気分でもうちょい値引きできる
 * 例えば値引き可能額2000円の場合、気分次第で倍の4000円まで値引きできる
 * ランダムで二重値引き (気分で不動産が引いて、更にオーナーが引く感じ)
 *
 * 借り主を適当に定義する
 * 借り主は支払い可能額を持つ（私は10万円まで）
 *
 * 物件・部屋に対して複数の申し込みがあった場合は適当に抽選
 *
 * 結果
 * 物件・部屋に対して申し込んだ人
 * 家賃
 * 誰が借りたか
 * 最終的に物件が見つからなかった人はホームレス
 * ホームレスの一覧も出す
 */

$apartments = [
    1 => [
        'name'               => 'ハイツA',
        'floor_room_count'   => 3,
        'floor_count'        => 2,
        'base_rent'          => 50000,
        'discountable_price' => 10000,
        'floor_rooms'        => [],
    ],
    2 => [
        'name'               => 'ハイツB',
        'floor_room_count'   => 2,
        'floor_count'        => 2,
        'base_rent'          => 60000,
        'discountable_price' => 10000,
        'floor_rooms'        => [],
    ],
    3 => [
        'name'               => 'ハイツC',
        'floor_room_count'   => 3,
        'floor_count'        => 12,
        'base_rent'          => 70000,
        'discountable_price' => 5000,
        'floor_rooms'        => [],
    ],
];

$tenant_count = 20;
$tenants      = [];

for ($i = 1; $i <= $tenant_count; $i++) {
    $tenants[$i] = [
        'name'          => '借主' . $i,
        'payable_money' => 10000 * mt_rand(4, 12),
    ];
}

foreach ($apartments as $apartment_id => $apartment) {
    for ($floor = 1; $floor <= $apartment['floor_count']; $floor++) {
        $apartments[$apartment_id]['floor_rooms'][$floor] = [];
        for ($number = 1; $number <= $apartment['floor_room_count']; $number++) {
            if ($number < 10) {
                $room_number = $floor . '0' . $number;
            } else {
                $room_number = $floor . $number;
            }

            $apartments[$apartment_id]['floor_rooms'][$floor][$room_number] = [
                'rent'        => $apartment['base_rent'] + (($floor - 1) * 1000),
                'is_rentable' => (mt_rand(0, 1) === 1),
            ];
        }
    }
}

$undecided_tenant_ids = array_keys($tenants);

$homeless_ids            = [];
$all_applications        = [];
$contracted_applications = [];

while (count($undecided_tenant_ids)) {
    $apartment_vacant_room_numbers = [];
    foreach ($apartments as $apartment_id => $apartment) {
        $apartment_vacant_room_numbers[$apartment_id] = [];
        foreach ($apartment['floor_rooms'] as $floor => $rooms) {
            $apartment_vacant_room_numbers[$apartment_id][$floor] = [];
            foreach ($rooms as $room_number => $room) {
                if ($room['is_rentable'] === true) {
                    $apartment_vacant_room_numbers[$apartment_id][$floor][] = $room_number;
                }
            }
        }
    }

    $rentable_rooms              = [];
    $apartment_room_applications = [];
    foreach ($undecided_tenant_ids as $tenant_id) {
        $rentable_rooms[$tenant_id] = [];
        foreach ($apartment_vacant_room_numbers as $apartment_id => $floor_room_numbers) {
            foreach ($floor_room_numbers as $floor => $room_numbers) {
                foreach ($room_numbers as $room_number) {
                    $rent               = $apartments[$apartment_id]['floor_rooms'][$floor][$room_number]['rent'];
                    $discountable_price = $apartments[$apartment_id]['discountable_price'];

                    if ($discountable_price > 0) {
                        $do_discount = (mt_rand(0, 2) === 0);
                        if ($do_discount) {
                            $rent -= $discountable_price;

                            $do_discount = (mt_rand(0, 2) === 0);
                            if ($do_discount) {
                                $rent -= $discountable_price;
                            }
                        }
                    }

                    if ($tenants[$tenant_id]['payable_money'] >= $rent) {
                        $rentable_rooms[$tenant_id][] = [
                            'apartment_id' => $apartment_id,
                            'floor'        => $floor,
                            'room_number'  => $room_number,
                            'rent'         => $rent,
                        ];
                    }
                }
            }
        }

        if (count($rentable_rooms[$tenant_id])) {
            $applied_room         = $rentable_rooms[$tenant_id][array_rand($rentable_rooms[$tenant_id])];
            $applied_apartment_id = $applied_room['apartment_id'];
            $applied_floor        = $applied_room['floor'];
            $applied_room_number  = $applied_room['room_number'];

            $application = [
                'tenant_id' => $tenant_id,
                'rent'      => $applied_room['rent'],
            ];

            if (!isset($apartment_room_applications[$applied_apartment_id])) {
                $apartment_room_applications[$applied_apartment_id] = [];
            }
            if (!isset($apartment_room_applications[$applied_apartment_id][$applied_floor])) {
                $apartment_room_applications[$applied_apartment_id][$applied_floor] = [];
            }
            if (!isset($apartment_room_applications[$applied_apartment_id][$applied_floor][$applied_room_number])) {
                $apartment_room_applications[$applied_apartment_id][$applied_floor][$applied_room_number] = [];
            }
            $apartment_room_applications[$applied_apartment_id][$applied_floor][$applied_room_number][] = $application;
        } else {
            $homeless_ids[] = $tenant_id;
            unset($undecided_tenant_ids[array_search($tenant_id, $undecided_tenant_ids)]);
        }
    }

    if (count($apartment_room_applications)) {
        foreach ($apartment_room_applications as $apartment_id => $floor_applications) {
            foreach ($floor_applications as $floor => $room_applications) {
                foreach ($room_applications as $room_number => $applications) {
                    $contracted_application = $applications[array_rand($applications)];
                    $contractor_id          = $contracted_application['tenant_id'];

                    $apartments[$apartment_id]['floor_rooms'][$floor][$room_number]['is_rentable'] = false;

                    if (!isset($contracted_applications[$apartment_id])) {
                        $contracted_applications[$apartment_id] = [];
                    }
                    if (!isset($contracted_applications[$apartment_id][$floor])) {
                        $contracted_applications[$apartment_id][$floor] = [];
                    }
                    if (!isset($contracted_applications[$apartment_id][$floor][$room_number])) {
                        $contracted_applications[$apartment_id][$floor][$room_number] = [];
                    }

                    $contracted_applications[$apartment_id][$floor][$room_number] = $contracted_application;
                    unset($undecided_tenant_ids[array_search($contractor_id, $undecided_tenant_ids)]);

                    if (!isset($all_applications[$apartment_id])) {
                        $all_applications[$apartment_id] = [];
                    }
                    if (!isset($all_applications[$apartment_id][$floor])) {
                        $all_applications[$apartment_id][$floor] = [];
                    }
                    if (!isset($all_applications[$apartment_id][$floor][$room_number])) {
                        $all_applications[$apartment_id][$floor][$room_number] = [];
                    }
                    $all_applications[$apartment_id][$floor][$room_number][] = $applications;
                }
            }
        }
    }

    foreach (array_keys($apartments) as $apartment_id) {
        krsort($apartments[$apartment_id]['floor_rooms']);
    }
}

?>

<html>
  <body>
    <?php foreach ($apartments as $apartment_id => $apartment) : ?>
      <table border="1">
        <tr><th colspan="<?php echo $apartment['floor_room_count'] ?>" bgcolor="#dcdcdc"><?php echo $apartment['name'] ?></th></tr>
        <?php foreach ($apartment['floor_rooms'] as $floor => $rooms) : ?>
          <tr align="center">
            <?php foreach ($rooms as $room_number => $room) : ?>
              <td>
                <?php echo $room_number ?>
                <br>
                <?php if (isset($all_applications[$apartment_id][$floor][$room_number])) : ?>
                  応募した人 :
                  <?php foreach ($all_applications[$apartment_id][$floor][$room_number] as $applications) : ?>
                    <?php foreach ($applications as $application) : ?>
                      <?php echo $tenants[$application['tenant_id']]['name'] ?>
                    <?php endforeach ?>
                  <?php endforeach ?>
                  <br>
                <?php endif ?>
                <?php if ($room['is_rentable'] === false) : ?>
                  <?php if (isset($contracted_applications[$apartment_id][$floor][$room_number])) : ?>
                    借りた人 :
                    <?php echo $tenants[$contracted_applications[$apartment_id][$floor][$room_number]['tenant_id']]['name'] ?><br>
                    家賃 :
                    <?php echo $contracted_applications[$apartment_id][$floor][$room_number]['rent'] ?>円<br>
                  <?php else : ?>
                    入居済
                  <?php endif ?>
                <?php else : ?>
                  空室
                <?php endif ?>
              </td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
      </table>
      <br>
    <?php endforeach ?>
    <?php if (count($homeless_ids)) : ?>
      <table border="1">
        <tr><th bgcolor="#dcdcdc">ホームレス</th></tr>
        <?php foreach ($homeless_ids as $id) : ?>
          <tr>
            <td align="center"><?php echo $tenants[$id]['name'] ?></td>
          </tr>
        <?php endforeach ?>
    </table>
    <?php endif ?>
  </body>
</html>

