<?php
declare (strict_types=1);

namespace shiyin\calendar;


/**
 * 农历/阴历
 * Class Solar
 * @package shiyin\calendar
 */
class Lunar extends Time
{
    public int $year;
    public int $month;
    public int $day;

    public bool $isLeap = false;

    public static function new(int $year, int $month, int $day, bool $isLeap=false): ?Lunar
    {
        if ($year < -1000 || $year > 3000) { // 超出该范围，则误差较大
            return null;
        }
        if ($month <= 0 || $month > 12) {
            return null;
        }
        $static = new static();
        return $static::configure($static, compact('year', 'month', 'day', 'isLeap'));
    }

    // 转换为阳历
    function tran(): ?Solar
    {
        $data = Calendar::Lunar2Solar($this->year, $this->month, $this->day, $this->isLeap);
        return is_null($data) ? null : Solar::new($data[0], $data[1], $data[2]);
    }

    function string(): string
    {
        return sprintf('%d年%s%02d月%02d日', $this->year, $this->isLeap ? '闰' : '', $this->month, $this->day);
    }

    /**
     * 获取农历某个月有多少天
     * @return int
     */
    public function monthDays(): int
    {
        list($jdnm, $mc) = Calendar::GetZQAndSMandLunarMonthCode($this->year);
        $leap = 0;
        for ($j = 1; $j <= 14; $j++) {
            if ($mc[$j] - floor($mc[$j]) > 0) {
                $leap = floor($mc[$j] + 0.5);
                break;
            }
        }
        $month = $this->month + 2;
        $nofd = [];
        for ($i = 0; $i <= 14; $i++) {
            $nofd[$i] = floor($jdnm[$i + 1] + 0.5) - floor($jdnm[$i] + 0.5);
        }

        $days = 0;
        if ($this->isLeap) {
            if ($leap >= 3) {
                if ($leap == $month) {
                    $days = $nofd[$month];
                }
            }
        } else {
            if ($leap == 0) {
                $days = $nofd[$month - 1];
            } else {
                $days = $nofd[$month + ($month > $leap) - 1];
            }
        }
        return (int)$days;
    }

    /**
     * 年的闰月,0为无闰月
     * @return int
     */
    public function leapMonth(): int
    {
        list(, $mc) = Calendar::GetZQAndSMandLunarMonthCode($this->year);
        $leap = 0;
        for ($j = 1; $j <= 14; $j++) {
            if ($mc[$j] - floor($mc[$j]) > 0) {
                $leap = floor($mc[$j] + 0.5);
                break;
            }
        }
        return (int)max(0, $leap - 2);
    }
}