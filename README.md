# CodeIgniter-Lottery
ci 抽奖 辅助函数

 
 # 用法
 
 ### 1、导入辅助函数
 ```php
 $this->load->helper('lottery');
 ```
 
 ### 2、调用
 ```php
 //抽奖，返回奖品id
 $price_id = lottery($arr);

 echo $price_id;
 ```
 
 关于`$arr`参数的规范有下面两种方式：
 
 ###### 方法一：直接传 array 参数

```php
$arr = array(
    array(
        "id" => 1,
        "rate" => 0.1,
    ),
    array(
        "id" => 2,
        "rate" => 0.2345,
    ),
    array(
        "id" => 3,
        "rate" => 0,
    )
);
```

 注：
 > (1) id ：奖品编号， rate：奖品中奖概率
 >
 > (2) id 从 1 开始递增
 >
 > (3) rate 为 [0,1]

 ###### 方法二：利用 `$query->result_array()` 查询数据库后返回的结果`$result`
 ```php      
$sql = "SELECT `id`,rate  FROM `price_table`";
$query = $this->db->query($sql);
$result = $query->result_array();
if (is_array($result) && count($result, COUNT_NORMAL) > 0) {
    return $result;
} else {
    return false;
}
 ```
        
 # 原理
 
 以上文`方法一：直接传 array 参数`传的参数举例：
 
 ##### 1、定义奖品，记得加上未中奖的情况
 |   type   |   id    |    rate   |
 |   :---:  |   :---:   |    :---:   |
 |   奖品1   |   1    |    0.1   |
 |   奖品2   |   2    |    0.234   |
 |   奖品3   |   3    |    0   |
 |   未中奖   |   4    |    0.6655   |
 
 ##### 2、按照 rate (中奖概率)递增排序
  |   type   |   id    |    rate   |
  |   :---:  |   :---:   |    :---:   |
    |   奖品3   |   3    |    0   |
  |   奖品1   |   1    |    0.1   |
    |   奖品2   |   2    |    0.234   |
  |   未中奖   |   4    |    0.6655   |
  
  ##### 3、取出除 0 以外的最小 rate：`min_rate` = 0.1，并计算每个奖品的权重值( weight )
  
  > 权重值计算公式：weight = rate / min_rate
  
  |   type   |   id    |    rate   |  weight | 
  |   :---:  |   :---:   |    :---:   |  :---: |
    |   奖品3   |   3    |    0   | 0 | 
  |   奖品1   |   1    |    0.1   | 1 |
    |   奖品2   |   2    |    0.234   | 2.345 | 
  |   未中奖   |   4    |    0.6655   |  6.655 | 
  
  ##### 4、划分每个奖品的落点范围( `range_min` , `rang_max` ]，左开右闭
  
  > 落点范围：
  > 
  > range_min = 上个奖品的 range_max 
  > 
  > range_max = range_min + weight
  
  |   type   |   id    |    rate   |  weight | range_min | rang_max |
  |   :---:  |   :---:   |    :---:   |  :---: | :---: | :---: |
    |   奖品3   |   3    |    0   | 0 |   0   |  0  |
  |   奖品1   |   1    |    0.1   | 1 |   0   |  1  |
    |   奖品2   |   2    |    0.234   | 2.345 |    1   |  3.345  |
  |   未中奖   |   4    |    0.6655   |  6.655 |    3.345   |  10  |
  
  ##### 5、找出落点范围小数点精确位数最多的奖品，并取出精确位数( `max_float_length` )
  |   type   |   id    |    rate   |  weight | range_min | rang_max |
    |   :---:  |   :---:   |    :---:   |  :---: | :---: | :---: |
      |   奖品2   |   2    |    0.234   | 2.345 |    1   |  3.345  |
    |   未中奖   |   4    |    0.6655   |  6.655 |    3.345   |  10  |
    
    
   > max_float_length = 3.345，即精确位数为小数点后 **3** 位
   
  ##### 6、生成随机小数的落点
  > 随机小数范围 = (1 , max(range_max) * pow(10,max_float_length)) / pow(10,max_float_length)
  
  ##### 7、判断随机小数落在哪个奖品的范围
  > 若随机小数 = 2.175 ，即落在**奖品2**！
  
  
