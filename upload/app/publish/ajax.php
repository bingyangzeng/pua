<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Anwsion dev team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);


if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		$rule_action['guest'] = array();
		$rule_action['user'] = array();
		$rule_action['actions'] = array();
		
		return $rule_action;
	}
	
	function setup()
	{
		HTTP::no_cache_header();
	}

	public function fetch_question_category_action()
	{
		if (get_setting('category_enable') == 'Y')
		{
			echo $this->model('system')->build_category_json('question', 0, $question_info['category_id']);
		}
		else
		{
			echo json_encode(array());
		}
		
		exit;
	}
	
	public function attach_upload_action()
	{
		if (get_setting('upload_enable') != 'Y' OR !$_GET['id'])
		{
			die;
		}
		
		switch ($_GET['id'])
		{
			case 'question':
				$item_type = 'questions';
			break;
			
			default:
				$item_type = 'answer';
				
				$_GET['id'] = 'answer';
			break;
		}
		
		AWS_APP::upload()->initialize(array(
			'allowed_types' => get_setting('allowed_upload_types'),
			'upload_path' => get_setting('upload_dir') . '/' . $item_type . '/' . date('Ymd'),
			'is_image' => FALSE,
			'max_size' => (get_setting('upload_size_limit') * 1024)
		));
		
		if (isset($_GET['qqfile']))
		{
			AWS_APP::upload()->do_upload($_GET['qqfile'], true);
		}
		else if (isset($_FILES['qqfile']))
		{
			AWS_APP::upload()->do_upload('qqfile');
		}
		else
		{
			return false;
		}
				
		if (AWS_APP::upload()->get_error())
		{
			switch (AWS_APP::upload()->get_error())
			{
				default:
					die("{'error':'错误代码: " . AWS_APP::upload()->get_error() . "'}");
				break;
				
				case 'upload_invalid_filetype':
					die("{'error':'文件类型无效'}");
				break;	
				
				case 'upload_invalid_filesize':
					die("{'error':'文件尺寸过大, 最大允许尺寸为 " . get_setting('upload_size_limit') .  " KB'}");
				break;
			}
		}
		
		if (! $upload_data = AWS_APP::upload()->data())
		{
			die("{'error':'上传失败, 请与管理员联系'}");
		}
		
		if ($upload_data['is_image'] == 1)
		{
			foreach(AWS_APP::config()->get('image')->attachment_thumbnail AS $key => $val)
			{			
				$thumb_file[$key] = $upload_data['file_path'] . $val['w'] . 'x' . $val['h'] . '_' . basename($upload_data['full_path']);
				
				AWS_APP::image()->initialize(array(
					'quality' => 90,
					'source_image' => $upload_data['full_path'],
					'new_image' => $thumb_file[$key],
					'width' => $val['w'],
					'height' => $val['h']
				))->resize();	
			}
			
			if ($thumb_file['square'])
			{
				$thumb = get_setting('upload_url') . '/' . $item_type . '/' .  date('Ymd') . '/' . basename($thumb_file['square']);
			}
		}
		
		$attach_id = $this->model('publish')->add_attach($_GET['id'], $upload_data['orig_name'], $_GET['attach_access_key'], time(), basename($upload_data['full_path']), $thumb);
		
		$output = array(
			'success' => true,
			'delete_url' => get_js_url('/publish/ajax/remove_attach/attach_id-' . base64_encode(H::encode_hash(array(
				'attach_id' => $attach_id, 
				'access_key' => $_GET['attach_access_key']
			)))),
			'attach_id' => $attach_id,
			'attach_tag' => 'attach'
			
		);
		
		if ($thumb)
		{
			$output['thumb'] = $thumb;
		}
		else
		{
			$output['class_name'] = $this->model('publish')->get_file_class(basename($upload_data['full_path']));
		}
		
		echo htmlspecialchars(json_encode($output), ENT_NOQUOTES);
	}
	
	public function question_attach_edit_list_action()
	{
		$question_id = $_POST['question_id'];
		
		if (! $question_info = $this->model('question')->get_question_info_by_id($question_id))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '无法获取附件列表'));
		}
		
		if ($question_info['published_uid'] != $this->user_id AND !$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '你没有权限编辑这个附件列表'));
		}
		
		if ($question_attach = $this->model('publish')->get_attach('question', $question_id))
		{
			foreach ($question_attach as $attach_id => $val)
			{
				$question_attach[$attach_id]['class_name'] = $this->model('publish')->get_file_class($val['file_name']);
				
				$question_attach[$attach_id]['delete_link'] = get_js_url('/publish/ajax/remove_attach/attach_id-' . base64_encode(H::encode_hash(array(
					'attach_id' => $attach_id, 
					'access_key' => $val['access_key']
				))));
				
				$question_attach[$attach_id]['attach_id'] = $attach_id;
				$question_attach[$attach_id]['attach_tag'] = 'attach';
			}
		}
		
		H::ajax_json_output(AWS_APP::RSM(array(
			'attachs' => $question_attach
		), 1, ''));
	}

	public function answer_attach_edit_list_action()
	{
		$answer_id = $_POST['answer_id'];
		
		if (!$answer_info = $this->model('answer')->get_answer_by_id($answer_id))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '回复不存在'));
		}
		
		if ($answer_info['uid'] != $this->user_id AND !$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '你没有权限编辑这个附件列表'));
		}
		
		if ($answer_attach = $this->model('publish')->get_attach('answer', $answer_id))
		{
			foreach ($answer_attach as $attach_id => $val)
			{
				$answer_attach[$attach_id]['class_name'] = $this->model('publish')->get_file_class($val['file_name']);
				$answer_attach[$attach_id]['delete_link'] = get_js_url('/publish/ajax/remove_attach/attach_id-' . base64_encode(H::encode_hash(array(
					'attach_id' => $attach_id, 
					'access_key' => $val['access_key']
				))));
				
				$answer_attach[$attach_id]['attach_id'] = $attach_id;
				$answer_attach[$attach_id]['attach_tag'] = 'attach';
			}
		}
				
		H::ajax_json_output(AWS_APP::RSM(array(
			'attachs' => $answer_attach
		), "1", ''));
	}

	public function remove_attach_action()
	{
		$attach_info = H::decode_hash(base64_decode($_GET['attach_id']));
		
		$this->model('publish')->remove_attach($attach_info['attach_id'], $attach_info['access_key']);
		
		H::ajax_json_output(AWS_APP::RSM(null, 1, '附件删除成功'));
	}

	function modify_question_action()
	{
		$question_id = intval($_POST['question_id']);
		
		$question_info = $this->model('question')->get_question_info_by_id($question_id);
		
		if (empty($question_info))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '问题不存在'));
		}
		
		if ($question_info['lock'] && !($this->user_info['permission']['is_administortar'] OR $this->user_info['permission']['is_moderator']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '锁定问题不能编辑。'));
		}
		
		if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] AND !$this->user_info['permission']['edit_question'])
		{			
			if ($question_info['published_uid'] != $this->user_id)
			{
				H::ajax_json_output(AWS_APP::RSM(null, '-1', '你没有权限编辑这个问题'));
			}
		}
		
		$question_content = $_POST['question_content'];
		$question_detail = $_POST['question_detail'];
		$modify_reason = $_POST['modify_reason'];
		
		if (!$_POST['category_id'] AND get_setting('category_enable') == 'Y')
		{
			H::ajax_json_output(AWS_APP::RSM(null, - 1, '请选择分类'));
		}

		if (cjk_strlen($question_content) < 5)
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '问题标题字数不得少于 5 个，请返回修改'));
		}

		if ((get_setting('question_title_limit') > 0) && (cjk_strlen($question_content) > get_setting('question_title_limit')))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '问题标题字数不得大于 ' . get_setting('question_title_limit') . ' 个，请返回修改。'));
		}
		
		if (!$this->user_info['permission']['publish_url'] && FORMAT::outside_url_exists($question_detail))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', "你所在的用户组不允许发布站外链接"));
		}
		
		if (human_valid('question_valid_hour') AND !core_captcha::validate($_POST['seccode_verify'], false))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', "请填写正确的验证码"));
		}
		
		// !注: 来路检测后面不能再放报错提示
		if (!valid_post_hash($_POST['post_hash']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '表单来路不正确或内容已提交, 请刷新页面重试'));
		}
		
		if ($_POST['do_delete'] AND !((!$question_info['lock'] AND ($question_info['published_uid'] == $this->user_id OR $this->user_info['permission']['edit_question'])) OR $this->user_info['permission']['is_administortar'] OR $this->user_info['permission']['is_moderator']))
		{				
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '对不起, 你没有删除问题的权限'));
		}
		
		core_captcha::clear();
		
		if ($_POST['do_delete'])
		{
			if ($this->user_id != $question_info['published_uid'])
			{
				$add_time = date('Y-m-d H:i:s', $question_info['add_time']);

				$message = <<<EOF
你好，你发表的问题 <<{$question_info['question_content']}>> 已被管理员删除。
					
----- 所删除的内容 ----- 
					
{$question_info['question_content']} ({$add_time})
					
{$question_info['question_detail']}
					
-----------------------------
					
如有任何疑问，请联系管理员，谢谢合作。
					
EOF;

				$this->model('message')->send_message($this->user_id, $question_info['published_uid'], null, $message, 0, 0);
				
				$this->model('email')->action_email(email_class::TYPE_QUESTION_DEL, $question_info['published_uid'], get_js_url('/inbox/'), array(
					'question_title' => $question_info['question_content'],
					'question_detail' => $question_info['question_detail'],
				));
			}
				
			$this->model('question')->remove_question($question_info['question_id']);
			
			H::ajax_json_output(AWS_APP::RSM(array(
				'url' => get_js_url('/home/explore/')
			), 1, null));
		}
		
		$modify_verified = true;
		
		if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'] AND $question_info['published_uid'] != $this->user_id)
		{
			$modify_verified = false;
		}
		
		$this->model('question')->update_question($question_id, $question_content, $question_detail, $this->user_id, $modify_verified, $modify_reason);
		
		if ($_POST['category_id'])
		{
			$this->model('question')->update_question_category($question_id, $_POST['category_id']);
		}
		
		//通知原发布者
		if ($this->user_id != $question_info['published_uid'])
		{
			$this->model('question')->add_focus_question($question_id, $this->user_id);
			
			$this->model('notify')->send($this->user_id, $question_info['published_uid'], notify_class::TYPE_MOD_QUESTION, notify_class::CATEGORY_QUESTION, $question_id, array(
				'from_uid' => $this->user_id,
				'question_id' => $question_id
			));
			
			$this->model('email')->action_email(email_class::TYPE_QUESTION_MOD, $question_info['published_uid'], get_js_url('/question/' . $question_id), array(
				'user_name' => $this->user_info['user_name'], 
				'question_title' => $question_info['question_content']
			));
		}
		
		if (intval($_POST['category_id']) != $question_info['category_id'])
		{
			$category_info = $this->model('system')->get_category_info(intval($_POST['category_id']));
			
			ACTION_LOG::save_action($this->user_id, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTION_CATEGORY, $category_info['title'], $category_info['id']);
		}
		
		if ($_POST['attach_access_key'])
		{
			if($this->model('publish')->update_attach('question', $question_id, $_POST['attach_access_key']))
			{
				ACTION_LOG::save_action($this->user_id, $question_id, ACTION_LOG::CATEGORY_QUESTION, ACTION_LOG::MOD_QUESTION_ATTACH);
			}
		}
		
		H::ajax_json_output(AWS_APP::RSM(array(
			'url' => get_js_url('/question/id-' . $question_id . '__column-log__rf-false')
		), 1, null));
	
	}
	
	public function publish_question_action()
	{
		if (!$this->user_info['permission']['publish_question'] AND !$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '你没有权限发布问题'));
		}
		
		if ($this->user_info['integral'] < 0 AND get_setting('integral_system_enabled') == 'Y')
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', '你的剩余积分已经不足以进行此操作'));
		}
		
		$question_content = $_POST['question_content'];
		$question_detail = $_POST['question_detail'];
		
		if (empty($question_content))
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), - 1, '请输入问题标题'));
		}
		
		if (get_setting('category_enable') == 'N')
		{
			$_POST['category_id'] = 1;
		}
		
		if (!$_POST['category_id'])
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), - 1, '请选择问题分类'));
		}

		if (cjk_strlen($question_content) < 5)
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), '-1', '问题标题字数不得少于 5 个，请返回修改。'));
		}

		if (get_setting('question_title_limit') > 0 && cjk_strlen($question_content) > get_setting('question_title_limit'))
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), '-1', '问题标题字数不得大于 ' . get_setting('question_title_limit') . ' 个，请返回修改。'));
		}
		
		if (!$this->user_info['permission']['publish_url'] && FORMAT::outside_url_exists($question_detail))
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), '-1', "你所在的用户组不允许发布站外链接"));
		}
		
		if (human_valid('question_valid_hour') AND !core_captcha::validate($_POST['seccode_verify'], false))
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), '-1', "请填写正确的验证码"));
		}
		
		// !注: 来路检测后面不能再放报错提示
		if (!valid_post_hash($_POST['post_hash']))
		{
			H::ajax_json_output(AWS_APP::RSM(array(
				'tips_id' => 'quick_publish_message'
			), '-1', '表单来路不正确或内容已提交, 请刷新页面重试'));
		}
		
		core_captcha::clear();
		
		if ($this->publish_approval_valid())
		{			
			$this->model('publish')->publish_approval('question', array(
				'question_content' => $question_content,
				'question_detail' => $question_detail,
				'category_id' => $_POST['category_id'],
				'topics' => $_POST['topics'],
				'anonymous' => $_POST['anonymous'],
				'attach_access_key' => $_POST['attach_access_key'],
				'ask_user_id' => $_POST['ask_user_id']
			), $this->user_id, $_POST['attach_access_key']);
				
			H::ajax_json_output(AWS_APP::RSM(array(
				'url' => get_js_url('/publish/wait_approval/')
			), 1, null));
		}
		else
		{
			if ($question_id = $this->model('publish')->publish_question($question_content, $question_detail, $_POST['category_id'], $this->user_id, $_POST['topics'], $_POST['anonymous'], $_POST['attach_access_key'], $_POST['ask_user_id']))
			{
				if ($_POST['is_mobile'])
				{
					$url = get_js_url('/mobile/question/' . $question_id);
				}
				else
				{
					$url = get_js_url('/question/' . $question_id);
				}
				
				H::ajax_json_output(AWS_APP::RSM(array(
					'url' => $url
				), 1, null));
			}
			else
			{
				H::ajax_json_output(AWS_APP::RSM(null, '-1', '问题发布失败, 请联系管理员'));
			}	
		}
	}
}