<?php

use PHPUnit\Framework\TestCase;

require_once('FizzBuzz.php');

class FizzBuzzTest extends TestCase
{
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FizzBuzz();
    }

    public function test_3の倍数かつ5の倍数のときFizzBuzzを返す()
    {
        $this->assertEquals("FizzBuzz", $this->sut->execute(15));
    }

    public function test_3の倍数のときFizzを返す()
    {
        $this->assertEquals("Fizz", $this->sut->execute(3));
    }

    public function test_5の倍数のときBuzzを返す()
    {
        $this->assertEquals("Buzz", $this->sut->execute(5));
    }

    public function test_その他の数のときその数を返す()
    {
        $this->assertEquals(2, $this->sut->execute(2));
    }
}
