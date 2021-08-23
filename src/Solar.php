<?php
declare (strict_types=1);

namespace shiyin\calendar;


/**
 * 公历/阳历
 * Class Solar
 * @package shiyin\calendar
 */
class Solar extends Time
{
    public int $year;
    public int $month;
    public int $day;

    public static function new(int $year, int $month, int $day): ?Solar
    {
        if ($year < -1000 || $year > 3000) { // 超出该范围，则误差较大
            return null;
        }
        if ($month <= 0 || $month > 12) {
            return null;
        }
        $static = new static();
        return $static::configure($static, compact('year', 'month', 'day'));
    }

    // 是否为闰年
    public function tran(): ?Lunar
    {
        $data = Calendar::Solar2Lunar($this->year, $this->month, $this->day);
        return is_null($data) ? null : Lunar::new($data[0], $data[1], $data[2], $data[3] == 1);
    }

    public function string(): string
    {
        return sprintf('%d年%02d月%02d日', $this->year, $this->month, $this->day);
    }

    /**
     * 周几
     * @return int
     */
    public function week(): int
    {
        $jd = Calendar::Solar2Julian($this->year, $this->month, $this->day, 12);
        return (((floor($jd + 1) % 7)) + 7) % 7;
    }

    /**
     * 所在月有多少天
     * @return int
     */
    public function monthDays(): int
    {
        $ndf1 = -($this->year % 4 == 0);
        $ndf2 = (($this->year % 400 == 0) - ($this->year % 100 == 0)) && ($this->year > 1582);
        $ndf = $ndf1 + $ndf2;
        return 30 + ((abs($this->month - 7.5) + 0.5) % 2) - intval($this->month == 2) * (2 + $ndf);
    }

    /**
     * 获取星座下标 ['水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座']
     * @return int|false
     */
    public function zodiac()
    {
        if ($this->month < 1 || $this->month > 12) {
            return false;
        }
        if ($this->day < 1 || $this->day > 31) {
            return false;
        }
        $dds = [20, 19, 21, 20, 21, 22, 23, 23, 23, 24, 22, 22];
        $kn = $this->month - 1;
        if ($this->day < $dds[$kn]) {
            $kn = (($kn + 12) - 1) % 12;
        }
        return $kn;
    }
}