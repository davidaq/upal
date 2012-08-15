function changeEduCar(o){
	var type = $(o).val();
	var educationHtml = '<li><DIV class="left alR" style="width:15%;">学校名称：</DIV>'
					  + '<DIV class="left cGray2" style="width:85%;padding:10px 0"><input name="school" type="text" class="text" id="textfield12" style="width:200px;" onBlur="this.className=\'text\'" onFocus="this.className=\'text2\'" dataType="LimitB" min="1" max="300" msg="学校名称不能为空!" /></DIV>'
					  + '<DIV class="left cGray2"></DIV></li>'
					  + '<li><DIV class="left alR" style="width:15%;">班级或院系：</DIV>'
					  + '<DIV class="left" style="width: 85%;padding:10px 0"><input name="classes" type="text" class="text" id="textfield13" style="width:200px;" onBlur="this.className=\'text\'" onFocus="this.className=\'text2\'" dataType="LimitB" min="1" max="300" msg="班级名称不能为空!" />'
					  + '</DIV></li>'
					  + '<li><DIV class="left alR" style="width:15%">入学年份：</DIV>'
					  + '<DIV class="left" style="width:85%;margin-top:10px">'
					  + '<select name="year" dataType="Require"  msg="未选择入学年份">' + getYearList() + '</select>年</DIV></li>'
					  + '<li><DIV class="left alR" style="width:15%;">&nbsp;</DIV>'
					  + '<input type="hidden" name="type" value="education">'
					  + '<DIV class="left" style="width: 85%;"><input type="submit" class="btn_b" value="添 加" />'
					  + '</DIV></li>';
	
	var careerHtml    = '<li><DIV class="left alR lh25" style="width:15%;">公司/机构：</DIV>'
					  + '<DIV class="left cGray2" style="width:85%;padding:10px 0">'
					  + '<input name="company" type="text" class="text" id="company" style="width:200px;" onBlur="this.className=\'text\'" onFocus="this.className=\'text2\'" dataType="LimitB" min="1" max="300" msg="公司名称不能为空!" />'
					  + '</DIV><DIV class="left cGray2"></DIV></li>'
					  + '<li><DIV class="left alR lh25" style="width:15%;">部门：</DIV>'
					  + '<DIV class="left" style="width: 85%;padding:10px 0">'
					  + '<input name="position" type="text" class="text" id="position" style="width:200px;" onBlur="this.className=\'text\'" onFocus="this.className=\'text2\'" dataType="LimitB" min="1" max="300" msg="部门名称不能为空!" />'
					  + '</DIV></li>'
					  + '<li><DIV class="left alR lh25" style="width:15%">就职时间：</DIV>'
                      + '<div style="width: 85%;" class="zh_i_of left">'
                      + '<div style="padding:10px 0 0 0;">'
                      + '<select style="width: 4.5em;width:60px;" name="beginyear" id="beginyear" dataType="Require"  msg="未选择入职年份">' + getYearList() + '</select>年 '
                      + '<select style="width: 3.5em;" id="beginmonth" name="beginmonth">' + getMonthList() + '</select>至'
                      + '<select style="width: 4.5em;width:60px;" id="endyear" name="endyear">' + getYearList() + '</select><span id="endyearword">年</span> '
                      + '<select style="width: 3.5em;" id="endmonth" name="endmonth">' + getMonthList() + '</select> <span> (<input type="checkbox" onclick="changeEnd(this)" value="1" name="nowworkflag" id=\'nowworkflag\'/>至今)</span>'
                      + '</div></div></li>'
                      + '<li><DIV class="left alR" style="width:15%;">&nbsp;</DIV><input type="hidden" name="type" value="career">'
                      + '<DIV class="left" style="width: 85%;"><input type="submit" class="btn_b" value="添 加" /></DIV></li>';
	
	if( type == 'education'){
		$("#educarContent").html( educationHtml );
	}else if( type== 'career' ){
		$("#educarContent").html( careerHtml );
	}else{
		$("#educarContent").html( '' );
	}
	
	
}

//获取年份列表
function getYearList(begin){
	var nowdate = new Date();
	var now = nowdate.getFullYear();
	var beginYear = begin || now;
	var html = '';
	for(var i=beginYear;i>=1910;i--){
		html+= '<option value='+i+'>'+i+'</option>';
	}
	return html;
}

//获取月份列表
function getMonthList(begin){
	var html = '';
	for(var i=1;i<=12;i++){
		html+= '<option value='+i+'>'+i+'</option>';
	}
	return html;
}

//选择至今
function changeEnd(o){
	if( o.checked == true ){
		$('#endyear').attr('disabled','true');
		$('#endmonth').attr('disabled','true');
	}else{
		$('#endyear').removeAttr('disabled');
		$('#endmonth').removeAttr('disabled');
	}
}