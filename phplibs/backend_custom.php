<?php

// 產生select option
function gen_options($option_arr = array(), $default = "")
{
    $str = "";
    foreach ($option_arr as $key => $value) {
        if ($value == $default) {
            $str .= "<option value='" . $value . "' selected>" . $key . "</option>";
        } else {
            $str .= "<option value='" . $value . "'>" . $key . "</option>";
        }
    }
    return $str;
}

function get_types($table = "", $page = "list", $orderby = "ASC", $title = "分類")
{
    global $mysqli;
    switch ($page) {
        case "list":
            $arr = array("全部" . $title => "");
            break;
        case "mang":
            $arr = array();
            break;
    }
    $arr = array();
    $query = "SELECT title, introd  FROM `" . $table . "` WHERE orders >=0 ORDER BY orders " . $orderby;
    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $arr[$row["title"]] = $row["introd"];
        }
    }
    return $arr;
}

function cal_percentage($num_amount, $num_total)
{
    if ($num_total > 0) {
        $count1 = $num_amount / $num_total;
        $count2 = $count1 * 100;
        return number_format($count2, 2);
    } else {
        return 0;
    }

}

/* 解開session string 中的json，若符號為"，就改成' */
function logs_replace_json($string, $find)
{
    $matches = array();
    preg_match('/.*?' . $find . '.*?\"(\{.+\})\"/', $string, $matches);
    if (isset($matches[1])) {
        $replaced = str_replace("\"", "'", $matches[1]);
        $json = preg_replace('/(.*?' . $find . '.*?)\{.*?\}(.*?)/', '$1' . $replaced . '$3', $string);
        return json_decode($json, true);
    } else {
        return json_decode($string, true);
    }

}