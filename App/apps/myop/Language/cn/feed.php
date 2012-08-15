<?php 
return  array(
	'myop_feed'   => array(
		'title' 		=> $title,
//		'body'			=> $content,
		'body'			=> ($image1 ? '<a target="_blank" href="'.$image1Link.'"><img class="summaryimg" src="'.$image1.'"></a>' : '') . $content,
	),
);
?>