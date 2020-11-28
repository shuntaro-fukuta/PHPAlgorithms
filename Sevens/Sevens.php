<?php

/**
 * 七並べをするプログラム
 *
 * トランプが48枚あります
 * 7はすべてゲーム開始時に並べられています
 *
 * 4人でプレイします(1人当たり手札は12枚)
 *
 * プレイヤーは順番にカードを並べていきます
 * 並べられるカードはすでに並べてあるカードの数字と隣り合う数字だけです
 *
 * カードが置けない場合は3回までスキップできます(4回目で失格)
 * 失格になったら手持ちのカードをすべて並べます
 *
 * ゲームを有利に進めるため、カードは7から遠い数字のものを優先的に置いていきます
 *
 * 手札がなくなったらゲームクリアです
 * クリアした順番を出力します
 *
 * 失格の場合は最下位です
 * 失格の時点で、その人の持っていたカードは全て場に置かれます
 * (ただし、7, 8, _, 10 と場にあった時に 11 は置けません)
 * もし、失格が2人以上いた場合は同率最下位です
 * 13の次に1はおけない
 */
$players = ['一郎', '二郎', '三郎', '四郎'];

$suits = ['diamonds', 'hearts', 'spades', 'clubs'];

$cards       = [];
$field_cards = [];
foreach ($suits as $suit) {
    for ($i = 1; $i <= 13; $i++) {
        $card = [
            'suit'   => $suit,
            'number' => $i,
        ];

        if ($i === 7) {
            $field_cards[$suit][] = $card;
        } else {
            $cards[] = $card;
        }
    }
}

$player_cards = [];
shuffle($cards);
while (true) {
    foreach ($players as $player) {
        $dealed_card             = array_pop($cards);
        $player_cards[$player][] = $dealed_card;

        if (count($cards) === 0) {
            break 2;
        }
    }
}

$skip_counters = [];
foreach ($players as $player) {
    $skip_counters[$player] = 0;
}

$cleared_players = [];
$lost_players    = [];
$lowest_rank     = count($players);

while (count($players)) {
    foreach ($players as $player) {
        $puttable_cards = [];
        foreach ($field_cards as $suit => $cards) {
            $card_numbers = array_column($cards, 'number');
            for ($i = 6; $i >= 1; $i--) {
                if (in_array($i, $card_numbers) === false) {
                    $puttable_cards[] = [
                        'suit'   => $suit,
                        'number' => $i,
                    ];
                    break;
                }
            }
            for ($i = 8; $i <= 13; $i++) {
                if (in_array($i, $card_numbers) === false) {
                    $puttable_cards[] = [
                        'suit'   => $suit,
                        'number' => $i,
                    ];
                    break;
                }
            }
        }

        $selectable_cards = [];
        foreach ($player_cards[$player] as $player_card) {
            if (in_array($player_card, $puttable_cards)) {
                $selectable_cards[] = $player_card;
            }
        }

        if (empty($selectable_cards)) {
            $skip_counters[$player]++;

            if ($skip_counters[$player] === 4) {
                foreach ($player_cards[$player] as $player_card) {
                    $field_cards[$player_card['suit']][] = $player_card;
                }

                $lost_players[] = $player;
                unset($players[array_search($player, $players)]);
            }

            continue;
        }

        $selected_card = $selectable_cards[0];
        foreach ($selectable_cards as $card) {
            if (abs($card['number'] - 7) > abs($selected_card['number'] - 7)) {
                $selected_card = $card;
            }
        }

        $field_cards[$selected_card['suit']][] = $selected_card;
        unset($player_cards[$player][array_search($selected_card, $player_cards[$player])]);

        if (empty($player_cards[$player])) {
            if (isset($cleared_players)) {
                $cleared_players[$player] = count($cleared_players) + 1;
            } else {
                $cleared_players[$player] = 1;
            }
            unset($players[array_search($player, $players)]);
        }
    }
}

?>

<html>
  <body>
    <?php foreach ($cleared_players as $name => $order) : ?>
      <p><?php echo "{$name} : {$order}" ?> 位</p>
    <?php endforeach ?>
    <?php if(count($lost_players) !== 0) : ?>
      <?php foreach ($lost_players as $name) : ?>
        <p><?php echo "{$name} : {$lowest_rank}" ?>位</p>
      <?php endforeach ?>
    <?php endif ?>
  </body>
</html>
