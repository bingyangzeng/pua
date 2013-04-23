jQuery.fn.extend({
	highText : function (searchWords, htmlTag, tagClass) {
		return this.each(function() {
			$(this).html(function high(replaced, search, htmlTag, tagClass) {
				var pattarn = search.replace(/\b(\w+)\b/g, "($1)").replace(/\s+/g, "|");
				
				return replaced.replace(new RegExp(pattarn, "ig"), function(keyword) {
					return $("<" + htmlTag + " class=" + tagClass + ">" + keyword + "</" + htmlTag + ">").outerHTML();
				});
			}($(this).text(), searchWords, htmlTag, tagClass));
		});
	},
	outerHTML : function(s) {
		return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
	}
});

// Test:
/*$(document).ready(function () {
	$('.footer a').highText('关于', 'span', 't');
});*/

function ajax_request(url, params)
{	
	if (params)
	{
		$.post(url, params, function (result) {
			if (result.err)
			{
				$.alert(result.err);
			}
			else if (result.rsm && result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}, 'json').error(function (error) { if ($.trim(error.responseText) != '') { alert('发生错误, 返回的信息: ' + error.responseText); } });
	}
	else
	{
		$.get(url, function (result) {
			if (result.err)
			{
				$.alert(result.err);
			}
			else if (result.rsm && result.rsm.url)
			{
				window.location = decodeURIComponent(result.rsm.url);
			}
			else
			{
				window.location.reload();
			}
		}, 'json').error(function (error) { if ($.trim(error.responseText) != '') { alert('发生错误, 返回的信息: ' + error.responseText); } });
	}
	
	return false;
}

function ajax_post(formEl, processer)	// 表单对象，用 jQuery 获取，回调函数名
{	
	if (typeof(processer) != 'function')
	{
		processer = _ajax_post_processer_qAlert;
	}
	else
	{
		if (document.getElementById('tip_error_message'))
		{
			$('#tip_error_message').hide();
		}
		
		$('span.err').hide();
	}
	
	var custom_data = {_post_type:'ajax'};
	
	formEl.ajaxSubmit({
		dataType: 'json',
		data: custom_data,
		success: processer,
		error:	function (error) { if ($.trim(error.responseText) != '') { alert('发生错误, 返回的信息: ' + error.responseText); } }
	});
}

function _ajax_post_processer_qAlert(result)
{
	return _ajax_post_processer(result)
}

function _ajax_post_processer(result)
{	
	if (typeof(result.errno) == 'undefined')
	{
		$.alert(result);
	}
	else if (result.errno != 1)
	{
		$.alert(result.err);
	}
	else
	{		
		if (result.rsm && result.rsm.url)
		{
			window.location = decodeURIComponent(result.rsm.url);
		}
		else
		{
			window.location.reload();
		}
	}
}

function _ajax_post_i_alert_processer(result)
{
	if (typeof(result.errno) == 'undefined')
	{
		alert(result);
	}
	else if (result.errno != 1)
	{
		alert(result.err);
	}
	else
	{
		if (result.rsm && result.rsm.url)
		{
			window.location = decodeURIComponent(result.rsm.url);
		}
		else
		{
			$('#i_gloBoxl').remove();
		}
	}
}

function _ajax_post_alert_processer(result)
{
	if (typeof(result.errno) == 'undefined')
	{
		alert(result);
	}
	else if (result.errno != 1)
	{
		alert(result.err);
	}
	else
	{
		if (result.rsm && result.rsm.url)
		{
			window.location = decodeURIComponent(result.rsm.url);
		}
		else
		{
			window.location.reload();
		}
	}
}

function focus_question(el, text_el, question_id)
{
	if (el.hasClass('cur'))
	{
		text_el.html('关注');
	}
	else
	{
		text_el.html('取消关注');
	}
	
	el.addClass('load');
	
	$.get(G_BASE_URL + '/question/ajax/focus/question_id-' + question_id, function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
			}
			else
			{
				el.removeClass('cur');
			}
		}
		else
		{
			if (data.err)
			{
				$.alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function focus_topic(el, text_el, topic_id)
{
	if (el.hasClass('cur'))
	{
		text_el.html('关注');
	}
	else
	{
		text_el.html('取消关注');
	}
	
	el.addClass('load');
	
	$.get(G_BASE_URL + '/topic/ajax/focus_topic/topic_id-' + topic_id, function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
			}
			else
			{
				el.removeClass('cur');
			}
		}
		else
		{
			if (data.err)
			{
				$.alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function follow_people(el, text_el, uid)
{
	if (el.hasClass('cur'))
	{
		text_el.html('关注');
	}
	else
	{
		text_el.html('取消关注');
	}
	
	el.addClass('load');
	
	$.get(G_BASE_URL + '/follow/ajax/follow_people/uid-' + uid, function (data)
	{
		if (data.errno == 1)
		{
			if (data.rsm.type == 'add')
			{
				el.addClass('cur');
			}
			else
			{
				el.removeClass('cur');
			}
		}
		else
		{
			if (data.err)
			{
				$.alert(data.err);
			}
			
			if (data.rsm.url)
			{
				window.location = decodeURIComponent(data.rsm.url);
			}
		}
		
		el.removeClass('load');
	}, 'json');
}

function check_notifications()
{
	if (G_USER_ID == 0)
	{
		return false;
	}
	
	$.get(G_BASE_URL + '/home/ajax/notifications/', function (result) {
		
		$('.inbox_num').html(Number(result.rsm.inbox_num));
		
		last_unread_notification = G_UNREAD_NOTIFICATION;
		
		G_UNREAD_NOTIFICATION = Number(result.rsm.notifications_num);

		if (G_UNREAD_NOTIFICATION > 0)
		{
			if (G_UNREAD_NOTIFICATION != last_unread_notification)
			{
				reload_notification_list();
				
				$('#notifications_num').html(G_UNREAD_NOTIFICATION);
			}
		}
		else
		{
			if ($('#header_notification_list').length > 0)
			{
				$("#header_notification_list").html('<p style="padding: 0" align="center">没有未读通知</p>').next('[name=operater]').hide();
			}

			if ($("#index_notification").length > 0)
			{
				$("#index_notification").fadeOut().find('[name=notification_unread_num]').html(0).parent().next('ul#notification_list').html('');
			}

			if (('#tab_all_notifications').length > 0)
			{
				$('#tab_all_notifications').click();
			}
		}
		
		if (Number(result.rsm.notifications_num) > 0)
		{
			document.title = '(' + (Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num)) + ') ' + document_title;
			
			$('#notifications_num').show();
		}
		else
		{
			$('#notifications_num').hide();
		}
		
		if (Number(result.rsm.inbox_num) > 0)
		{
			$('.inbox_num').show();
		}
		else
		{
			$('.inbox_num').hide();
		}
		
		if (((Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num))) > 0)
		{
			document.title = '(' + (Number(result.rsm.notifications_num) + Number(result.rsm.inbox_num)) + ') ' + document_title;
		}
	}, 'json');
}

function reload_notification_list()
{
	if ($("#index_notification").length > 0)
	{
		$("#index_notification").fadeIn().find('[name=notification_unread_num]').html(G_UNREAD_NOTIFICATION).parent().next('ul#notification_list').html('<p align="center" style="padding: 15px 0"><img src="' + G_STATIC_URL + '/common/loading_b.gif"/></p>');

		$.get(G_BASE_URL + '/notifications/ajax/list/flag-0__page-0', function (index_response)
		{
			if (index_response.length)
			{
				$("#index_notification").find('ul#notification_list').html(index_response);
				
				notification_show(5);
			}
		});
	}

	if ($("#header_notification_list").length > 0)
	{
		$("#header_notification_list").html('<p align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif"/></p>');

		$.get(G_BASE_URL + '/notifications/ajax/list/flag-0__page-0__per_page-5__template-header_list', function (response)
		{
			if (response.length)
			{
				$("#header_notification_list").html(response).next('[name=operater]').show();
				
			}
			else
			{
				$("#header_notification_list").html('<p style="padding: 0" align="center">没有未读通知</p>').next('[name=operater]').hide();
			}
		});
	}
}

function read_notification(notification_id, el, reload)
{
	if (notification_id)
	{
		el.remove();

		notification_show(5);
		
		if ($("#announce_num").length > 0)
		{
			$("#announce_num").html(String(G_UNREAD_NOTIFICATION-1));
		}
		
		if ($("#notifications_num").length > 0)
		{
			$("#notifications_num").html(String(G_UNREAD_NOTIFICATION-1));
		}
		
		var url = G_BASE_URL + '/notifications/ajax/read_notification/notification_id-' + notification_id + '__read_type-1';
	}
	else
	{
		if ($("#index_notification").length > 0)
		{
			$("#index_notification").fadeOut();
		}
		
		var url = G_BASE_URL + '/notifications/ajax/read_notification/read_type-0';
	}
	
	$.get(url, function (respose)
	{
		check_notifications();

		if (reload)
		{
			window.location.reload();
		}
	});
}

function notification_show(max)
{
	if ($('#index_notification').length > 0)
	{
		var n_count = 0;
		
		$('#index_notification').find('ul#notification_list').find("li").each(function()
		{
			if (n_count < 5)
			{
				$(this).show();
			}
			else
			{
				$(this).hide();
			}
			
			n_count++;
		});
		
		if ($('#index_notification').find('ul#notification_list').find("li").size() == 0)
		{
			$('#index_notification').fadeOut();
		}
	}
}

function ajax_load(url, target)
{
	$(target).html('<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>');
	
	$.get(url, function (response)
		{
			if (response.length)
			{
				$(target).html(response);
			}
			else
			{
				$(target).html('<p style="padding: 15px 0" align="center">没有内容</p>');
			}
	});
}

var _bp_more_o_inners = new Array();
var _bp_more_pages = new Array();

function bp_more_load(url, bp_more_o_inner, target_el, start_page, callback_func)
{
	if (!bp_more_o_inner.attr('id'))
	{
		return false;
	}
	
	if (!start_page)
	{
		start_page = 0
	}
	
	_bp_more_pages[bp_more_o_inner.attr('id')] = start_page;
	
	_bp_more_o_inners[bp_more_o_inner.attr('id')] = bp_more_o_inner.html();
	
	bp_more_o_inner.unbind('click');
	
	bp_more_o_inner.bind('click', function () {
		var _this = this;
			
		$(this).addClass('loading');
		
		$(this).find('a').html('正在载入...');
			
		$.get(url + '__page-' + _bp_more_pages[bp_more_o_inner.attr('id')], function (response)
		{
			if ($.trim(response) != '')
			{
				if (_bp_more_pages[bp_more_o_inner.attr('id')] == start_page)
				{
					target_el.html(response);
				}
				else
				{
					target_el.append(response);
				}
							
				_bp_more_pages[bp_more_o_inner.attr('id')]++; 
				
				$(_this).html(_bp_more_o_inners[bp_more_o_inner.attr('id')]);
			}
			else
			{
				if (_bp_more_pages[bp_more_o_inner.attr('id')] == start_page)
				{
					target_el.html('<p style="padding: 15px 0" align="center">没有内容</p>');
				}
							
				$(_this).addClass('disabled').unbind('click').bind('click', function () { return false; });
						
				$(_this).find('a').html('没有更多了');
			}
				
			$(_this).removeClass('loading');
			
			if (callback_func != null)
			{
				callback_func();
			}
		});
			
		return false;
	});
	
	bp_more_o_inner.click();
}

function content_switcher(hide_el, show_el)
{
	hide_el.hide();
	show_el.fadeIn();
}

function _tips_form_processer(result)
{
	if (typeof(result.errno) == 'undefined')
	{
		alert(result);
	}
	else if (result.errno != 1)
	{		
		if (typeof(result.rsm) == 'undefined')
		{
			if (document.getElementById('tip_error_message'))
			{
				$('#tip_error_message').html(result.err).show();
			}	
			else
			{
				$.alert(result.err);
			}
		}
		else if (result.rsm)
		{	
			var selecter = 'input[name=' + result.rsm.input + '], select[name=' + result.rsm.input + ']';
			
			if (document.getElementById('tip_' + result.rsm.tips_id))
			{
				$('#tip_' + result.rsm.tips_id).html(result.err).show();
			}
			else if (document.getElementById(result.rsm.tips_id))
			{
				$('#' + result.rsm.tips_id).html(result.err).show();
			}
			else if ($('#tip_' + $(selecter).attr('id')).attr('id'))
			{
				if (!$('#tip_' + $(selecter).attr('id')).hasClass('default_err') && !$('#tip_' + $(selecter).attr('id')).hasClass('all_err_tips'))
				{
					$('#tip_' + $(selecter).attr('id')).removeClass().addClass('err').html(result.err).show();
				}
				else
				{
					$('#tip_' + $(selecter).attr('id')).html(result.err).show();
				}
			}
			else if (document.getElementById('tip_error_message'))
			{
				$('#tip_error_message').html(result.err).show();
			}		
			else
			{
				$.alert(result.err);
			}
		}
		else
		{
			if (document.getElementById('tip_error_message'))
			{
				$('#tip_error_message').html(result.err).show();
			}	
			else
			{
				$.alert(result.err);
			}
		}
	}
	else
	{
		if (result.rsm && result.rsm.url)
		{
			window.location = decodeURIComponent(result.rsm.url);
		}
		else if (result.err && document.getElementById('tip_success_message'))
		{
			$.scrollTo(0, 800, {queue:true});
			
			$('#tip_success_message').html(result.err).fadeIn();
			
			setTimeout(function () {
				$('#tip_success_message').fadeOut();
			}, 3000);
		}
		else
		{
			window.location.reload();
		}
	}
}

function hightlight(el, class_name)
{
	if (el.hasClass(class_name))
	{
		return true;
	}
	
	//window.scrollTo(0, (el.position()['top'] - 5));
	
	var hightlight_timer_front = setInterval(function () {
		el.addClass(class_name);
	}, 500);
	
	var hightlight_timer_background = setInterval(function () {
		el.removeClass(class_name);
	}, 600);
	
	setTimeout(function () {
		clearInterval(hightlight_timer_front);
		clearInterval(hightlight_timer_background);
		
		el.addClass(class_name);
	}, 1200);
	
	setTimeout(function () {
		el.removeClass(class_name);
	}, 6000);
}

function nl2br(str)
{
	return str.replace(new RegExp("\r\n|\n\r|\r|\n", "g"), "<br />");
}

function init_img_uploader(upload_url, upload_name, upload_element, upload_status_elememt, perview_element)
{
    return new AjaxUpload(upload_element, {
        action: upload_url,
        name: upload_name,
        responseType: 'json',
        
        onSubmit: function (file, ext) {
            if (!new RegExp('(png|jpe|jpg|jpeg|gif)$', 'i').test(ext))
            {
                alert('上传失败: 只支持 jpg、gif、png 格式的图片文件');
                
                return false;
            }
			
            this.disable();
            
            if (upload_status_elememt)
            {
            	upload_status_elememt.show();
            }
        },
        
        onComplete: function (file, response) {
            this.enable();
            
			if (upload_status_elememt)
            {
            	upload_status_elememt.hide();
            }
            
            if (response.errno == -1)
			{
            	alert(response.err);
        	}
        	else
        	{
        		if (typeof(perview_element.attr('src')) != 'undefined')
        		{
	        		perview_element.attr('src', response.rsm.preview + '?' + Math.floor(Math.random() * 10000));
        		}
        		else
        		{
	        		perview_element.css('background-image', 'url(' + response.rsm.preview + '?' + Math.floor(Math.random() * 10000) + ')');
        		}
            }		
        }
    });
}

function init_avatar_uploader(upload_element, upload_status_elememt, avatar_element)
{
	return init_img_uploader(G_BASE_URL + '/account/ajax/avatar_upload/', 'user_avatar', upload_element, upload_status_elememt, avatar_element);
}

function init_fileuploader(element_id, action_url)
{
	if (!document.getElementById(element_id))
	{
		return false;
	}
	
	return new _ajax_uploader.FileUploader({
		element: document.getElementById(element_id),
		action: action_url,
		debug: false
	});
}

function share_content(share_type, target_id, focus_tab)
{
	if (typeof focus_tab == 'undefined')
	{
		focus_tab = "weibo";
	}

	switch(share_type)
	{
		//请求分享内容
		case 'question' : 
			url = G_BASE_URL + "/question/ajax/question_share_txt/question_id-" + target_id;
			share_title = "分享问题";
			break;
		case 'answer' : 
			url = G_BASE_URL + "/question/ajax/answer_share_txt/answer_id-" + target_id;
			share_title = "分享回复";
			break;
		case 'topic' : 
			url = G_BASE_URL + "/question/ajax/topic_share_txt/topic_id-" + target_id;
			share_title = "分享话题";
			break;
		default: 
			return false;
	}

	var title = '<a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" class="close_ti" title="点击关闭对话框 "><em>close</em></a>' + share_title;
	
	new DialogBox_show('500', '<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>', title, '', function()
	{
		$('#w_tagPupD').css('marginTop', '-140px');
	});
	
	$.get(url, function(result)
	{
		if (result.errno == '1')
		{
			$('.w_Contxt').html(show_share_box());

			$('#weibo_share').html('<div id="bdshare" class="bdshare_t bds_tools get-codes-bdshare" data="{text:\'' + result.rsm.share_txt.sns_share +  '\',url:\'' + result.rsm.share_txt.url + '\', snsKey: {\'tsina\': \'726571307\',\'tqq\': \'801158211\'}, review:\'off\', render:false}"><a class="bds_qzone">QQ空间</a><a class="bds_tsina">新浪微博</a><a class="bds_tqq">腾讯微博</a><a class="bds_hi">百度空间</a><a class="bds_t163">网易微博</a><a class="bds_tqf">朋友网</a><a class="bds_kaixin001">开心网</a><a class="bds_renren">人人网</a><a class="bds_douban">豆瓣网</a><a class="bds_taobao">淘宝</a><a class="bds_fbook">Facebook</a><a class="bds_twi">Twitter</a><a class="bds_ms">Myspace</a><a class="bds_deli">Delicious</a><a class="bds_linkedin">linkedin</a></div><script type="text/javascript" src="http://bdimg.share.baidu.com/static/js/bds_s_v2.js?cdnversion=' + new Date().getHours() + '"></script>');
			
			$("[name=model_type]").val(share_type);
			$("[name=target_id]").val(target_id);
			
			$(".txt_area[rel=mail]").html(result.rsm.share_txt.mail);
			$(".txt_area[rel=message]").html(result.rsm.share_txt.message);
			
			$("#menu_s").find("li[rel=" + focus_tab + "]").click();
		}
	}, 'json');

}

function show_share_box()
{
	var html = '<ul id="menu_s">' +
			'<li class="cur" onclick="elementClickEvent.tabs(this); $(\'#tip_share_send\').hide();" rel="weibo" uid="0">站外分享</li>'+
			'<li onclick="elementClickEvent.tabs(this); $(\'#tip_share_send\').hide();" rel="mail" uid="1">邮件分享</li>';
	
	if (G_USER_ID)
	{
		html +=	'<li onclick="elementClickEvent.tabs(this); $(\'#tip_share_send\').hide();" rel="letter" uid="2">私信分享</li>';
	}
	
	html += '</ul><div class="default_error_share hide" id="tip_share_send"></div>';
	
	html += '<div id="weibo_share" class="class_share hide"></div>';

	html += '<div class="class_share hide" id="mail_share"><form id="mail_share_form" onSubmit="return false;" method="post" action="'+G_BASE_URL + '/question/ajax/send_share_email/"><input type="hidden" name="model_type" value=""/><input type="hidden" name="target_id" value=""/><ul class="txt_list">'+
			'<li><label for="inputsType_txt">收件人：</label><input type="text" name="email_address" class="txt_input"/></li>'+
			'<li><label for="inputsType_area">内容：</label><textarea rel="mail" class="txt_area" name="email_message"></textarea></li>'+
			'</ul><p class="tr"><a href="javascript:;" onclick="ajax_post($(\'#mail_share_form\'), _pm_form_processer); $(\'#tip_share_send\').hide();" class="set_msg">发送</a><a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form></div>';

	if (G_USER_ID)
	{
		html += '<div class="class_share hide" id="letter_share"><form id="message_share_form" onSubmit="return false;" method="post" action="'+G_BASE_URL + '/inbox/ajax/send/click_id-share_box_close__tips_id-share_send"><ul class="txt_list">'+
			'<li><label for="userShare_txt">发给：</label><input type="text" name="recipient" class="txt_input" id="userShare_txt"/><div class="ajax_date hide"></div></li>'+
			'<li><label for="inputsType_area">内容：</label><textarea name="message" rel="message" class="txt_area"></textarea></li></ul>'+
			'<p class="tr"><a href="javascript:;" onclick="ajax_post($(\'#message_share_form\'), _pm_form_processer); $(\'#tip_share_send\').hide(); return false;" class="set_msg">发送</a><a href="javascript:;" id="share_box_close" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form></div>';
	}

	return html;
}

function htmlspecialchars(text)  
{  
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function delete_draft(item_id, type)
{
	$.post(G_BASE_URL + '/account/ajax/delete_draft/', 'item_id=' + item_id + '&type=' + type, function (result) {
		if (result.errno != 1)
		{
			$.alert(result.err);
		}
	}, 'json');
}

function agree_vote(answer_id, value)
{
	$.post(G_BASE_URL + '/question/ajax/answer_vote/', "answer_id=" + answer_id + "&value=" + value, function (result) {
		if (result.errno == -1)
		{
			$.alert(result.err);
		}
	}, 'json');
}

//问题-不感兴趣
function question_uninterested(el, question_id)
{
	el.fadeOut();
	
	$.post(G_BASE_URL + '/question/ajax/uninterested/', 'question_id=' + question_id, function (result) {
		if (result.errno != '1')
		{
			alert(result.err);
		}
	}, 'json');
}

function question_invite_delete(el, question_invite_id)
{
	$.post(G_BASE_URL + '/question/ajax/question_invite_delete/', 'question_invite_id=' + question_invite_id, function (result) {
		if (result.errno == '1')
		{
			el.parent().parent().remove();
		}
		else
		{
			alert(result.rsm.err);
		}
	}, 'json');
}

function toggle_comments(item_id, type_name,e,callback)
{	
	if ($('#' + type_name + '_comments_' + item_id).css('display') == 'none')
	{
		if ($('#' + type_name + '_comments_' + item_id + ' div[name=comments_list]').html() == '')
		{
			reload_comments_list(item_id, item_id, type_name);
		}
		
		$('#' + type_name + '_comments_' + item_id).fadeIn('normal'); 
		
	}
	else
	{
		$('#' + type_name + '_comments_' + item_id).hide();
	}
	callback != null ? callback.call(this,item_id,type_name,e) :''
}

function reload_comments_list(item_id, element_id, type_name)
{
	var _element_id = element_id;
	
	$('#' + type_name + '_comments_' + _element_id + ' div[name=comments_list]').html('<p style="padding: 10px 0" align="center"><img src="' + G_STATIC_URL + '/common/load.gif" alt="" /></p>');
	
	$.get(G_BASE_URL + '/question/ajax/get_' + type_name + '_comments/' + type_name + '_id-' + item_id, function (data) {
		$('#' + type_name + '_comments_' + _element_id + ' div[name=comments_list]').html(data);
	});
}

function header_message(message)
{
	$('<div/>').addClass('reply_div').html('<p><!--<span title="点击取消显示" class="s"></span>-->' + message + '</p>').insertBefore($('#default_msg').find('p.transparent'));
}

function save_comment(save_button_el)
{
	$(save_button_el).attr('_onclick', $(save_button_el).attr('onclick')).addClass('disabled').removeAttr('onclick').addClass('save_comment');
	
	ajax_post($(save_button_el).parents('form'), _comments_form_processer);
}

function _comments_form_processer(result)
{
	$.each($('a.save_comment.disabled'), function (i, e) {
		$(this).attr('onclick', $(this).attr('_onclick')).removeAttr('_onclick').removeClass('disabled').removeClass('save_comment');
	});
	
	if (result.errno != 1)
	{
		$.alert(result.err);
	}
	else
	{
		reload_comments_list(result.rsm.item_id, result.rsm.item_id, result.rsm.type_name);

		$('#' + result.rsm.type_name + '_comments_' + result.rsm.item_id + ' form input').val("");

		$('#' + result.rsm.type_name + '_comments_' + result.rsm.item_id + ' form').fadeOut();
	}
}

function remove_comment(el, type, comment_id)
{
	$(el).parents('li').fadeOut('slow',function(){
		$(this).remove();
		$.get(G_BASE_URL + '/question/ajax/remove_comment/type-' + type + '__comment_id-' + comment_id);
	});
}

function insert_attach(el, attach_id, attach_tag)
{
	$(el).parents('form').find('textarea').val($(el).parents('form').find('textarea').val() + "\n[" + attach_tag + "]" + attach_id + "[/" + attach_tag + "]\n");
}

function question_thanks(question_id, element)
{
	$.post(G_BASE_URL + '/question/ajax/question_thanks/', 'question_id=' + question_id, function (result) {
		if (result.errno != 1)
		{
			$.alert(result.err);
		}
		else if (result.rsm.action == 'add')
		{
			$(element).html('已感谢');
			$(element).removeAttr('onclick');
		}
		else
		{
			$(element).html('感谢');
		}
	}, 'json');
}

function answer_user_rate(answer_id, type, element)
{
	$.post(G_BASE_URL + '/question/ajax/question_answer_rate/', 'type=' + type + '&answer_id=' + answer_id, function (result) {
		
		if (result.errno != 1)
		{
			$.alert(result.err);
		}
		else if (result.errno == 1)
		{
			switch (type)
			{
				case 'thanks':
					if (result.rsm.action == 'add')
					{
						$(element).html('已感谢');
						$(element).removeAttr('onclick');
					}
					else
					{
						$(element).html('感谢');
					}
				break;
				
				case 'uninterested':
					if (result.rsm.action == 'add')
					{
						$(element).html('撤消没有帮助');
					}
					else
					{
						$(element).html('没有帮助');
					}
				break;
			}
		}
	}, 'json');
}

function report_content(type, target_id)
{
	var url = G_BASE_URL + '/question/ajax/report_reason/';
	var title = '<a href="javascript:;" onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" class="close_ti" title="点击关闭对话框 "><em>close</em></a>举报';
	
	new DialogBox_show('500', '<p style="padding: 15px 0" align="center"><img src="' + G_STATIC_URL + '/common/loading_b.gif" alt="" /></p>', title, '', function()
	{
		$('#w_tagPupD').css('marginTop', '-140px');
	});

	$.get(url, function(result)
	{
		if (result.errno == '1')
		{
			var contxt = '<div class="default_error_share hide" style="margin-bottom:10px;margin-top:0px;" id="tip_share_send">asdfsf</div><div class="class_share" style="padding:0;"><form action="' + G_BASE_URL + '/question/ajax/save_report/" method="post" onsubmit="return false;" id="message_share_form"><input type="hidden" name="type" value="' + type + '"/><input type="hidden" name="target_id" value="' + target_id + '"/><ul class="txt_list"><li style="position: relative; z-index: 10; width: 360px;"><label for="userShare_txt">可选理由：</label><select type="text" id="reason_select" class="txt_input" name="reason_select" onChange="$(this).parent().next().find(\'[name=reason]\').val($(this).val())" style="width:100px;"><option value="" onClick="">请选择</option>';

			$(result.rsm).each(function (i, d)
			{
				contxt += '<option value="' + d + '" onClick="">' + d + '</option>';
			});

			contxt += '</select><div class="ajax_date hide"></div></li><li><label for="inputsType_area">举报理由：</label><textarea class="txt_area" name="reason" id="reason" style="height:80px;"></textarea></li></ul><p class="tr"><a class="set_msg" onclick="ajax_post($(\'#message_share_form\'), _ajax_post_processer); $(\'#tip_share_send\').hide(); return false;" href="javascript:;">确定</a><a onclick="hidePupBox(\'w_tagPupD\',\'w_mask\');" id="share_box_close" href="javascript:;">取消</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></form></div>';
			
			$('.w_Contxt').html(contxt);
		}
	}, 'json');
}

function htmldecode(str) {
	str = str.replace(/&amp;/gi, '&');
	str = str.replace(/&nbsp;/gi, ' ');
	str = str.replace(/&quot;/gi, '"');
	str = str.replace(/&#39;/g, "'");
	str = str.replace(/&lt;/gi, '<');
	str = str.replace(/&gt;/gi, '>');
	str = str.replace(/<br[^>]*>(?:(rn)|r|n)?/gi, 'n');
	return str;
}