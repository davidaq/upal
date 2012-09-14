<?php 
	return '<div class="feed_quote" id="file_show_{rand}"> 
	                  <div class="q_tit"><img class="q_tit_l" src="__THEME__/images/zw_img.gif" /></div>
	                  <div class="q_con1">
		                  <img src="__THEME__/images/file/{data.file_ext}.gif" alt="{data.file_ext}" />
				    	  <a href="' . U('home/Attach/download') . '&id={data.file_id}&uid={uid}" target="_blank">{data.file_name}</a>
	                  </div>
	                  <div class="q_btm"><img class="q_btm_l" src="__THEME__/images/zw_img.gif" /></div>
	                </div>';
?>