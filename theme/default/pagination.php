<?php
function ap_pagination($pages = '', $range = 1, $paged = false, $wp_query = false)
{ 
	$showitems = ($range * 2)+1; 
	
	if(!$paged){
		global $paged;
		if(empty($paged)) $paged = 1;
	}
 
	if($pages == ''){
		if(!$wp_query)
			global $wp_query;
			
		$pages = $wp_query->max_num_pages;
		if(!$pages){
			$pages = 1;
		}
	}  
 
     if(1 != $pages)
     {
         echo '<ul class="ap-pagination clearfix">';
		 
		 echo '<li><span class="page-count">Page '.$paged.' of '.$pages.'</span></li>';
         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo '<li><a href="'.get_pagenum_link(1).'" title="'.__('First', 'ap').'" >&laquo;</a></li>';
         if($paged > 1 && $showitems < $pages) echo '<li><a href="'.get_pagenum_link($paged - 1).'" title="'.__('Previous', 'ap').'">&lsaquo; </a></li>';
 
         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                 echo ($paged == $i)? '<li class="active"><span>'.$i.'</span></li>':'<li><a href="'.get_pagenum_link($i).'" title="'.__('Go to page ','ap').$i.'">'.$i.'</a></li>';
             }
         }
 
         if ($paged < $pages && $showitems < $pages) echo "<li><a href=\"".get_pagenum_link($paged + 1)."\">Next &rsaquo;</a></li>"; 
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<li><a href='".get_pagenum_link($pages)."'>Last &raquo;</a></li>";
         echo "</ul>";
     }
}
