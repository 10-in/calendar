<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use shiyin\calendar\Calendar;
use shiyin\calendar\Chinese;
use shiyin\calendar\Lunar;
use shiyin\calendar\Solar;

class DemoTest extends TestCase
{
    public function testLunar2Solar()
    {
        $res = Calendar::Lunar2Solar(2020, 4, 1, true);

        $this->assertSame([2020, 5, 23], $res);
    }

    public function testSolar2Lunar()
    {
        $s = Calendar::Solar2Lunar(2020, 5, 23);

        $this->assertSame([2020, 4, 1, 1], $s);
    }

    public function testLunar()
    {
        $l = Lunar::new(2020, 4, 1, true);
        $this->assertEquals('2020年闰04月01日', $l->string());
        $this->assertEquals('2020年05月23日', $l->tran()->string());
    }

    public function testSolar()
    {
        $l = Solar::new(2020, 5, 23);
        $this->assertEquals('2020年05月23日', $l->string());
        $this->assertEquals('2020年闰04月01日', $l->tran()->string());
    }


    public function testBaZi()
    {
        $res = '{"tg":[4,5,4,5],"dz":[2,7,6,7],"big_tg":[6,7,8,9,0,1,2,3,4,5,6,7],"big_dz":[8,9,10,11,0,1,2,3,4,5,6,7],"start_desc":"9年6月1天起运","start_time":[2007,11,22,5,46,34],"years":"","big":"庚申辛酉壬戌癸亥甲子乙丑丙寅丁卯戊辰己巳庚午辛未","BaZi":"戊寅己未戊午己未","big_start_time":[[2007,11,22,5,46,34],[2017,9,30,5,46,34],[2027,8,9,5,46,34],[2037,6,17,5,46,34],[2047,4,26,5,46,34],[2057,3,4,5,46,34],[2067,1,11,5,46,34],[2076,11,19,5,46,34],[2086,9,28,5,46,34],[2096,8,6,5,46,34],[2106,6,16,5,46,34],[2116,4,24,5,46,34]],"sx":"虎"}';
        $fate = Chinese::Fate(true, 1998, 7, 10, 13);
        $this->assertEquals(json_encode($fate, JSON_UNESCAPED_UNICODE), $res);
    }
}