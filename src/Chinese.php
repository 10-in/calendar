<?php
declare(strict_types=1);

namespace soonio\calendar;

/**
 * 天干地支
 * Class Chinese
 * @package soonio\calendar
 */
class Chinese
{
    // 中文数字
    const Number = ['日', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];

    // 农历月份常用称呼
    const Month = ['正', '二', '三', '四', '五', '六', '七', '八', '九', '十', '冬', '腊'];

    // 农历日期常用称呼
    const DayPreference = ['初', '十', '廿', '卅'];

    // 十天干
    const GAN = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];

    // 十二地支
    const ZHI = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];

    // 十二生肖
    const Zodiac = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];

    // 星期
    const WeekDay = ['日', '一', '二', '三', '四', '五', '六'];

    /** 廿四节气(从春分开始) */
    const SolarTerms = [
        '春分', '清明', '谷雨', '立夏', '小满', '芒种',
        '夏至', '小暑', '大暑', '立秋', '处暑', '白露',
        '秋分', '寒露', '霜降', '立冬', '小雪', '大雪',
        '冬至', '小寒', '大寒', '立春', '雨水', '惊蛰'];

    /**
     * 某公历年立春点开始的24节气
     * @param int $year
     * @return array jq[($k+21)%24]
     */
    public static function solarTerms(int $year): array
    {
        $solarTermsTime = [];
        $dj = Calendar::GetAdjustedJQ($year - 1, 21, 23); //求出含指定年立春开始之3个节气JD值,以前一年的年值代入
        foreach ($dj as $k => $v) {
            if ($k < 21 || $k > 23) {
                continue;
            }
            $solarTermsTime[] = Calendar::Julian2Solar($v); //21立春;22雨水;23惊蛰
        }
        $dj = Calendar::GetAdjustedJQ($year, 0, 20); //求出指定年节气之JD值,从春分开始
        foreach ($dj as $v) {
            $solarTermsTime[] = Calendar::Julian2Solar($v);
        }
        return $solarTermsTime;
    }

    /**
     * 四柱计算,分早子时晚子时,传公历
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour 时间(0-23)
     * @param int $minute 分钟数(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节
     * @param int $second 秒数(0-59)
     * @param bool $zero
     * @return array(天干, 地支, 对应的儒略日历时间, 对应年的12节+前后N节, 对应时间所处节的索引)
     */
    public static function GanZhi(int $year, int $month, int $day, int $hour, int $minute = 0, int $second = 0, bool $zero = true): array
    {
        if (!$jd = Calendar::Solar2Julian($year, $month, $day, $hour, $minute, max(1, $second))) { //多加一秒避免精度问题
            return [];
        }

        $tg = $dz = [];
        $jq = Calendar::GetPureJQSinceSpring($year); //取得自立春开始的节,该数组长度固定为16
        if ($jd < $jq[1]) { //jq[1]为立春,约在2月5日前后,
            $year = $year - 1; //若小于jq[1],则属于前一个节气年
            $jq = Calendar::GetPureJQSinceSpring($year); //取得自立春开始的节
        }

        $ygz = (($year + 4712 + 24) % 60 + 60) % 60;
        $tg[0] = $ygz % 10; //年干
        $dz[0] = $ygz % 12; //年支

        $ix = 0;
        for ($j = 0; $j <= 15; $j++) { //比較求算节气月,求出月干支
            if ($jq[$j] >= $jd) { //已超過指定时刻,故應取前一个节气
                $ix = $j - 1;
                break;
            }
        }

        $tmm = (($year + 4712) * 12 + ($ix - 1) + 60) % 60; //数组0为前一年的小寒所以这里再减一
        $mgz = ($tmm + 50) % 60;
        $tg[1] = $mgz % 10; //月干
        $dz[1] = $mgz % 12; //月支

        $jdA = $jd + 0.5; //计算日柱之干支,加0.5是将起始點从正午改为从0點开始.
        $theS = (($jdA - floor($jdA)) * 86400) + 3600; //将jd的小数部份化为秒,並加上起始點前移的一小时(3600秒),取其整数值
        $dayJD = floor($jdA) + $theS / 86400; //将秒数化为日数,加回到jd的整数部份
        $dgz = (floor($dayJD + 49) % 60 + 60) % 60;
        $tg[2] = $dgz % 10; //日干
        $dz[2] = $dgz % 12; //日支
        if ($zero && ($hour >= 23)) { //区分早晚子时,日柱前移一柱
            $tg[2] = ($tg[2] + 10 - 1) % 10;
            $dz[2] = ($dz[2] + 12 - 1) % 12;
        }

        $dh = $dayJD * 12; //计算时柱之干支
        $hgz = (floor($dh + 48) % 60 + 60) % 60;
        $tg[3] = $hgz % 10; //时干
        $dz[3] = $hgz % 12; //时支

        return [$tg, $dz, $jd, $jq, $ix];
    }

    /**
     * 公历年排盘
     * @param bool $isMale 是否为男性
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour 时间(0-23)
     * @param int $minute 分钟数(0-59),在跨节的时辰上会需要,有的排盘忽略了跨节
     * @param int $second 秒数(0-59)
     * @return array
     */
    public static function Fate(bool $isMale, int $year, int $month, int $day, int $hour, int $minute = 0, int $second = 0): array
    {
        $ret = [];
        $LuckyG = $LuckyZ = []; //大运干支

        list($tg, $dz, $jd, $jq, $ix) = static::GanZhi($year, $month, $day, $hour, $minute, $second);

        $pn = $tg[0] % 2; //起大运.阴阳年干:0阳年1阴年

        if (($isMale && $pn == 0) || (!$isMale && $pn == 1)) { //起大运时间,阳男阴女顺排
            $span = $jq[$ix + 1] - $jd; //往后数一个节,计算时间跨度
            for ($i = 1; $i <= 12; $i++) { //大运干支
                $LuckyG[] = ($tg[1] + $i) % 10;
                $LuckyZ[] = ($dz[1] + $i) % 12;
            }
        } else { // 阴男阳女逆排,往前数一个节
            $span = $jd - $jq[$ix];
            for ($i = 1; $i <= 12; $i++) { //确保是正数
                $LuckyG[] = ($tg[1] + 20 - $i) % 10;
                $LuckyZ[] = ($dz[1] + 24 - $i) % 12;
            }
        }

        $days = intval($span * 4 * 30); //折合成天数:三天折合一年,一天折合四个月,一个时辰折合十天,一个小时折合五天,反推得到一年按360天算,一个月按30天算
        $y = intval($days / 360); //三天折合一年
        $m = intval($days % 360 / 30); //一天折合四个月
        $d = $days % 360 % 30; //一个小时折合五天

        $ret['tg'] = $tg;
        $ret['dz'] = $dz;
        $ret['big_tg'] = $LuckyG;
        $ret['big_dz'] = $LuckyZ;
        $ret['start_desc'] = "{$y}年{$m}月{$d}天起运";
        $start_jd_time = $jd + $span * 120; //三天折合一年,一天折合四个月,一个时辰折合十天,一个小时折合五天,反推得到一年按360天算
        $ret['start_time'] = Calendar::Julian2Solar($start_jd_time); //转换成公历形式,注意这里变成了数组

        $ret['BaZi'] = $ret['big'] = $ret['years'] = ''; //八字,大运,流年的字符表示
        $ret['big_start_time'] = []; //各步大运的起始时间

        $ret['sx'] = self::Zodiac[$dz[0]]; //生肖

        for ($i = 0; $i <= 3; $i++) {
            $ret['BaZi'] .= self::GAN[$tg[$i]];
            $ret['BaZi'] .= self::ZHI[$dz[$i]];
        }

        for ($i = 0; $i < 12; $i++) {
            $ret['big'] .= self::GAN[$LuckyG[$i]];
            $ret['big'] .= self::ZHI[$LuckyZ[$i]];
            $ret['big_start_time'][] = Calendar::Julian2Solar($start_jd_time + $i * 10 * 360);
        }

//        for ($i = 1, $j = 0; ; $i++) {
//            if (($year + $i) < $ret['start_time'][0]) { //还没到起运年
//                continue;
//            }
//            if ($j++ >= 120) {
//                break;
//            }
//
//            $t = ($tg[1] + $i) % 10;
//            $d = ($dz[1] + $i) % 12;
//
//            $ret['years'] .= self::GAN[$t];
//            $ret['years'] .= self::ZHI[$d];
//            if ($j % 10 == 0) {
//                $ret['years'] .= "\n";
//            }
//        }
        return $ret;
    }

    /**
     * 农历月份常用名称
     * @param int $month
     * @return string
     */
    public static function monthString(int $month): string
    {
        $k = $month - 1;
        return self::Month[$k];
    }

    /**
     * 农历日期数字返回汉字表示法
     * @param int $day
     * @return string
     */
    public static function dayString(int $day): string
    {
        switch ($day) {
            case 10:
                $dayStr = self::DayPreference[0] . self::Number[10];
                break;
            case 20:
                $dayStr = self::DayPreference[2] . self::Number[10];
                break;
            case 30:
                $dayStr = self::DayPreference[3] . self::Number[10];
                break;
            default:
                $k = intval(floor($day / 10));
                $m = $day % 10;
                $dayStr = self::DayPreference[$k] . self::Number[$m];
        }
        return $dayStr;
    }
}