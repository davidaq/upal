// $.extend({
    // WeiboIndex:function(setting){
        // var defaultOpt = {
            // timeStep : 12000,
            // sinceId:0,
            // lastId:0,
            // show_feed:0,
            // follow_gid:0,
            // gid:0,
            // weiboType:0,
            // type:0,
            // typeList:{},
            // childrenMenu:'.tab_dropdown',
            // hoverClass:'hover',
            // loadNewDiv:'#countNew',
            // weiboList:"#feed_list",
            // loadMoreDiv:'#loadMoreDiv'
        // },opt={};
// 
        // $.extend(opt, defaultOpt, setting);
        // var operateFactory = new OperateFactory(opt.type);
        // var innerObj = {
                // countNew:function(){
                    // if(!opt.show_feed){
                        // setInterval(function(){
                            // operateFactory.create('countNew',function(txt){
                                // if(txt.indexOf('<TSAJAX>')==0) {
                                    // if(txt.indexOf('<HASNEW>')!=-1) {
                                        // $('#countNew').html(txt);
                                        // loadNew();
                                    // }
                                // }else{
                                    // //location.reload();
                                // }
                            // });
                        // },opt.timeStep);
                    // }
                // },
                // showAndHideMenu:function (id, e, on, off){  
                    // try{  
                        // var sbtitle=document.getElementById(id);  
                        // if(sbtitle){  
                            // if(sbtitle.style.display=='block'){  
                                // sbtitle.style.display='none';  
                                // $(e).removeClass(off).addClass(on);
                            // }else{  
                                // sbtitle.style.display='block';
                                // $(e).removeClass(on).addClass(off);
                            // }  
                        // }  
                    // }catch(e){}  
               // }          
        // };
//         
        // function OperateFactory(nowType){
            // var post=function(type,otherParam){
                // var param = {};
                // for(var one in type.param){
                    // if(opt[type.param[one]] != undefined){
                        // param[one] = opt[type.param[one]];
                    // }else{
                        // param[one] = type.param[one];
                    // }
                // }
                // if(otherParam){
                    // param = $.extend(param, otherParam);
                // }
                // $.post(type.url,param,type.callback);
            // }
//             
            // var weibo={
                // countNew:{
                   // url:U('weibo/index/countnew'),
                   // param:{lastId:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
                // loadNew:{
                    // url:U('weibo/index/loadnew'),
                    // param:{since:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
                // loadMore:{
                    // url:U('weibo/index/loadmore'),
                    // param:{since:'sinceId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
            // }
//             
            // var group={
                // countNew:{
                   // url:U('group/WeiboIndex/countnew'),
                   // param:{lastId:'lastId',showfeed:'show_feed',gid:'gid',type:'weiboType'}
                // },
                // loadNew:{
                    // url:U('group/WeiboIndex/loadnew'),
                    // param:{since:'lastId',gid:'gid',type:'weiboType'}
                // },
                // loadMore:{
                    // url:U('group/WeiboIndex/loadmore'),
                    // param:{since:'sinceId',type:'weiboType',gid:'gid'}
                // },
            // }
//             
            // var all={
                // countNew:{
                   // url:U('weibo/index/countnew'),
                   // param:{hasUid:false,lastId:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
                // loadNew:{
                    // url:U('weibo/index/loadnew'),
                    // param:{hasUid:false,since:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
                // loadMore:{
                    // url:U('weibo/index/loadmore'),
                    // param:{hasUid:false,since:'sinceId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                // },
            // }
//             
            // this.create = function(commond,callback,params){
                // var type;
                // switch(nowType){
                    // case opt.typeList.WEIBO:
                        // type = weibo[commond];
                        // break;
                    // case opt.typeList.GROUP:
                        // type = group[commond];
                        // break;
                    // case opt.typeList.ALL:
                        // type = all[commond];
                        // break;
                    // default:
                        // type = weibo[commond];
                // }
                // type.callback = callback;
                // post(type,params);
            // }
        // }
//         
        // var switchMenu = function(target,classes){
           // $(target).hover(
                    // function(){ $(this).addClass(classes); },
                    // function(){ $(this).removeClass(classes); }
            // );
        // }
//         
        // var loadNew = function(){
            // $(opt.loadNewDiv).find('a').click(function() {
                // var limit = $(this).attr('limit');
                // operateFactory.create("loadNew",function(txt){
                    // if(txt.indexOf('<TSAJAX>')==0){
                        // if(txt.indexOf('<HASNEW>')!=-1) {
                            // $(opt.loadNewDiv).html('');
                            // $(opt.weiboList).prepend(txt);
                            // opt.lastId = $(opt.weiboList).find('li:first').attr('id').split("_").pop();
                        // }
                    // }else{
                        // //location.reload();
                    // }
                // },{limit:limit});
            // });
        // }
//         
        // var lodeMore = function(){
             // $(opt.loadMoreDiv).click(function() {
                // $(this).html('加载中...');
                // var self = this;
                // operateFactory.create("loadMore",function(txt){
                    // $(opt.weiboList).append(txt);
                    // opt.sinceId = $(opt.weiboList).find('li:last').attr('id').split("_").pop();
                    // $(self).html('<span class="ico_morefoot"></span>更多');
                // });
            // });
        // }
//         
        // var start = function(){
            // innerObj.countNew();
            // switchMenu(opt.childrenMenu,opt.hoverClass);
            // lodeMore();
            // if(opt.gid){
                // weibo.setGid(opt.gid);
            // }
            // return innerObj;
        // }
//         
        // return start();
    // }
// });