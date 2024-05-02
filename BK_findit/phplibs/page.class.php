<?php

/* * *********************************************
 * @類名:   page
 * @參數:   $myde_total - 總記錄數
 *          $myde_size - 一頁顯示的記錄數
 *          $myde_page - 當前頁
 *          $myde_url - 獲取當前的url
 * @功能:   分頁實現
 * @作者:   宋海閣
 */

class page
{

    private $myde_total;          //總記錄數
    private $myde_size;           //一頁顯示的記錄數
    private $myde_page;           //當前頁
    private $myde_page_count;     //總頁數
    private $myde_i;              //起頭頁數
    private $myde_en;             //結尾頁數
    private $myde_url;            //獲取當前的url
    /*
     * $show_pages
     * 頁面顯示的格式，顯示連結的頁數為2*$show_pages+1。
     * 如$show_pages=2那麼頁面上顯示就是[首頁] [上頁] 1 2 3 4 5 [下頁] [尾頁] 
     */
    private $show_pages;

    public function __construct($myde_total = 1, $myde_size = 1, $myde_page = 1, $myde_url, $show_pages = 2)
    {
        $this->myde_total = $this->numeric($myde_total);
        $this->myde_size = $this->numeric($myde_size);
        $this->myde_page = $this->numeric($myde_page);
        $this->myde_page_count = ceil($this->myde_total / $this->myde_size);
        $this->myde_url = $myde_url;
        if ($this->myde_total < 0)
            $this->myde_total = 0;
        if ($this->myde_page < 1)
            $this->myde_page = 1;
        if ($this->myde_page_count < 1)
            $this->myde_page_count = 1;
        if ($this->myde_page > $this->myde_page_count)
            $this->myde_page = $this->myde_page_count;
        $this->limit = ($this->myde_page - 1) * $this->myde_size;
        $this->myde_i = $this->myde_page - $show_pages;
        $this->myde_en = $this->myde_page + $show_pages;
        if ($this->myde_i < 1) {
            $this->myde_en = $this->myde_en + (1 - $this->myde_i);
            $this->myde_i = 1;
        }
        if ($this->myde_en > $this->myde_page_count) {
            $this->myde_i = $this->myde_i - ($this->myde_en - $this->myde_page_count);
            $this->myde_en = $this->myde_page_count;
        }
        if ($this->myde_i < 1)
            $this->myde_i = 1;
    }

    //檢測是否為數字
    private function numeric($num)
    {
        if (strlen($num)) {
            if (!preg_match("/^[0-9]+$/", $num)) {
                $num = 1;
            } else {
                $num = substr($num, 0, 11);
            }
        } else {
            $num = 1;
        }
        return $num;
    }

    //地址替換
    private function page_replace($page)
    {
        return str_replace("{page}", $page, $this->myde_url);
    }

    //首頁
    private function myde_home()
    {
        if ($this->myde_page != 1) {
            //return "<a href='" . $this->page_replace(1) . "' title='首頁'>首頁</a>";
        } else {
            //return "<p>首頁</p>";
        }
    }

    //上一頁
//    private function myde_prev() {
//        if ($this->myde_page != 1) {
//            return "<a href='" . $this->page_replace($this->myde_page - 1) . "' title='上一頁'> < </a>";
//        } else {
//            return "<p> < </p>";
//        }
//    }

    private function myde_prev()
    {
        if ($this->myde_page != 1) {
            return '<li class="page-item">
                      <a class="page-link" href="' . $this->page_replace(1) . '" aria-label="第一頁" title="第一頁">
                            <span aria-hidden="true">&#8676;</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="' . $this->page_replace($this->myde_page - 1) . '" aria-label="上一頁" title="上一頁">
                            <span aria-hidden="true">&laquo;</span>
                      </a>
                    </li>';
        } else {
            return '<li class="page-item disabled">
                      <span class="page-link">&laquo;</span>
                    </li>';
        }
    }

    //下一頁
//    private function myde_next()
//    {
//        if ($this->myde_page != $this->myde_page_count) {
//            return "<a href='" . $this->page_replace($this->myde_page + 1) . "' title='下一頁'> > </a>";
//        } else {
//            return "<p> > </p>";
//        }
//    }

    private function myde_next()
    {
        if ($this->myde_page != $this->myde_page_count) {
            return '<li class="page-item">
                      <a class="page-link" href="' . $this->page_replace($this->myde_page + 1) . '" aria-label="下一頁" title="下一頁">
                            <span aria-hidden="true">&raquo;</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="' . $this->page_replace($this->myde_page_count) . '" aria-label="最後一頁"  title="最後一頁">
                            <span aria-hidden="true">&#8677;</span>
                      </a>
                    </li>';
        } else {
            return '<li class="page-item disabled">
                      <span class="page-link">&raquo;</span>
                    </li>';
        }
    }

    //尾頁
    private function myde_last()
    {
        if ($this->myde_page != $this->myde_page_count) {
            //return "<a href='" . $this->page_replace($this->myde_page_count) . "' title='尾頁'>尾頁</a>";
        } else {
            //return "<p>尾頁</p>";
        }
    }

    //輸出
//    public function myde_write($id = 'page') {
//        $str = "<div id=" . $id . ">";
//        $str.=$this->myde_home();
//        $str.=$this->myde_prev();
//        if ($this->myde_i > 1) {
//            $str.="<p class='pageEllipsis'>...</p>";
//        }
//        for ($i = $this->myde_i; $i <= $this->myde_en; $i++) {
//            if ($i == $this->myde_page) {
//                $str.="<a href='" . $this->page_replace($i) . "' title='第" . $i . "頁' class='cur'>$i</a>";
//            } else {
//                $str.="<a href='" . $this->page_replace($i) . "' title='第" . $i . "頁'>$i</a>";
//            }
//        }
//        if ($this->myde_en < $this->myde_page_count) {
//            $str.="<p class='pageEllipsis'>...</p>";
//        }
//        $str.=$this->myde_next();
//        $str.=$this->myde_last();
//        //$str.="<p class='pageRemark'>共<b>" . $this->myde_page_count .
//                "</b>頁<b>" . $this->myde_total . "</b>條數據</p>";
//        $str.="</div>";
//        return $str;
//    }

    public function myde_write($id = 'page')
    {
        $str = '<nav aria-label="Page navigation" id="' . $id . '">
                    <ul class="pagination justify-content-end">';
        $str .= $this->myde_home();
        $str .= $this->myde_prev();
        if ($this->myde_i > 1) {
            $str .= '<li class="page-item">
                        <span class="page-link">...</span>
                     </li>';
        }
        for ($i = $this->myde_i; $i <= $this->myde_en; $i++) {
            if ($i == $this->myde_page) {
                $str .= '<li class="page-item active"><a class="page-link" href="' . $this->page_replace($i) . '" title="第' . $i . '頁">' . $i . '</a></li>';
            } else {
                $str .= '<li class="page-item"><a class="page-link" href="' . $this->page_replace($i) . '" title="第' . $i . '頁">' . $i . '</a></li>';
            }
        }
        if ($this->myde_en < $this->myde_page_count) {
            $str .= '<li class="page-item"><span class="page-link">...</a></li>';
        }
        $str .= $this->myde_next();
        $str .= $this->myde_last();
        //$str.="<p class='pageRemark'>共<b>" . $this->myde_page_count ."</b>頁<b>" . $this->myde_total . "</b>條數據</p>";
        $str .= "</nav>";
        return $str;
    }

    public function myde_showTotal()
    {
        $str = "<span class='text-sm text-muted'>目前為第 " . $this->myde_page . " 頁 / 共 " . $this->myde_page_count . " 頁</span><br/>";
        $str .= "<span class='text-muted text-sm'>每頁顯示 " . $this->myde_size . " 筆 / 共 " . $this->myde_total . " 筆</span>";
        return $str;
    }

    public function myde_showRow()
    {
        $limit = array(5, 10, 25, 50, 100);
        $str = '<select class="form-control form-control-sm col-2 mx-1" name="limit" search_ref>';
        foreach ($limit as $value) {
            $selected = "";
            if ($this->myde_size == $value) {
                $selected = "selected";
            }
            $str .= '<option ' . $selected . ' value="' . $value . '">每頁 ' . $value . ' 筆</option>';
        }
        $str .= '</select>';
        return $str;
    }


}

