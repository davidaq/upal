<?php
  function compress($buffer) {//去除文件中的注释
	      $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	      return $buffer;
  }
	function getContentUrl($url){
		return getShortUrl( $url[1] ).' ';
	}