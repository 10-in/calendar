# 历法

## 安装使用

  - 安装
  ```shell
  composer require shiyin/calendar
  ```

  - 农历转公历
  ```php
  $a= Calendar::Lunar2Solar(2020, 4, 1, true);
  print_r($a); // [2020, 5, 23]
  ```

  - 公历转农历
  ```php
  $s = Calendar::Solar2Lunar(2020, 5, 23);
  print_r($s); // [2020, 4, 1, 1]
  ```

  - 构造公历对象
  ```php
  $l = Lunar::new(2020, 4, 1, true)
  ```
  - 构造农历对象
  ```php
  $s = Solar::new(2020, 5, 23);
  ```

## 功能

  - 公历转农历
  - 农历转公历
  - 干支纪年法
  - 属相
  - 星座
  - 节气

## TODO

  - 对固定值结果不再重复计算，直接写公式计算的结果

## 参考

  [liujiawm/paipan](https://github.com/liujiawm/paipan)  
  [nozomi199/qimen_star](https://github.com/nozomi199/qimen_star)  
  [http://www.bieyu.com/](http://www.bieyu.com/)