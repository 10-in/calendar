<?php
declare (strict_types=1);

namespace soonio\calendar;

/**
 * Class Time
 * @package soonio\calendar
 */
abstract class Time
{
    // 禁止外部直接创建
    protected function __construct(){}

    /**
     * 把公历转成农历|把农历转成阴历
     * @return Time|null
     */
    abstract function tran(): ?Time;

    /**
     * 输出格式化的字符串
     * @return string
     */
    abstract function string(): string;
}