<?php
declare (strict_types=1);

namespace shiyin\calendar;

/**
 * 基础历法的核心计算逻辑
 * Class Calendar
 * @package shiyin\calendar
 */
class Calendar
{
    /** 均值朔望月长(mean length of synodic month) */
    const SynMonth = 29.530588853;

    /** 因子 */
    const ptsa = [485, 203, 199, 182, 156, 136, 77, 74, 70, 58, 52, 50, 45, 44, 29, 18, 17, 16, 14, 12, 12, 12, 9, 8];
    const ptsb = [324.96, 337.23, 342.08, 27.85, 73.14, 171.52, 222.54, 296.72, 243.58, 119.81, 297.17, 21.02, 247.54, 325.15, 60.93, 155.12, 288.79, 198.04, 199.76, 95.39, 287.11, 320.81, 227.73, 15.45];
    const ptsc = [1934.136, 32964.467, 20.186, 445267.112, 45036.886, 22518.443, 65928.934, 3034.906, 9037.513, 33718.147, 150.678, 2281.226, 29929.562, 31555.956, 4443.417, 67555.328, 4562.452, 62894.029, 31436.921, 14577.848, 31931.756, 34777.259, 1222.114, 16859.074];

    /**
     * 计算指定年(公历)的春分点(vernal equinox),
     * 但因地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation),必须将此现象产生的偏移量加入.
     * @param int $year
     * @return float|null 返回儒略日历格林威治时间
     */
    public static function vernalEquinox(int $year): ?float
    {
        if ($year < -8000 || $year > 8001) {
            return null;
        }
        if ($year >= 1000) {
            $m = ($year - 2000) / 1000;
            return 2451623.80984 + 365242.37404 * $m + 0.05169 * $m * $m - 0.00411 * $m * $m * $m - 0.00057 * $m * $m * $m * $m;
        }
        if ($year >= -8000) {
            $m = $year / 1000;
            return 1721139.29189 + 365242.1374 * $m + 0.06134 * $m * $m + 0.00111 * $m * $m * $m - 0.00071 * $m * $m * $m * $m;
        }
        return null;
    }

    /**
     * 地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation)
     * @param float $jd
     * @return float|int 返回某时刻(儒略日历)的摄动偏移量
     */
    public static function Perturbation(float $jd)
    {
        $t = ($jd - 2451545) / 36525;
        $s = 0;
        for ($k = 0; $k <= 23; $k++) {
            $s = $s + self::ptsa[$k] * cos(self::ptsb[$k] * 2 * M_PI / 360 + self::ptsc[$k] * 2 * M_PI / 360 * $t);
        }
        $w = 35999.373 * $t - 2.47;
        $l = 1 + 0.0334 * cos($w * 2 * M_PI / 360) + 0.0007 * cos(2 * $w * 2 * M_PI / 360);
        return 0.00001 * $s / $l;
    }

    /**
     * 求∆t
     *
     * @param int $year 年份
     * @param int $month 月份
     * @return float|int
     */
    public static function DeltaT(int $year, int $month)
    {
        $y = $year + ($month - 0.5) / 12;
        if ($y <= -500) {
            $u = ($y - 1820) / 100;
            $dt = (-20 + 32 * $u * $u);
        } else if ($y < 500) {
            $u = $y / 100;
            $dt = (10583.6 - 1014.41 * $u + 33.78311 * $u * $u - 5.952053 * $u * $u * $u - 0.1798452 * $u * $u * $u * $u + 0.022174192 * $u * $u * $u * $u * $u + 0.0090316521 * $u * $u * $u * $u * $u * $u);
        } else if ($y < 1600) {
            $u = ($y - 1000) / 100;
            $dt = (1574.2 - 556.01 * $u + 71.23472 * $u * $u + 0.319781 * $u * $u * $u - 0.8503463 * $u * $u * $u * $u - 0.005050998 * $u * $u * $u * $u * $u + 0.0083572073 * $u * $u * $u * $u * $u * $u);
        } else if ($y < 1700) {
            $t = $y - 1600;
            $dt = (120 - 0.9808 * $t - 0.01532 * $t * $t + $t * $t * $t / 7129);
        } else if ($y < 1800) {
            $t = $y - 1700;
            $dt = (8.83 + 0.1603 * $t - 0.0059285 * $t * $t + 0.00013336 * $t * $t * $t - $t * $t * $t * $t / 1174000);
        } else if ($y < 1860) {
            $t = $y - 1800;
            $dt = (13.72 - 0.332447 * $t + 0.0068612 * $t * $t + 0.0041116 * $t * $t * $t - 0.00037436 * $t * $t * $t * $t + 0.0000121272 * $t * $t * $t * $t * $t - 0.0000001699 * $t * $t * $t * $t * $t * $t + 0.000000000875 * $t * $t * $t * $t * $t * $t * $t);
        } else if ($y < 1900) {
            $t = $y - 1860;
            $dt = (7.62 + 0.5737 * $t - 0.251754 * $t * $t + 0.01680668 * $t * $t * $t - 0.0004473624 * $t * $t * $t * $t + $t * $t * $t * $t * $t / 233174);
        } else if ($y < 1920) {
            $t = $y - 1900;
            $dt = (-2.79 + 1.494119 * $t - 0.0598939 * $t * $t + 0.0061966 * $t * $t * $t - 0.000197 * $t * $t * $t * $t);
        } else if ($y < 1941) {
            $t = $y - 1920;
            $dt = (21.2 + 0.84493 * $t - 0.0761 * $t * $t + 0.0020936 * $t * $t * $t);
        } else if ($y < 1961) {
            $t = $y - 1950;
            $dt = (29.07 + 0.407 * $t - $t * $t / 233 + $t * $t * $t / 2547);
        } else if ($y < 1986) {
            $t = $y - 1975;
            $dt = (45.45 + 1.067 * $t - $t * $t / 260 - $t * $t * $t / 718);
        } else if ($y < 2005) {
            $t = $y - 2000;
            $dt = (63.86 + 0.3345 * $t - 0.060374 * $t * $t + 0.0017275 * $t * $t * $t + 0.000651814 * $t * $t * $t * $t + 0.00002373599 * $t * $t * $t * $t * $t);
        } else if ($y < 2050) {
            $t = $y - 2000;
            $dt = (62.92 + 0.32217 * $t + 0.005589 * $t * $t);
        } else {
            $u = ($y - 1820) / 100;
            if ($y < 2150) {
                $dt = (-20 + 32 * $u * $u - 0.5628 * (2150 - $y));
            } else {
                $dt = (-20 + 32 * $u * $u);
            }
        }

        if ($y < 1955 || $y >= 2005) {
            $dt = $dt - (0.000012932 * ($y - 1955) * ($y - 1955));
        }
        return $dt / 60; // 将秒转换为分
    }

    /**
     * 获取指定年的春分开始的24节气,另外多取2个确保覆盖完一个公历年
     * 大致原理是:先用此方法得到理论值,再用摄动值(Perturbation)和固定参数DeltaT做调整
     * @param int $year
     * @return array
     */
    public static function MeanJQJD(int $year): array
    {
        $jd = self::vernalEquinox($year);
        if (is_null($jd)) { // 该年的春分點
            return [];
        }
        $ty = self::vernalEquinox($year + 1) - $jd; // 该年的回归年长

        $num = 24 + 2; //另外多取2个确保覆盖完一个公历年

        $ath = 2 * M_PI / 24;
        $tx = ($jd - 2451545) / 365250;
        $e = 0.0167086342 - 0.0004203654 * $tx - 0.0000126734 * $tx * $tx + 0.0000001444 * $tx * $tx * $tx - 0.0000000002 * $tx * $tx * $tx * $tx + 0.0000000003 * $tx * $tx * $tx * $tx * $tx;
        $tt = $year / 1000;
        $vp = 111.25586939 - 17.0119934518333 * $tt - 0.044091890166673 * $tt * $tt - 4.37356166661345E-04 * $tt * $tt * $tt + 8.16716666602386E-06 * $tt * $tt * $tt * $tt;
        $rvp = $vp * 2 * M_PI / 360;
        $peri = array();
        for ($i = 0; $i < $num; $i++) {
            $flag = 0;
            $th = $ath * $i + $rvp;
            if ($th > M_PI && $th <= 3 * M_PI) {
                $th = 2 * M_PI - $th;
                $flag = 1;
            }
            if ($th > 3 * M_PI) {
                $th = 4 * M_PI - $th;
                $flag = 2;
            }
            $f1 = 2 * atan((sqrt((1 - $e) / (1 + $e)) * tan($th / 2)));
            $f2 = ($e * sqrt(1 - $e * $e) * sin($th)) / (1 + $e * cos($th));
            $f = ($f1 - $f2) * $ty / 2 / M_PI;
            if ($flag == 1) {
                $f = $ty - $f;
            }
            if ($flag == 2) {
                $f = 2 * $ty - $f;
            }
            $peri[$i] = $f;
        }
        $jqjd = [];
        for ($i = 0; $i < $num; $i++) {
            $jqjd[$i] = $jd + $peri[$i] - $peri[0];
        }

        return $jqjd;
    }

    /**
     * 获取指定年的春分开始作Perturbaton调整后的24节气,可以多取2个
     * @param int $year
     * @param int $start 0~25 索引
     * @param int $end 0~25 索引
     * @return array
     */
    public static function GetAdjustedJQ(int $year, int $start, int $end): array
    {
        $jq = [];
        $Jd4JQ = self::MeanJQJD($year); // 获取该年春分开始的24节气时间点
        foreach ($Jd4JQ as $k => $jd) {
            if ($k < $start) continue;
            if ($k > $end) continue;

            $ptb = self::Perturbation($jd); // 取得受perturbation影响所需微调
            $dt = self::DeltaT($year, intval(floor(($k + 1) / 2) + 3)); // 修正dynamical time to Universal time
            $jq[$k] = $jd + $ptb - $dt / 60 / 24; // 加上摄动调整值ptb,减去对应的Delta T值(分钟转换为日)
            $jq[$k] = $jq[$k] + 1 / 3; // 因中国(北京、重庆、上海)时间比格林威治时间先行8小时，即1/3日
        }
        return $jq;
    }

    /**
     * 求出以某年立春点开始的节(注意:为了方便计算起运数,此处第0位为上一年的小寒)
     * @param int $yy
     * @return array jq[(2*$k+21)%24]
     */
    public static function GetPureJQSinceSpring(int $yy): array
    {
        $jd4jq = [];
        $dj = self::GetAdjustedJQ($yy - 1, 19, 23); // 求出含指定年立春开始之3个节气JD值,以前一年的年值代入
        foreach ($dj as $k => $v) {
            if ($k < 19 || 23 < $k || $k % 2 == 0) continue;
            $jd4jq[] = $v; // 19小寒;20大寒;21立春;22雨水;23惊蛰
        }

        $dj = self::GetAdjustedJQ($yy, 0, 25); // 求出指定年节气之JD值,从春分开始,到大寒,多取两个确保覆盖一个公历年,也方便计算起运数
        foreach ($dj as $k => $v) {
            if ($k % 2 == 0) continue;
            $jd4jq[] = $v;
        }

        return $jd4jq;
    }

    /**
     * 求出自冬至点为起点的连续15个中气(zq)
     * @param int $year
     * @return array jq[(2*$k+18)%24]
     */
    public static function GetZQSinceWinterSolstice(int $year): array
    {
        $jd4zq = [];

        $dj = self::GetAdjustedJQ($year - 1, 18, 23); // 求出指定年冬至开始之节气JD值,以前一年的值代入
        $jd4zq[0] = $dj[18]; //冬至
        $jd4zq[1] = $dj[20]; //大寒
        $jd4zq[2] = $dj[22]; //雨水

        $dj = self::GetAdjustedJQ($year, 0, 23); // 求出指定年节气之JD值
        foreach ($dj as $k => $v) {
            if ($k % 2 != 0) continue;
            $jd4zq[] = $v;
        }
        return $jd4zq;
    }

    /**
     * 求出实际新月点
     * 以2000年初的第一个均值新月点为0点求出的均值新月点和其朔望月之序数 k 代入此副程式來求算实际新月点
     * @param $k
     * @return float|int
     */
    public static function TrueNewMoon($k)
    {
        $jdt = 2451550.09765 + $k * self::SynMonth;
        $t = ($jdt - 2451545) / 36525; // 2451545为2000年1月1日正午12时的JD
        $t2 = $t * $t; // square for frequent use
        $t3 = $t2 * $t; // cube for frequent use
        $t4 = $t3 * $t; // to the fourth
        // mean time of phase
        $pt = $jdt + 0.0001337 * $t2 - 0.00000015 * $t3 + 0.00000000073 * $t4;
        // Sun's mean anomaly(地球绕太阳运行均值近点角)(从太阳观察)
        $m = 2.5534 + 29.10535669 * $k - 0.0000218 * $t2 - 0.00000011 * $t3;
        // Moon's mean anomaly(月球绕地球运行均值近点角)(从地球观察)
        $mPrime = 201.5643 + 385.81693528 * $k + 0.0107438 * $t2 + 0.00001239 * $t3 - 0.000000058 * $t4;
        // Moon's argument of latitude(月球的纬度参数)
        $f = 160.7108 + 390.67050274 * $k - 0.0016341 * $t2 - 0.00000227 * $t3 + 0.000000011 * $t4;
        // Longitude of the ascending node of the lunar orbit(月球绕日运行轨道升交点之经度)
        $omega = 124.7746 - 1.5637558 * $k + 0.0020691 * $t2 + 0.00000215 * $t3;
        // 乘式因子
        $es = 1 - 0.002516 * $t - 0.0000074 * $t2;
        // 因perturbation造成的偏移：
        $slice = M_PI / 180;
        $apt1 = -0.4072 * sin($slice * $mPrime);
        $apt1 += 0.17241 * $es * sin($slice * $m);
        $apt1 += 0.01608 * sin($slice * 2 * $mPrime);
        $apt1 += 0.01039 * sin($slice * 2 * $f);
        $apt1 += 0.00739 * $es * sin($slice * ($mPrime - $m));
        $apt1 -= 0.00514 * $es * sin($slice * ($mPrime + $m));
        $apt1 += 0.00208 * $es * $es * sin($slice * (2 * $m));
        $apt1 -= 0.00111 * sin($slice * ($mPrime - 2 * $f));
        $apt1 -= 0.00057 * sin($slice * ($mPrime + 2 * $f));
        $apt1 += 0.00056 * $es * sin($slice * (2 * $mPrime + $m));
        $apt1 -= 0.00042 * sin($slice * 3 * $mPrime);
        $apt1 += 0.00042 * $es * sin($slice * ($m + 2 * $f));
        $apt1 += 0.00038 * $es * sin($slice * ($m - 2 * $f));
        $apt1 -= 0.00024 * $es * sin($slice * (2 * $mPrime - $m));
        $apt1 -= 0.00017 * sin($slice * $omega);
        $apt1 -= 0.00007 * sin($slice * ($mPrime + 2 * $m));
        $apt1 += 0.00004 * sin($slice * (2 * $mPrime - 2 * $f));
        $apt1 += 0.00004 * sin($slice * (3 * $m));
        $apt1 += 0.00003 * sin($slice * ($mPrime + $m - 2 * $f));
        $apt1 += 0.00003 * sin($slice * (2 * $mPrime + 2 * $f));
        $apt1 -= 0.00003 * sin($slice * ($mPrime + $m + 2 * $f));
        $apt1 += 0.00003 * sin($slice * ($mPrime - $m + 2 * $f));
        $apt1 -= 0.00002 * sin($slice * ($mPrime - $m - 2 * $f));
        $apt1 -= 0.00002 * sin($slice * (3 * $mPrime + $m));
        $apt1 += 0.00002 * sin($slice * (4 * $mPrime));

        $apt2 = 0.000325 * sin($slice * (299.77 + 0.107408 * $k - 0.009173 * $t2));
        $apt2 += 0.000165 * sin($slice * (251.88 + 0.016321 * $k));
        $apt2 += 0.000164 * sin($slice * (251.83 + 26.651886 * $k));
        $apt2 += 0.000126 * sin($slice * (349.42 + 36.412478 * $k));
        $apt2 += 0.00011 * sin($slice * (84.66 + 18.206239 * $k));
        $apt2 += 0.000062 * sin($slice * (141.74 + 53.303771 * $k));
        $apt2 += 0.00006 * sin($slice * (207.14 + 2.453732 * $k));
        $apt2 += 0.000056 * sin($slice * (154.84 + 7.30686 * $k));
        $apt2 += 0.000047 * sin($slice * (34.52 + 27.261239 * $k));
        $apt2 += 0.000042 * sin($slice * (207.19 + 0.121824 * $k));
        $apt2 += 0.00004 * sin($slice * (291.34 + 1.844379 * $k));
        $apt2 += 0.000037 * sin($slice * (161.72 + 24.198154 * $k));
        $apt2 += 0.000035 * sin($slice * (239.56 + 25.513099 * $k));
        $apt2 += 0.000023 * sin($slice * (331.55 + 3.592518 * $k));
        return $pt + $apt1 + $apt2;
    }

    /**
     * 对于指定日期时刻所属的朔望月,求出其均值新月点的月序数
     * @param float $jd
     * @return array
     */
    public static function MeanNewMoon(float $jd): array
    {
        // $kn为从2000年1月6日14时20分36秒起至指定年月日之阴历月数,以synodic month为单位
        $kn = floor(($jd - 2451550.09765) / self::SynMonth); // 2451550.09765为2000年1月6日14时20分36秒之JD值.
        $jdt = 2451550.09765 + $kn * self::SynMonth;
        // Time in Julian centuries from 2000 January 0.5.
        $t = ($jdt - 2451545) / 36525; // 以100年为单位,以2000年1月1日12时为0点
        $theJD = $jdt + 0.0001337 * $t * $t - 0.00000015 * $t * $t * $t + 0.00000000073 * $t * $t * $t * $t;
        // 2451550.09765为2000年1月6日14时20分36秒,此为2000年后的第一个均值新月
        return [$kn, $theJD];
    }

    /**
     * 将儒略日历时间转换为公历(格里高利历)时间
     * @param float $jd
     * @return array(年,月,日,时,分,秒)
     */
    public static function Julian2Solar(float $jd): array
    {
        if ($jd >= 2299160.5) { //1582年10月15日,此日起是儒略日历,之前是儒略历
            $y4h = 146097;
            $init = 1721119.5;
        } else {
            $y4h = 146100;
            $init = 1721117.5;
        }
        $jdr = floor($jd - $init);
        $yh = $y4h / 4;
        $cen = floor(($jdr + 0.75) / $yh);
        $d = floor($jdr + 0.75 - $cen * $yh);
        $ywl = 1461 / 4;
        $jy = floor(($d + 0.75) / $ywl);
        $d = floor($d + 0.75 - $ywl * $jy + 1);
        $ml = 153 / 5;
        $mp = floor(($d - 0.5) / $ml);
        $d = floor(($d - 0.5) - 30.6 * $mp + 1);
        $y = (100 * $cen) + $jy;
        $m = ($mp + 2) % 12 + 1;
        if ($m < 3) {
            $y = $y + 1;
        }
        $sd = floor(($jd + 0.5 - floor($jd + 0.5)) * 24 * 60 * 60 + 0.00005);
        $minute = floor($sd / 60);
        $second = $sd % 60;
        $hour = floor($minute / 60);
        $minute = $minute % 60;
        $year = floor($y);
        $month = floor($m);
        $day = floor($d);

        return [(int)$year, (int)$month, (int)$day, $hour, $minute, $second];
    }

    /**
     * 以比较日期法求算冬月及其余各月名称代码,包含闰月,冬月为0,腊月为1,正月为2,其余类推.闰月多加0.5
     * @param int $year
     * @return array
     */
    public static function GetZQAndSMandLunarMonthCode(int $year): array
    {
        $mc = [];
        $jd4zq = self::GetZQSinceWinterSolstice($year); // 取得以前一年冬至为起点之连续15个中气
        $jd4nm = self::GetSMSinceWinterSolstice($year, $jd4zq[0]); // 求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月點
        $yz = 0; // 设定flag,0表示未遇到闰月,1表示已遇到闰月
        if (floor($jd4zq[12] + 0.5) >= floor($jd4nm[13] + 0.5)) { // 若第13个中气jdzq(12)大于或等于第14个新月jdnm(13)
            for ($i = 1; $i <= 14; $i++) { // 表示此两个冬至之间的11个中气要放到12个朔望月中,
                // 至少有一个朔望月不含中气,第一个不含中气的月即为闰月
                // 若阴历腊月起始日大于冬至中气日,且阴历正月起始日小于或等于大寒中气日,则此月为闰月,其余同理
                if (($jd4nm[$i] + 0.5) > floor($jd4zq[$i - 1 - $yz] + 0.5) && floor($jd4nm[$i + 1] + 0.5) <= floor($jd4zq[$i - $yz] + 0.5)) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; //标示遇到闰月
                } else {
                    $mc[$i] = $i - $yz; // 遇到闰月开始,每个月号要减1
                }
            }
        } else { // 否则表示两个连续冬至之间只有11个整月,故无闰月
            for ($i = 0; $i <= 12; $i++) { // 直接赋予这12个月月代码
                $mc[$i] = $i;
            }
            for ($i = 13; $i <= 14; $i++) { //处理次一置月年的11月与12月,亦有可能含闰月
                // 若次一阴历腊月起始日大于附近的冬至中气日,且阴历正月起始日小于或等于大寒中气日,则此月为腊月,次一正月同理.
                if (($jd4nm[$i] + 0.5) > floor($jd4zq[$i - 1 - $yz] + 0.5) && floor($jd4nm[$i + 1] + 0.5) <= floor($jd4zq[$i - $yz] + 0.5)) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; // 标示遇到闰月
                } else {
                    $mc[$i] = $i - $yz; // 遇到闰月开始,每个月号要减1
                }
            }
        }
        return [$jd4nm, $mc];
    }

    /**
     * 求算以含冬至中气为阴历11月开始的连续16个朔望月
     * @param int $year 年份
     * @param float $jd4ws 冬至的儒略日历时间
     * @return array
     */
    public static function GetSMSinceWinterSolstice(int $year, float $jd4ws): array
    {
        $tjd = [];
        $jd = self::Solar2Julian($year - 1, 11, 1); //求年初前兩个月附近的新月點(即前一年的11月初)
        list($kn,) = self::MeanNewMoon($jd); //求得自2000年1月起第kn个平均朔望日及其JD值
        for ($i = 0; $i <= 19; $i++) { //求出連續20个朔望月
            $k = $kn + $i;
            $tjd[$i] = self::TrueNewMoon($k) + 1 / 3; //以k值代入求瞬时朔望日,因中國比格林威治先行8小时,加1/3天
            //下式为修正dynamical time to Universal time
            $tjd[$i] = $tjd[$i] - self::DeltaT($year, $i - 1) / 1440; //1为1月,0为前一年12月,-1为前一年11月(当i=0时,i-1=-1,代表前一年11月)
        }
        for ($j = 0; $j <= 18; $j++) {
            if (floor($tjd[$j] + 0.5) > floor($jd4ws + 0.5)) {
                break;
            } // 已超過冬至中氣(比較日期法)
        }

        $JdNM = [];
        for ($k = 0; $k <= 15; $k++) { // 取上一步的索引值
            $JdNM[$k] = $tjd[$j - 1 + $k]; // 重排索引,使含冬至朔望月的索引为0
        }
        return $JdNM;
    }

    /**
     * 将公历时间转换为儒略日历时间
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour [0-23]
     * @param int $minute [0-59]
     * @param int $second [0-59]
     * @return float
     */
    public static function Solar2Julian(int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0)
    {
        if (!self::validDate($year, $month, $day)) {
            return false;
        }
        if ($hour < 0 || $hour >= 24) {
            return false;
        }
        if ($minute < 0 || $minute >= 60) {
            return false;
        }
        if ($second < 0 || $second >= 60) {
            return false;
        }

        $yp = $year + floor(($month - 3) / 10);
        if (($year > 1582) || ($year == 1582 && $month > 10) || ($year == 1582 && $month == 10 && $day >= 15)) { //这一年有十天是不存在的
            $init = 1721119.5;
            $jdy = floor($yp * 365.25) - floor($yp / 100) + floor($yp / 400);
        }
        if (($year < 1582) || ($year == 1582 && $month < 10) || ($year == 1582 && $month == 10 && $day <= 4)) {
            $init = 1721117.5;
            $jdy = floor($yp * 365.25);
        }
        if (!isset($init) || !isset($jdy)) {
            return false;
        }
        $mp = floor($month + 9) % 12;
        $jdm = $mp * 30 + floor(($mp + 1) * 34 / 57);
        $jdd = $day - 1;
        $jdh = ($hour + ($minute + ($second / 60)) / 60) / 24;
        return $jdy + $jdm + $jdd + $jdh + $init;
    }

    /**
     * 判断公历日期是否有效
     * @param int $yy
     * @param int $mm
     * @param int $dd
     * @return boolean
     */
    public static function validDate(int $yy, int $mm, int $dd): bool
    {
        if (
            ($yy < -1000 || $yy > 3000) //适用于西元-1000年至西元3000年,超出此范围误差较大
            || ($mm < 1 || $mm > 12)
            || ($yy == 1582 && $mm == 10 && $dd >= 5 && $dd < 15) //这段日期不存在.所以1582年10月只有20天
        ) return false;

        $ndf1 = $yy % 4 == 0 ? -1 : 0; //可被四整除
        $ndf2 = (($yy % 400 == 0 ? 1 : 0) - ($yy % 100 == 0 ? 1 : 0)) == 1 && $yy > 1582;
        $ndf = $ndf1 + $ndf2;
        $dom = 30 + ((abs($mm - 7.5) + 0.5) % 2) - ($mm == 2 ? 1 : 0) * (2 + $ndf);

        return 0 < $dd && $dd <= $dom;
    }

    /**
     * 将农历时间转换成公历时间
     * @param int $year
     * @param int $month
     * @param int $day
     * @param bool $isLeap 是否闰月
     * @return array /array(年,月,日)
     */
    public static function Lunar2Solar(int $year, int $month, int $day, bool $isLeap): ?array
    {
        if (
            $year < -7000  //超出计算能力 有效的计算范围在公元前后七千年
            || ($year < -1000 || $year > 3000)  //适用于西元-1000年至西元3000年,超出此范围误差较大
            || ($month < 1 || $month > 12) //输入月份必須在1-12月之內
            || ($day < 1 || $day > 30) //输入日期必須在1-30日之內
        ) return null;

        list($jd4nm, $mc) = self::GetZQAndSMandLunarMonthCode($year);

        $leap = 0; //若闰月flag为0代表无闰月
        for ($j = 1; $j <= 14; $j++) { //确认指定年前一年11月开始各月是否闰月
            if ($mc[$j] - floor($mc[$j]) > 0) { //若是,则将此闰月代码放入闰月flag內
                $leap = floor($mc[$j] + 0.5); //leap=0对应阴历11月,1对应阴历12月,2对应阴历隔年1月,依此类推.
                break;
            }
        }

        $month = $month + 2; //11月对应到1,12月对应到2,1月对应到3,2月对应到4,依此类推

        $NoFd = [];
        for ($i = 0; $i <= 14; $i++) { //求算阴历各月之大小,大月30天,小月29天
            $NoFd[$i] = floor($jd4nm[$i + 1] + 0.5) - floor($jd4nm[$i] + 0.5); //每月天数,加0.5是因JD以正午起算
        }

        if ($isLeap) { //若是闰月
            if ($leap < 3) { //而flag非闰月或非本年闰月,则表示此年不含闰月.leap=0代表无闰月,=1代表闰月为前一年的11月,=2代表闰月为前一年的12月
                return null; //此年非闰年
            } else { //若本年內有闰月
                if ($leap != $month) { //但不为输入的月份
                    return null; //则此输入的月份非闰月,此月非闰月
                } else { //若输入的月份即为闰月
                    if ($day <= $NoFd[$month]) { //若输入的日期不大于当月的天数
                        $jd = $jd4nm[$month] + $day - 1; //则将当月之前的JD值加上日期之前的天数
                    } else { //日期超出范围
                        return null;
                    }
                }
            }
        } else { //若沒有勾选闰月则
            if ($leap == 0) { //若flag非闰月,则表示此年不含闰月(包括前一年的11月起之月份)
                if ($day <= $NoFd[$month - 1]) { //若输入的日期不大于当月的天数
                    $jd = $jd4nm[$month - 1] + $day - 1; //则将当月之前的JD值加上日期之前的天数
                } else { //日期超出范围
                    return null;
                }
            } else { //若flag为本年有闰月(包括前一年的11月起之月份) 公式nofd(mx - (mx > leap) - 1)的用意为:若指定月大于闰月,则索引用mx,否则索引用mx-1
                if ($day <= $NoFd[$month + ($month > $leap) - 1]) { //若输入的日期不大于当月的天数
                    $jd = $jd4nm[$month + ($month > $leap) - 1] + $day - 1; //则将当月之前的JD值加上日期之前的天数
                } else { //日期超出范围
                    return null;
                }
            }
        }
        return array_slice(self::Julian2Solar($jd), 0, 3);
    }

    /**
     * 将公历时间转换成农历时间
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array|null array(年,月,日,是否闰月)
     */
    public static function Solar2Lunar(int $year, int $month, int $day): ?array
    {
        if (!self::validDate($year, $month, $day)) { // 验证输入的日期是否正确
            return null;
        }

        $prev = 0; //是否跨年了,跨年了则减一
        $isLeap = 0;//是否闰月

        list($JdNm, $mc) = self::GetZQAndSMandLunarMonthCode($year);

        $jd = self::Solar2Julian($year, $month, $day, 12); //求出指定年月日之JD值
        if (floor($jd) < floor($JdNm[0] + 0.5)) {
            $prev = 1;
            list($JdNm, $mc) = self::GetZQAndSMandLunarMonthCode($year - 1);
        }
        $mi = 0;
        for ($i = 0; $i <= 14; $i++) { //指令中加0.5是为了改为从0时算起而不从正午算起
            if (floor($jd) >= floor($JdNm[$i] + 0.5) && floor($jd) < floor($JdNm[$i + 1] + 0.5)) {
                $mi = $i;
                break;
            }
        }

        if ($mc[$mi] < 2 || $prev == 1) { //年
            $year = $year - 1;
        }

        if (($mc[$mi] - floor($mc[$mi])) * 2 + 1 != 1) { //因mc(mi)=0对应到前一年阴历11月,mc(mi)=1对应到前一年阴历12月,mc(mi)=2对应到本年1月,依此类推
            $isLeap = 1;
        }
        $month = floor($mc[$mi] + 10) % 12 + 1; //月
        $day = floor($jd) - floor($JdNm[$mi] + 0.5) + 1; //日,此处加1是因为每月初一从1开始而非从0开始

        return [$year, $month, (int)$day, $isLeap];
    }
}