<?php
/*
/**
 * Paginates the song requests in the frontend
 *
 * @since       1.0.0
 * @package     ss_song_requester
 * @subpackage  ss_song_requester/includes
 * @author      Ciprian Popescu a.k.a. Wolfe
 * @contributor André R. Kohl <code@sixtyseven.info>
 *
 * @source: https://gist.github.com/wolffe/d2a7511696157f85bb94e2b8301b5473
 */
class ss_song_requester_pagination {
    var $total_pages = -1;
    var $limit = 20;
    var $target = '';
    var $page = 1;
    var $adjacents = 1;
    var $showCounter = true;
    var $className = 'tablenav-pages';
    var $parameterName = 'paging';
    var $itemName = 'items';

    var $nextT = ''; // 'Next' label
    var $nextI = '&#187;'; //&#9658;
    var $prevT = ''; // 'Previous' label
    var $prevI = '&#171;'; //&#9668;

    var $calculate = false;
    
    var $jumptarget = '';

    var $frp_pagination;

    function items($value) {
        $this->total_pages = (int) $value;
    } // total items

    function limit($value) {
        $this->limit = (int) $value;
    } // items per page

    function target($value) {
        $this->target = $value;
    } // page value (page argument for WordPress "?" parameter)

    function currentPage($value) {
        $this->page = (int) $value;
    } // current page

    function adjacents($value) {
        $this->adjacents = (int)$value;
    } // adjacent pages to show on each side of the current page

    function showCounter($value = '') {
        $this->showCounter = ($value === true) ? true : false;
    }

    function changeClass($value = '') {
        $this->className = $value;
    } // class of the pagination div // should be the WordPress default

    function nextLabel($value) {
        $this->nextT = $value;
    }

    function nextIcon($value) {
        $this->nextI = $value;
    }

    function prevLabel($value) {
        $this->prevT = $value;
    }

    function prevIcon($value) {
        $this->prevI = $value;
    }

    function parameterName($value = '') {
        $this->parameterName = $value;
    } // change the class name of the pagination div (?)
    
    function itemName($value = '') {
        $this->itemName = $value;
    } // change the wording of "items"
    
    function jumptarget($value = '') {
        $this->jumptarget = '#'.$value;
    } // jump to specific part of paage


    function show() {
        if (!$this->calculate) {
            if ($this->calculate()) {
                echo '<div class="' . $this->className . '">' . $this->frp_pagination . '</div>';
            }
        }
    }
    
    function return_pagination() {
        if (!$this->calculate) {
            if ($this->calculate()) {
                return '<div class="' . $this->className . '">' . $this->frp_pagination . '</div>';
            }
        }
    }

    function getOutput() {
        if (!$this->calculate) {
            if ($this->calculate()) {
                return '<div class="' . $this->className . '">' . $this->frp_pagination . '</div>';
            }
        }
    }

    function get_pagenum_link($id) {
        if (strpos($this->target, '?') === false) {
            return "$this->target?$this->parameterName=$id";
        } else {
            return htmlspecialchars("$this->target&$this->parameterName=$id");
        }
    }

    function calculate() {
        $this->frp_pagination = '';
        $this->calculate == true;
        $error = false;

        if ($this->total_pages < 0) {
            echo "It is necessary to specify the <strong>number of pages</strong> (\$class->items(1000))<br>";
            $error = true;
        }
        if ($this->limit == null) {
            echo "It is necessary to specify the <strong>limit of items</strong> to show per page (\$class->limit(10))<br />";
            $error = true;
        }
        if ($error) {
            return false;
        }

        $n = trim($this->nextT . ' ' . $this->nextI);
        $p = trim($this->prevI . ' ' . $this->prevT);

        $this->page = $this->page ? $this->page : 1;

        // setup page variables for display
        $prev = $this->page - 1; 							// previous page is page - 1
        $next = $this->page + 1; 							// next page is page + 1
        $lastpage = ceil($this->total_pages / $this->limit); // lastpage = total pages / items per page, rounded up.
        $lpm1 = $lastpage - 1; 								// last page minus 1

        /* 
         * Apply all the above rules and draw the pagination object.
         * Save the code to a variable in case it is drawn more than once.
         */
        if ($lastpage > 1) {
            if ($this->showCounter) {
                $this->frp_pagination .= "<span class=\"displaying-num\">$this->total_pages $this->itemName</span> ";
            }
            if ($this->page) {
                if ($this->page > 1) {
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($prev) . "\" class=\"prev\">$p</a>";
                } else {
                    $this->frp_pagination .= "<a class=\"disabled\">$p</a>";
                }
            }
            //pages	
            if ($lastpage < 7 + ($this->adjacents * 2)) { // not enough pages to bother breaking it up
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $this->page) {
                        $this->frp_pagination .= "<a class=\"current\">$counter</a>";
                    } else {
                        $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($counter) . $this->jumptarget . "\">$counter</a>";
                    }
                }
            } elseif($lastpage > 5 + ($this->adjacents * 2)) { // enough pages to hide some
                // close to beginning; only hide later pages
                if ($this->page < 1 + ($this->adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($this->adjacents * 2); $counter++) {
                        if ($counter == $this->page) {
                            $this->frp_pagination .= "<a class=\"current\">$counter</a>";
                        } else {
                            $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($counter) . $this->jumptarget . "\">$counter</a>";
                        }
                    }
                    $this->frp_pagination .= '...';
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($lpm1) . "\">$lpm1</a>";
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($lastpage) . "\">$lastpage</a>";
                }
                // in the middle; hide some front and some back
                elseif ($lastpage - ($this->adjacents * 2) > $this->page && $this->page > ($this->adjacents * 2)) {
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link(1) . $this->jumptarget . "\">1</a>";
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link(2) . $this->jumptarget . "\">2</a>";
                    $this->frp_pagination .= '...';

                    for ($counter = $this->page - $this->adjacents; $counter <= $this->page + $this->adjacents; $counter++) {
                        if ($counter == $this->page) {
                            $this->frp_pagination .= "<a class=\"current\">$counter</a>";
                        } else {
                            $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($counter) . $this->jumptarget . "\">$counter</a>";
                        }
                    }
                    $this->frp_pagination .= '...';
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($lpm1) . $this->jumptarget . "\">$lpm1</a>";
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($lastpage) . $this->jumptarget . "\">$lastpage</a>";
                }
                // close to end; only hide early pages
                else {
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link(1) . "\">1</a>";
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link(2) . "\">2</a>";
                    $this->frp_pagination .= '...';
                    for ($counter = $lastpage - (2 + ($this->adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $this->page) {
                            $this->frp_pagination .= "<a class=\"current\">$counter</a>";
                        } else {
                            $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($counter) . $this->jumptarget . "\">$counter</a>";
                        }
                    }
                }
            }
            if ($this->page) {
                if ($this->page < $counter - 1) {
                    $this->frp_pagination .= "<a href=\"" . $this->get_pagenum_link($next) . $this->jumptarget . "\" class=\"next-page\">$n</a>";
                } else {
                    $this->frp_pagination .= "<a class=\"disabled\">$n</a>";
                }
			}
        }
        return true;
    }
}
