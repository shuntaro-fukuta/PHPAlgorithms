<?php
/**
 * 神経衰弱
 * 
 * プレイヤーはN(N>1)人
 * 最後に取得したペアの数を出力
 */

$player_names = ["taro", "jiro", "saburo"];

$player_hands = [];
foreach ($player_names as $player_name) {
    $player_hands[$player_name] = 0;
}

$suits = ['ハート', 'ダイヤ', 'クローバー', 'スペード'];
$cards = [];
foreach ($suits as $suit) {
    for ($number = 1; $number <= 13; $number++) {
        $cards[] = [
            'suit' => $suit,
            'number' => $number
        ];
    }
}

while (count($cards) !== 0) {
    foreach ($player_names as $player_name) {
        while (count($cards) !== 0) {
            $reversed_card_indexes = [];
            $reversed_card_indexes = array_rand($cards, 2);

            $is_same_number = ($cards[$reversed_card_indexes[0]]['number'] === $cards[$reversed_card_indexes[1]]['number']);
            if (!$is_same_number) break;
            
            unset($cards[$reversed_card_indexes[0]], $cards[$reversed_card_indexes[1]]);
            $player_hands[$player_name]++;
        }
    }
}

echo '結果', PHP_EOL;
foreach ($player_hands as $player_name => $player_hand) {
    echo $player_name . ':' . $player_hand . '枚' , PHP_EOL;
}
