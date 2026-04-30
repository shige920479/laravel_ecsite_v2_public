<?php

namespace Tests\Unit\Services;

use App\Helper\Helper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// use PHPUnit\Framework\TestCase;

class ItemTrimSearchWordTest extends TestCase
{
    #[Test]
    public function trimSearchWord_スペースをトリムして配列で返す():void
    {
        $string = '  北欧   マグ  ';
        $result= Helper::trimSearchWord($string);
        $this->assertSame(['北欧', 'マグ'], $result);
    }

    #[Test]
    public function trimSearchWord_全角を半角に変換する():void
    {
        $string = 'ＡＢＣ 123';
        $result= Helper::trimSearchWord($string);
        $this->assertSame(['abc', '123'], $result);
    }
    
    #[Test]
    public function trimSearchWord_単一ワードを配列で返す():void
    {
        $result = Helper::trimSearchWord('単語');
        $this->assertSame(['単語'], $result);
    }

    #[Test]
    public function trimSearchWord_スペースや改行をトリムする():void
    {
        $result = Helper::trimSearchWord("  北欧\tマグ\nカップ ");
        $this->assertSame(['北欧', 'マグ', 'カップ'], $result);
    }
}
