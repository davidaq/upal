//@language=home/widget
$.extend({
    weibo:function(setting){
        var defaultOpt = {
            timeStep : 12000,
            sinceId:0,
            lastId:0,
            show_feed:0,
            follow_gid:0,
            gid:0,
            weiboType:0,
            type:0,
            searchUrl:U('home/User/searchTips'),
            typeList:{
                WEIBO:0,
                GROUP:1,
                ALL:2
            },
            childrenMenu:'.tab_dropdown',
            hoverClass:'hover',
            loadNewDiv:'#countNew',
            weiboListDiv:"#feed_list",
            loadMoreDiv:'#loadMoreDiv',
            publishForm:{
                type:"input[name=publish_type]",
                weiboType:"#publish_type_content_before",
                wordNum:".wordNum",
                textarea:"#content_publish",
                button:"#publish_handle",
                form:"#miniblog_publish"
            },
            weiboList:{
                comment:{a:"a[rel='comment']",form:"form[rel='miniblog_comment']"}
            },
            initForm:false,
            loading:'<div class="feed_quote feed_wb" style="text-align:center"><img src="'+ _THEME_+'/images/icon_waiting.gif" width="15"></div>',
            leftAppPublish:'.user_app_link'
        },opt={},self=this,popup=null,isLoading=false;
        
        $.extend(opt, defaultOpt, setting);
        var operateFactory = new OperateFactory(opt.type);
        var typeInput = $(opt.publishForm.type);
        
        $.extend(this,{
                obj:null,
                countNew:function(){
                    if(_MID_ <= 0){
                        return ;
                    }
                    if(!opt.show_feed && opt.lastId>0){
                        setInterval(function(){
                            operateFactory.create('countNew',function(txt){
                                if(txt.indexOf('<TSAJAX>')==0) {
                                    if(txt.indexOf('<HASNEW>')!=-1) {
                                        $('#countNew').html(txt);
                                        events.loadNew();
                                    }
                                }else{
                                    //location.reload();
                                }
                            });
                        },opt.timeStep);
                    }
                },
                closeCallback:function(fn){
                    popup.closeCallback = fn;
                },
                showAndHideMenu:function (id, e, on, off){  
                    try{  
                        var sbtitle=document.getElementById(id);  
                        if(sbtitle){  
                            if(sbtitle.style.display=='block'){  
                                sbtitle.style.display='none';  
                                $(e).removeClass(off).addClass(on);
                            }else{  
                                sbtitle.style.display='block';
                                $(e).removeClass(on).addClass(off);
                            }  
                        }  
                    }catch(e){}  
                },
                setLastIdByWeiboListDiv:function(){
                   setLastIdByWeiboListDiv();
                },
                reset:function(){
                    typeInput.val(0);
                    popup && popup.remove();
                    if(weibo.obj != null){
                        weibo.obj.destroy();
                        weibo.obj = null;
                    }
                    if(popup && popup.closeCallback != undefined && typeof popup.closeCallback == 'function'){
                        popup.closeCallback();
                    }
                    hasBox = false;
                },
                publish_type_val:function(publish_type){
                    $(opt.publishForm.type).val( publish_type );
                },
                //发布操作
                do_publish:function(){
                    if( before_publish() ){
                        textareaStatus('sending');
                        var options = {
                            success: function(txt) {
                              if(txt){
                                  after_publish(txt);
                              }else{
                                  alert( '{:L('发布失败')}' );
                              }
                            }
                        };      
                        $(opt.publishForm.form).ajaxSubmit( options );
                        return false;
                    }
                },
                //删除一条微博
                deleted:function(weibo_id){
                   operateFactory.create("deleteWeibo",function(txt){
                        if( txt ){
                            $("#list_li_"+weibo_id).slideUp('fast');
                            weibo.downCount('weibo');
                        }else{
                            alert('{:L('del_error')}');
                        }
                   },{id:weibo_id})
                },
                upCount:function(){
                    upCount();
                },
                addtheme:function(){
                    var text = '#请在这里输入自定义话题#';
                    var   patt   =   new   RegExp(text,"g");  
                    var content_publish = $(opt.publishForm.textarea);
                    var result;
                                
                    if( content_publish.val().search(patt) == '-1' ){
                        content_publish.insertAtCaret(text);
                    
                    var textArea = document.getElementById(opt.publishForm.textarea.split('#').pop());
                    
                    result = patt.exec( content_publish.val() );
                    
                    var end = patt.lastIndex-1 ;
                    var start = patt.lastIndex - text.length +1;
                    
                    if (document.selection) { //IE
                         var rng = textArea.createTextRange();
                         rng.collapse(true);
                         rng.moveEnd("character",end)
                         rng.moveStart("character",start)
                         rng.select();
                    }else if (textArea.selectionStart || (textArea.selectionStart == '0')) { // Mozilla/Netscape…
                        textArea.selectionStart = start;
                        textArea.selectionEnd = end;
                    }
                    textArea.focus();
                    return ;
                    }
                },
                //收藏
                favorite:function(id,o){
                    if(_MID_ <= 0){
                        ui.error('{:L('请登录后再操作')}'); return ;
                    }
                    operateFactory.create('favorite',function(txt){
                        if( txt ){
                            $(o).wrap('<span id=content_'+id+'></span>');
                            $('#content_'+id).html('');
                        }else{
                            ui.error('{:L('收藏失败')}');
                        }
                    },{id:id});
                },
                //取消收藏
                unFavorite:function(id,o){
                    if(_MID_ <= 0){
                        ui.error('{:L('请登录后再操作')}'); return ;
                    }
                    operateFactory.create('unFavorite',function(txt){
                         if( txt ){
                            $('#list_li_'+id).slideUp('slow');
                        }else{
                            ui.error('{:L('取消失败')}');
                        }
                    },{id:id});
                },
                // 分享微博
                share:function(id){
                    if(_MID_ <= 0){
                        ui.error('{:L('请登录后再操作')}'); return ;
                    }
                    var upcontent = ( upcontent == undefined ) ? 1 : 0;
                    operateFactory.loadbox('share',{id:id,upcontent:upcontent});
                },
                //转发
                transpond:function(id,upcontent){
                    var upcontent = ( upcontent == undefined ) ? 1 : 0;
                    operateFactory.loadbox('transpond',{id:id,upcontent:upcontent});
                },
                //关注话题
                followTopic:function(name){
                    if(_MID_ <= 0){
                        ui.error('{:L('请登录后再操作')}'); return ;
                    }
                    operateFactory.create('followTopic',function(txt){
                        txt = eval( '(' + txt + ')' );
                        if(txt.code==12){
                            $('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.unfollowTopic(\''+txt.topicId+'\',\''+name+'\')">{:L('followed_topic')}</a>');
                        }
                    },{name:name});
                },
                unfollowTopic:function(id,name){
                    operateFactory.create('unfollowTopic',function(txt){
                        if(txt=='01'){
                            $('#followTopic').html('<a href="javascript:void(0)" onclick="weibo.followTopic(\''+name+'\')">{:L('follow_topic')}</a>');
                        }
                    },{topicId:id});
                },
                quickpublish:function(text){
                    if(_MID_ <= 0){
                        ui.error('{:L('请登录后再操作')}'); return ;
                    }
                    operateFactory.create('quickpublish',function(txt){
                       ui.box.show(txt,{title:'{:L('说几句')}',closeable:true});
                    },{text:text});
                },
                publish_type_box:function(content,obj){
                    var obj_left = $(obj).offset().left;
                    var mg_left = obj_left - $('#publish_type_content_before').offset().left+($(obj).width()/2);
                    //if(this.hasBox) return;
                    var html = '<div class="talkPop">'
                        + '<div style="position:relative; height:7px; line-height:3px; z-index:99">'
                        + '<img class="talkPop_arrow" style="margin-left:'+ mg_left +'px;position:absolute;" src="'+_THEME_+'/images/zw_img.gif" /></div>'
                        + '<div class="talkPop_box">'
                        + '<div class="pop_tit close" id="weibo_close_handle"><a href="javascript:void(0)" class="del" onclick="weibo.reset()" > </a></div>'
                        + '<div id="publish_type_content">'+content+'</div>'
                        + '</div></div>';
                    $('div.talkPop').remove();
                    $("#publish_type_content_before").after( html );
                    popup = $('.talkPop');
                },
                //检查字数输入
                checkInputLength:function(num){
                    var len = getLength($(opt.publishForm.textarea).val(), true);
                    var wordNumObj = $(opt.publishForm.wordNum);
                    checkInputLength(num,$(opt.publishForm.wordNum),$(opt.publishForm.textarea));
                },
                contentFirst:function(id){
                    var text = document.getElementById(id);
                    if (document.selection) { //IE
                         var rng = text.createTextRange();
                         rng.collapse(true);
                         rng.moveStart("character",0)
                    }else if (text.selectionStart || (text.selectionStart == '0')) { // Mozilla/Netscape…
                        text.selectionStart = 0;
                        text.selectionEnd = 0;
                    }
                    text.focus();
                },
                initForm:function(id,callback,systemType){
                    var formObj    = $('#'+id),
                        buttonObj  = formObj.find('.buttonObj'),
                        contentObj = formObj.find('.contentObj'),
                        numObj     = formObj.find('.numObj');
              
                    userAutoTips({id:contentObj.attr('id'),url: opt.searchUrl});

                    var defType = {
                        emotions:true,
                        topic:true
                    }
                    defType = $.extend(defType, systemType);
                    initHtml(formObj,defType.emotions,defType.topic);
                    form(formObj,buttonObj,contentObj,numObj,callback);
                },
                plugin:{}
        });
        
        function OperateFactory(nowType){
            var post=function(type,otherParam){
                var param = {};
                for(var one in type.param){
                    if(opt[type.param[one]] != undefined){
                        param[one] = opt[type.param[one]];
                    }else{
                        param[one] = type.param[one];
                    }
                }
                if(otherParam){
                    param = $.extend(param, otherParam);
                }
                $.post(type.url,param,type.callback);
            };
            
            var weibo={
                countNew:{
                   url:U('weibo/index/countnew'),
                   param:{lastId:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                },
                loadNew:{
                    url:U('weibo/index/loadnew'),
                    param:{since:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                },
                deleteWeibo:{
                  url:U("weibo/Operate/delete")
                },
                comment:{
                    url:U("weibo/Index/loadcomment")
                },
                 //收藏
                favorite:{
                    url:U("weibo/Operate/stow")
                },
                //取消收藏
                unFavorite:{
                    url:U("weibo/Operate/unstow")
                },
                //转发
                transpond:{
                    url:"weibo/operate/transpond",
                    other:{title:"{:L('转发')}",closeable:true}
                },
                //关注话题
                followTopic:{
                    url:U('weibo/operate/followtopic')
                },
                unfollowTopic:{
                    url:U('weibo/operate/unfollowtopic')
                },
                quickpublish:{
                    url:U('weibo/operate/quickpublish')
                },
                loadMore:{
                    url:U('weibo/index/loadmore'),
                    param:{since:'sinceId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                }
            }
            
            var group={
                countNew:{
                   url:U('group/WeiboIndex/countnew'),
                   param:{lastId:'lastId',showfeed:'show_feed',gid:'gid',type:'weiboType'}
                },
                loadNew:{
                    url:U('group/WeiboIndex/loadnew'),
                    param:{since:'lastId',gid:'gid',type:'weiboType'}
                },
                comment:{
                    url:U("group/WeiboIndex/loadcomment"),
                    param:{gid:'gid'}
                },
                share:{
                    url:"group/WeiboOperate/shareWeibo",
                    param:{gid:'gid'},
                    other:{title:"{:L('分享到微博')}",closeable:true}
                },
                //转发
                transpond:{
                    url:"group/WeiboOperate/transpond",
                    param:{gid:'gid'},
                    other:{title:"{:L('转发到群聊')}",closeable:true}
                },
                loadMore:{
                    url:U('group/WeiboIndex/loadmore'),
                    param:{since:'sinceId',type:'weiboType',gid:'gid'}
                },
                deleteWeibo:{
                    url:U('group/WeiboOperate/delete'),
                    param:{gid:'gid'}
                }
            }
            
            var all={
                countNew:{
                   url:U('weibo/index/countnew'),
                   param:{hasUid:false,lastId:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                },
                loadNew:{
                    url:U('weibo/index/loadnew'),
                    param:{hasUid:false,since:'lastId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                },
                comment:{
                    url:weibo.comment.url   
                },
                //收藏
                favorite:{
                    url:weibo.favorite.url
                },
                //取消收藏
                unFavorite:{
                    url:weibo.unFavorite.url
                },
                //转发
                transpond:{
                    url:weibo.transpond.url,
                    other:weibo.transpond.other
                },
                //关注话题
                followTopic:{
                    url:weibo.transpond.url
                },
                unfollowTopic:{
                    url:weibo.unfollowTopic.url
                },
                quickpublish:{
                    url:weibo.quickpublish.url
                },
                deleteWeibo:{
                    url:weibo.deleteWeibo.url
                },
                loadMore:{
                    url:U('weibo/index/loadmore'),
                    param:{hasUid:false,since:'sinceId',showfeed:'show_feed',type:'weiboType',follow_gid:'follow_gid'}
                }
            }
            
            var type;
            switch(nowType){
                case opt.typeList.WEIBO:
                    type = weibo;
                    break;
                case opt.typeList.GROUP:
                    type = group;
                    break;
                case opt.typeList.ALL:
                    type = all;
                    break;
                default:
                    type = weibo;
            }
            
            this.loadbox = function(commond,params){
                var temp = type[commond];
                if(temp != undefined){ 
                    var param ={},postParam=[];
                    for(var one in temp.param){
                        if(opt[temp.param[one]] != undefined){
                            param[one] = opt[temp.param[one]];
                        }else{
                            param[one] = temp.param[one];
                        }
                    }
                    if(params){
                        param = $.extend(param, params);
                    }
                    for(var one in param){
                        postParam.push(one+"="+param[one]);
                    }
                    ui.box.load( U(temp.url,postParam ),temp.other);
                }
            }
            
            this.create = function(commond,callback,params){
                var temp = type[commond];
                if(temp != undefined){ 
                    temp.callback = callback;
                    post(temp,params);
                }
            }
        }
        
        //发布按钮状态
        var textareaStatus = function(type,obj){
            var obj = (obj==undefined)?$(opt.publishForm.button):obj;
            switch(type){
                case 'on':
                    obj.removeAttr('disabled').removeClass('btn_big_disable').addClass('btn_big_after');
                break;
                case 'off':
                    obj.attr('disabled','true').removeClass('btn_big_after').addClass('btn_big_disable');
                break;
                case 'sending':
                    obj.attr('disabled','true').removeClass('btn_big_after').addClass('btn_big_disable');
                break;
            }
        },
        checkInputLength = function(num,numObj,contentObj,buttonObj){
            var len = getLength(contentObj.val(), true);
            var wordNumObj = numObj;
            if(len==0){
                wordNumObj.css('color','').html('{:L('还可以输入')}<strong id="strconunt">'+ (num-len) + '</strong>{:L('字')}');
                textareaStatus('off',buttonObj);
            }else if( len > num ){
                wordNumObj.css('color','red').html('{:L('已超出')}<strong id="strconunt">'+ (len-num) +'</strong>{:L('字')}');
                textareaStatus('off',buttonObj);
            }else if( len <= num ){
                wordNumObj.css('color','').html('{:L('还可以输入')}<strong id="strconunt">'+ (num-len) + '</strong>{:L('字')}');
                textareaStatus('on',buttonObj);
            }
        },
        //发布前的检测
        before_publish = function(obj){
            if(_MID_ <= 0){
                ui.error('{:L('请登录后再操作')}'); return ;
            }
            obj = obj==undefined?$(opt.publishForm.textarea):obj;
            if( $.trim( obj.val() ) == '' ){
                ui.error('{:L('内容不能为空')}');     
                return false;
            }
            return true;
        },
        //发布后的处理
        after_publish = function(txt){
                self.reset();
                $(opt.weiboListDiv).prepend( txt ).slideDown('slow');
                var sync = [];
                $('#Sync').find('input[type="checkbox"]').each(function(){
                    if($(this).attr('checked')){
                        sync.push($(this));
                    }
                });
                
                $(opt.publishForm.form).clearForm();
                for(var one in sync){
                    sync[one].attr('checked',true);
                }
                ui.success('{:L('微博发布成功')}');
        },
        upCount=function(type){
            if(type=='weibo'){
                $("#miniblog_count").html( parseInt($('#miniblog_count').html())+1 );
            }
        },
        downCount=function(type){
            if(type=='weibo'){
                $("#miniblog_count").html( parseInt($('#miniblog_count').html())-1 );
            }
        },
        setLastIdByWeiboListDiv=function(){
            opt.lastId = $(opt.weiboListDiv).find('li:first').attr('id').split("_").pop();
        },
        form=function(formObj,buttonObj,contentObj,numObj,callback){
            var callbackStruct={
                keypress:function(formObj,buttonObj,contentObj,numObj){},
                blur:function(formObj,buttonObj,contentObj,numObj){},
                focus:function(formObj,buttonObj,contentObj,numObj){},
                enter:function(formObj,buttonObj,contentObj,numObj,txt){},
                after:function(formObj,buttonObj,contentObj,numObj){}
            },Interval;
            callback = $.extend(callbackStruct, callback);
            contentObj.keypress(function(event){
                var key = event.keyCode?event.keyCode:event.which?event.which:event.charCode;
                if (key == 27) {
                    clearInterval(Interval);
                }
                callback.keypress(formObj,buttonObj,contentObj,numObj);
                checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
            }).blur(function(){
                clearInterval(Interval);
                callback.blur(formObj,buttonObj,contentObj,numObj);
                checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
            }).focus(function(){
                if(callback.focus(formObj,buttonObj,contentObj,numObj)){
                     checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
                }
                 //微博字数监控
                clearInterval(Interval);
                Interval = setInterval(function(){
                             checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
                            },300);
            });
            callback.after(formObj,buttonObj,contentObj,numObj);
            checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
            
            var publish = function(){
                if(before_publish(contentObj)){
                   textareaStatus('sending',buttonObj);
                   var options = {
                       success:function(txt){
                           if(txt){
                               if(txt==0){
                                   ui.success('{:L('您发布的微博含有敏感词，请等待审核！')}');
                               }else if(txt=='submitlocked'){
								   ui.error('{:L('亲，您的操作太频繁了，请稍后再发布！')}');
							   }else if(txt=='duplicatecontent'){
                                   ui.error('{:L('亲，请不要连续发布相同的内容哦！')}');
                               }else{
                                   callback.enter(formObj,buttonObj,contentObj,numObj,txt);
                                   upCount('weibo');
                                   checkInputLength(_LENGTH_,numObj,contentObj,buttonObj);
                                   if(opt.lastId>0){
                                       setLastIdByWeiboListDiv();
                                   }
                                   textareaStatus('off',buttonObj);
                                   return ;
                               }
                           }else{
                               ui.error(buttonObj.attr('error'));
                           }
                           textareaStatus('on',buttonObj);
                       }
                   }
                   clearInterval(Interval); 
                   formObj.ajaxSubmit( options );
                }
             };
             buttonObj.click(publish);
            shortcut('ctrl+return', publish,{'target':formObj.attr('id')});
        };
        
        var events = {
                switchMenu:function(){
                   $(opt.childrenMenu).hover(
                            function(){ $(this).addClass(opt.hoverClass); },
                            function(){ $(this).removeClass(opt.hoverClass); }
                    );
                },
                loadNew:function(){
                    if(_MID_ <= 0){
                        return ;
                    }
                    $(opt.loadNewDiv).find('a').click(function() {
                        var limit = $(this).attr('limit');
                        operateFactory.create("loadNew",function(txt){
                            if(txt.indexOf('<TSAJAX>')==0){
                                if(txt.indexOf('<HASNEW>')!=-1) {
                                    $(opt.loadNewDiv).html('');
                                    $(opt.weiboListDiv).prepend(txt);
                                    setLastIdByWeiboListDiv();
                                }
                            }else{
                                location.reload();
                            }
                        },{limit:limit});
                    });
                },
                lodeMore:function(){
                     $(opt.loadMoreDiv).click(function() {
                        $(this).html('{:L('加载中...')}');
                        var self = this;
                        loadMoreCount = typeof(loadMoreCount) == 'undefined' ? 0 : loadMoreCount;
                        operateFactory.create("loadMore",function(txt){
                            clearInterval(isLoading);
                            isLoading = false;
                            loadMoreCount++ ;
                            if(parseInt(opt.sinceId) !== 0) {
                                $(opt.weiboListDiv).append(txt);
                            }
                            try{
                                // var tempSinceId = $(opt.weiboListDiv).find('li:last').attr('id').split("_").pop();
                                var tempSinceId = $(opt.weiboListDiv).find("li[id^='list_li_']").last().attr('id').split('_').pop();
                            }catch(e){
                                var tempSinceId = false;
                            }
                            opt.sinceId = typeof(sinceId) == 'undefined' ? tempSinceId : (tempSinceId || sinceId) ;
                            //判断没有更多数据时.不显示更多按钮
                            if(txt.indexOf('<HASNEW>')==-1){
                                $(self).parent().html('<span class="morefoot">{:L('没有更多数据了')}</span>');
                            }else{
                                //if(loadMoreCount<5){
                                    $(self).html('<span class="ico_morefoot"></span>{:L('more')}');
                                //}else{
                                    //显示分页
                                //  $(self).html('这里是分页');
                                //}
                            }
                            //loadmore后修改弹窗黑背景的高度
                            var obj = document.getElementById('boxy-modal-blackout');
                            if(obj !== null) {
                                $('#boxy-modal-blackout').css('height', document.body.clientHeight + 100);
                            }
                        });
                    });
                },
                comment:function(){
                     // 评论切换
                     $(opt.weiboList.comment.a).live('click',function(){
                         var id = $(this).attr('minid');
                         var $comment_list = $("#comment_list_"+id);
                         if( $comment_list.html() == '' ){
                             $comment_list.html(opt.loading);
                             operateFactory.create("comment",function(txt){
                                 if(_MID_ <= 0){
                                    $comment_list.html("") ;
                                    ui.error('{:L('请登录后再操作')}'); return ;
                                 }
                                 $comment_list.html( txt ) ;
                             },{id:id});
                         }else{
                             $comment_list.html("");
                         }
                      });
                      
                       $(opt.weiboList.comment.form).live("submit", function(){
                        if(document.getElementById('emotions') != null) {
                            $('#emotions').remove();
                        }
                        var _this = $(this);
                        var _comment_content = _this.find("textarea[name='comment_content']");
                        if( _comment_content.val()=='' ){
                            ui.error('{:L('内容不能为空')}');
                            return false;
                        }
                        var _button = _this.find("input[type='submit']");
                        _button.val( '{:L('评论中')}...').attr('disabled','true') ;
                        var options = {
                            success: function(txt) {
								if(txt=='submitlocked'){
									_this.find("input[type='submit']").val( '{:L('确定')}');
									_this.find("input[type='submit']").removeAttr('disabled') ;
									ui.error('{:L('亲，您的操作太频繁了，请稍后再发布！')}');
                                    return false;
								}else if(txt=='duplicatecontent'){
                                    _this.find("input[type='submit']").val( '{:L('确定')}');
                                    _this.find("input[type='submit']").removeAttr('disabled') ;
                                    ui.error('{:L('亲，请不要连续发布相同的内容哦！')}');
                                    return false;
                                }else if(txt=='emptycontent'){
                                    _this.find("input[type='submit']").val( '{:L('确定')}');
                                    _this.find("input[type='submit']").removeAttr('disabled') ;
                                    ui.error('{:L('内容不能为空')}');
                                    return false;
                                }
                                if(_this.attr('reload')=="true"){
                                    ui.success('{:L('回复成功')}');
                                    _button.removeAttr('disabled');
                                    setInterval("location.reload()",1000);
                                    return false;
                                }
                                txt = eval('('+txt+')');
                                 _this.find("input[type='submit']").val( '{:L('确定')}');
                                 _this.find("input[type='submit']").removeAttr('disabled') ;
                                 _comment_content.val('');
                                 _comment_content.css('height','');
                                $("#replyid_" + txt.data['weibo_id'] ).val('');
                                if(txt.status == undefined){
                                    $("#comment_list_before_"+txt.data['weibo_id']).after( txt.html ); 
                                    //更新评论数
                                    $("a[rel='comment'][minid='"+txt.data['weibo_id']+"']").html("{:L('comment')}("+txt.data['comment']+")");
                                }else{
                                    ui.error(txt.info);
                                }
                              
                            }
                        };
                        _this.ajaxSubmit( options );
                        return false;
                    });
                },
                scrollResize:function(){
                    if(opt.initForm){
                        var loadCount = 0;
                        $(window).bind('scroll resize',function(event){
                            if(loadCount <3 && !isLoading){
                                var bodyTop = document.documentElement.scrollTop + document.body.scrollTop;
                                //滚动到底部时出发函数
                                //滚动的当前位置+窗口的高度 >= 整个body的高度
                                if(bodyTop+$(window).height() >= $(document.body).height()){
                                    isLoading = true;
                                    $(opt.loadMoreDiv).click();
                                    loadCount ++;
                                }
                            }
                        });
                    }
                },
                leftClick:function(){
                     //左侧快捷发布
                     $(opt.leftAppPublish).each(function(){
                         $(this).children('span').click(function(event) {
                               window.open($(this).attr('url'),$(this).attr('target'));
                               event.stopPropagation();
                               return false;
                         });
                     })
                }
        }
            
        for(var one in events){
                events[one]();
        }
        
        var initHtml=function(parent,emtions,topic){
            var emotionsHtml = "<a href=\"javascript:void(0)\" target_set=\"content_publish\" onclick=\"ui.emotions(this)\" class=\"a52\">"
                + "<img class=\"icon_add_face_d\" src=\""+_THEME_+"/images/zw_img.gif\" />{:L('表情')}</a> ";
            var topicHtml    = "<a href=\"javascript:void(0)\" onclick=\"weibo.addtheme()\" class=\"a52\">"
                + "<img class=\"icon_add_topic_d\" src=\""+_THEME_+"/images/zw_img.gif\" />{:L('话题')}</a> ";
            var html = '';
            if(emtions){
                html += emotionsHtml;
            }
            if(topic){
                html += topicHtml;
            }
            parent.find(opt.publishForm.weiboType).prepend(html);
        }
                
        var start = function(){
            self.countNew();//激活定式更新微博数事件
            if(opt.initForm){
               self.initForm(opt.publishForm.form.split('#').pop(),{ enter:function(formObj,buttonObj,contentObj,numObj,txt){after_publish(txt);}});
            }
          
            return self;
        }
                
        return start();
    }
});

var CallBack = function(){
    return{
        Vote:{}
    }
}

CallBack.Vote = {
    addSuccess:function(data){ }
}
