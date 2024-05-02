<?php
$login_type = isset($_SESSION['admin']['types']) ? $_SESSION['admin']['types'] : "admin";
$permission_arr = isset($_SESSION['admin']['permission']) ? json_decode($_SESSION['admin']['permission'], true) : array();
$special_permission_arr = isset($_SESSION['admin']['special_permission']) ? json_decode($_SESSION['admin']['special_permission'], true) : array();

$basic_array = array();
if (count($permission_arr) == 0) {
    $basic_array = $menu_array; //如果權限長度是0，那就看最大權限
} else {
    foreach ($menu_array as $key1 => $value1) {
        foreach ($menu_array[$key1] as $key2 => $value2) {
            if (in_array($value2["link"], $permission_arr)) {
                $basic_array[$key1][] = array("title" => $value2["title"], "link" => $value2["link"], "target" => $value2["target"]);
            } else {
                if (($login_type === "admin" && $value2['checked']) || ($login_type === "user" && $value2['user_checked'] && $value2['user_show'])) {
                    $basic_array[$key1][] = array("title" => $value2["title"], "link" => $value2["link"], "target" => $value2["target"]);
                }
            }
        }
    }
}

//$basic_array = $menu_array;


echo '<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">';
foreach ($basic_array as $key => $values) {

    $active = "";
    $menu_open = "";
    if ($GLOBALS['template_sub'] == $key) {
        $active = "active";
        $menu_open = "menu-open";
    };
    if (count($values) == 1) {
        $target = (isset($values[0]["target"])) ? $values[0]["target"] : "_self";
        echo '<li class="nav-item">';
        echo '<a href="' . $values[0]["link"] . '" class="nav-link ' . $active . '" target="' . $target . '">';
        echo '<i class="nav-icon ' . $icon_array[$key] . ' " aria-hidden="true"></i>';
        echo '<p>' . $values[0]["title"] . '</p>';
        echo '</a>';
        echo '</li>';;
    } else {
        echo '<li class="nav-item ' . $menu_open . '">';
        echo '<a href="#" class="nav-link ' . $active . '">';
        echo '<i class="nav-icon ' . $icon_array[$key] . '" aria-hidden="true"></i>';
        echo '<p>' . $key . '<i class="right fas fa-angle-left"></i></p>';
        echo '</a>';

        echo '<ul class="nav nav-treeview">';
        foreach ($values as $key => $value) {
            $active = "";
            if ($GLOBALS['template_title'] == $value["title"]) {
                $active = "active";
            }
            $target = (isset($value["target"])) ? $value["target"] : "_self";
            echo '<li class="nav-item">';
            echo '<a href="' . $value["link"] . '" class="nav-link ' . $active . '" target="' . $target . '">';
            echo '<i class="far fa-circle nav-icon"></i>';
            echo '<p>' . $value["title"] . '</p>';
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</li>';
    }
}
echo '</ul>';