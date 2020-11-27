<?php

/**
 * ウルトラソウル
 *
 * ウル / トラ / ソウル の3つの中からランダムに出力しつづける
 * もしウルトラソウルの3つが続いたら「ハイ！」と出力する
 *
 */

$words = ['ウル', 'トラ', 'ソウル'];
$words_count = count($words);

$selected_words = [];

while ($selected_words !== $words) {
    $selected_word = $words[array_rand($words)];
    echo $selected_word, PHP_EOL;

    if (count($selected_words) >= $words_count) {
        array_shift($selected_words);
    }

    $selected_words[] = $selected_word;
}

echo 'ハイ!';
