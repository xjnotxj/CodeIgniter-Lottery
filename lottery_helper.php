<?php

/**
 *  抽奖
 */
function lottery($arr)
{

    //1\补充上未中奖，它也算一种特殊的奖品
    $no_price_rate = 1;
    foreach ($arr as $row) {
        $no_price_rate -= $row ['rate'];
    }
    array_push($arr, array("id" => count($arr) + 1, "rate" => $no_price_rate));

    //2\奖品按照中奖比率从小到大排序
    foreach ($arr as $key => $row) {
        $num1[$key] = $row ['rate'];
    }
    array_multisort($num1, SORT_ASC, $arr);

    //3\取出除了0以外的最小概率
    $rate_min = null;
    foreach ($arr as $row) {
        if ($row ['rate'] != 0) {
            $rate_min = $row ['rate'];
            break;
        }
    };

    //4\计算每个奖品的权重值
    foreach ($arr as $key => $row) {
        if ($row ['rate'] == 0) {
            $arr[$key]["weight"] = 0;
        } else {
            $arr[$key]["weight"] = $row ['rate'] / $rate_min;
        }
    }

    //5\划分每个奖品的落点范围，左开右闭
    $last_rate = 0;
    foreach ($arr as $key => $row) {
        $arr[$key]["range_min"] = $last_rate;
        $arr[$key]["range_max"] = $last_rate + $row ['weight'];

        $last_rate = $last_rate + $row ['weight'];
    }

    //6\找出落点范围小数点精确位数最多的奖品，并取出精确位数
    $max_float_length = 0;
    foreach ($arr as $row) {
        $range_min_float_length = _getFloatLength($row["range_min"]);
        $range_max_float_length = _getFloatLength($row["range_max"]);
        if ($range_min_float_length > $max_float_length) {
            $max_float_length = $range_min_float_length;
        }
        if ($range_max_float_length > $max_float_length) {
            $max_float_length = $range_max_float_length;
        }
    }

    //7\生成随机数(小数)落点
    $price_rand = rand(1, $arr[count($arr) - 1]['range_max'] * pow(10, $max_float_length)) / pow(10, $max_float_length);

    //8\判断随机数落在哪个奖品的范围
    $price_id = 0;
    foreach ($arr as $key => $row) {
        if ($price_rand > $row['range_min'] && $price_rand <= $row['range_max']) {
            //如果是未中奖，返回0
            if ($row['id'] != count($arr)) {
                $price_id = $row['id'];
            }
            break;
        }
    }

//     print_r($arr);
//     print_r($price_rand . PHP_EOL);
//     print_r($price_id . PHP_EOL);

    return $price_id;

}

function _getFloatLength($num)
{
    $count = 0;

    $temp = explode('.', $num);

    if (sizeof($temp) > 1) {
        $decimal = end($temp);
        $count = strlen($decimal);
    }

    return $count;
}
