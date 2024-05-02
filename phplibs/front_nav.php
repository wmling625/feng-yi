<?php
echo '<nav>';
echo '<ul class="nav nav-pills" id="mainNav">';
if (!isset($_SESSION['member'])) {
    echo '<li class="text-center mb-3">如須使用館場預約，請先登入會員。</li>';
    echo '<li class="user_li active">';
    echo '<a class="border-0 btn-modern btn-arrow-effect-1 d-inline-block" href="index.php">會員登入 <i class="las la-arrow-right"></i></a>';
    echo '</li>';
    echo '<li>';
    echo '<div class="heading heading-border heading-middle-border heading-middle-border-center">';
    echo '<h5>OR</h5>';
    echo '</div>';
    echo '</li>';
    echo '<li>';
    echo '<a href="signup.php" class="btn btn-modern btn-arrow-effect-1 bg-transparent text-primary border-0 px-0  mb-2 d-inline-block">成為會員
                <i class="las la-arrow-right"></i></a>';
    echo '</li>';
} else {
    echo '<li class="user_li active">';
    echo '<div class="img" style="background-image: url(assets/img/hi.png)"></div>';
    echo '<span>' . $_SESSION['member']['nickname'] . '</span>';
    echo '</li>';
    echo '<li class="w-100"><a href="record.php" class="nav-link">預約紀錄</a></li>';
    echo '<li class="w-100"><a href="member.php" class="nav-link">會員資訊</a></li>';
    // 螢幕太小會被隱藏
    echo '<li class="w-100"><a href="logout.php" class="nav-link">登出</a></li>';
}
echo '</ul>';
echo '</nav>';