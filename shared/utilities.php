<?php
class Utilities{
  
    public function getPaging($page, $total_rows, $records_per_page, $page_url){
  
        // paging array
        $paging_arr=array();
  
        // button for first page
        $paging_arr["first"] = $page > 1 ? "{$page_url}page=1" : "";
        
        // count all products in the database to calculate total pages
        $total_pages = ceil($total_rows / $records_per_page);
        // range of links to show
        $range = 2;
  
        // display links to 'range of pages' around 'current page'
        $initial_num = $page - $range;
        $condition_limit_num = ($page + $range)  + 1;
  
        $paging_arr['pages']=array();
        $page_count=0;
          
        // button for last page
        $paging_arr["last"] = $total_pages;
  
        // json format
        return $paging_arr;
    }
  
}
?>