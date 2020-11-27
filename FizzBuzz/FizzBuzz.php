<?php

class FizzBuzz {

    public function execute(int $input): String {
        if ($input % 3 == 0 && $input % 5 == 0) {
            return "FizzBuzz";
        } elseif ($input % 3 == 0) {
            return "Fizz";
        } elseif ($input % 5 == 0) {
            return "Buzz";
        } else {
            return $input;
        }
    }

}
