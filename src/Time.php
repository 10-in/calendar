<?php
declare (strict_types=1);

namespace shiyin\calendar;

/**
 * Class Time
 * @package shiyin\calendar
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

    public static function configure($object, array $properties)
    {
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }

}