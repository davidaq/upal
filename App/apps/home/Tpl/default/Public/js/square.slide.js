/*广场首页-活跃用户滑动*/
function hot_user_slide(){
	var slidesrctime;
	var $hotuserlist = $('#hot_user_list');
	var $slides = $('#hot_user_slides',$hotuserlist);
	var slidewidth = $slides.width()+'px';
	var $slide = $slidefirst = $slides.find('.hot_user_slide:first');
	var $nextslide =  $slide.next();

	if($nextslide.hasClass('hot_user_slide')){
		$hotuserlist.hover(function(){
	         clearInterval(slidesrctime);
	 	},function(){
	 		slidesrctime = setInterval(function(){
	 			if($slide.css('left')=='0px' || $slide.css('left')=='auto' || $slide.css('left')=='0'){
	 				$nextslide.css('left',slidewidth);
	 				$nextslide.css('display','block');

		 			$slide.stop().animate({left:'-'+slidewidth},{queue:false,duration:400});
		 			$nextslide.stop().animate({left:'0px'},{queue:false,duration:400});

			 			$slide = $nextslide;
			 			$nextslide = $slide.next();
			 			if(!$nextslide.hasClass('hot_user_slide')){
			 				$nextslide = $slidefirst;
			 			}

	 			}else{
	 				$slide.css('left','0px');
	 				$nextslide.css('left',slidewidth);
	 			}
			},5500);
	
	 	}).trigger("mouseleave");
	}
    $hotuserlist.click(function(o){
     	var $a = $(o.target);
     	var act = $a.attr("ref");
     	if(act == 'next_slide'){
     		if($slide.css('left')=='0px' || $slide.css('left')=='auto' || $slide.css('left')=='0'){
 				$nextslide.css('left',slidewidth);
 				$nextslide.css('display','block');

	 			$slide.stop().animate({left:'-'+slidewidth},{queue:false,duration:400});
	 			$nextslide.stop().animate({left:'0px'},{queue:false,duration:400});

		 			$slide = $nextslide;
		 			$nextslide = $slide.next();
		 			if(!$nextslide.hasClass('hot_user_slide')){
		 				$nextslide = $slidefirst;
		 			}
 			}
         	return false;
     	}else if(act == 'pre_slide'){
     		if($slide.css('left')=='0px' || $slide.css('left')=='auto' || $slide.css('left')=='0'){
     			$preslide = $slide.prev();
	 			if(!$preslide.hasClass('hot_user_slide')){
	 				$preslide = $slides.find('.hot_user_slide:last');
	 			}
	 			$preslide.css('left','-'+slidewidth);
	 			$preslide.css('display','block');

	 			$slide.stop().animate({left:slidewidth},{queue:false,duration:400});
	 			$preslide.stop().animate({left:'0px'},{queue:false,duration:400});

		 			$nextslide = $slide;
		 			$slide = $preslide;
 			}
         	return false;
     	}
     });
}