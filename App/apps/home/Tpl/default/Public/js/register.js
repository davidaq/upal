
$(function(){
	$('#regform').checkForm();
});

//选择生日
function selectMonth(){
	var Year = $('#birthday_year').val();
	var Month = $('#birthday_month').val();
	var monthDay   =  new  Array(31,28,31,30,31,30,31,31,30,31,30,31);
	var monthDayNum;
	if(Year%400==0||(Year%4==0&&Year%100!=0)) monthDay[1]=   29;
	monthDayNum   =   monthDay[Month-1];

	var i;
	var daysout = '';
	for(i=1;i<=monthDayNum;i++){
		daysout+='<option value='+i+'>'+i+'</option>';
	}
	$('#birthday_day').html(daysout);
}

function insert_birth(){
	$("#birthday").removeClass("errorInput");
	$(".error_birthday").hide();
	$("#success_birthday").show();
}

function areaopt_plugin_fun(){
	$("#areaval").removeClass("errorInput");
	$(".error_areaval").hide();
	$("#success_areaval").show();
}
function work_check(){
	$("#school_check").hide();
	$("#school_name").val("null");
	if($("#work_name").val() == "null") $("#work_name").val("");
	$("#work_check").show();
}
function school_check(){
	$("#work_check").hide();
	$("#work_name").val("null");
	if($("#school_name").val() == "null") $("#school_name").val("");
	$("#school_check").show();
}
function other_check(){
	$(".the_check").hide();
	$("#work_name").val("null");
	$("#school_name").val("null");
}

function service_dialog(){

	ymPrompt.win({message:APP+'/Information/service',width:600,height:290,title:'服务条款',iframe:true})


}
