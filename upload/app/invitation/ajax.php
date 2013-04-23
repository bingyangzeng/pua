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
	var $per_page = 10;
	
	function setup()
	{
		HTTP::no_cache_header();
	}
	
	/**
	 * 列出受邀请的列表
	 */
	function invitation_list_action()
	{		
		$page = intval($_GET['page']);
		
		$limit = $page * $this->per_page . ', ' . $this->per_page;
		
		if ($invitation_list = $this->model('invitation')->get_invitation_list($this->user_id, $limit))
		{
			$user_ids = array();

			foreach ($invitation_list as $key => $val)
			{
				if ($val['active_status'] == '1')
				{
					$user_ids[] = $val['active_uid'];
				}
			}

			if($user_ids = array_unique($user_ids))
			{
				if($user_infos = $this->model('account')->get_user_info_by_uids($user_ids))
				{
					foreach ($invitation_list as $key => $val)
					{
						if ($val['active_status'] == '1')
						{
							$invitation_list[$key]['user_info'] = $user_infos[$val['active_uid']];
						}
					}
				}
			}
		}
		
		TPL::assign('invitation_list', $invitation_list);
		
		TPL::output("invitation/ajax/invitation_list");
	}
	
	//执行邀请动作
	function invite_action()
	{
		$email = trim($_POST['email']);
		
		if (! H::valid_email($email))
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, "请填写正确的邮箱"));
		}
		
		//判断当前用户是否还有邀请额
		if (! $this->user_info['invitation_available'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, "已经没有可使用的邀请名额"));
		}
		
		//搜索邮箱是否已为本站用户，存在则提示用户已存在，返回显示。
		if ($uid = $this->model('account')->check_email($email))
		{
			if ($uid == $this->user_id)
			{
				H::ajax_json_output(AWS_APP::RSM(null, -1, "你不能邀请您自己"));
			}
			
			H::ajax_json_output(AWS_APP::RSM(null, -1, "此邮箱已在本站注册帐号"));
		}
		
		//若再次填入已邀请过的邮箱，则再发送一次邀请邮件
		if ($invitation_info = $this->model('invitation')->get_invitation_by_email($email))
		{
			if ($invitation_info['uid'] == $this->user_id)
			{				
				if ($this->model('invitation')->send_invitation_email($invitation_info['invitation_id']))
				{
					H::ajax_json_output(AWS_APP::RSM(null, 1, "重发邀请成功"));
				}
				else
				{
					H::ajax_json_output(AWS_APP::RSM(null, -1, "邀请发送失败, 请联系管理员"));
				}
			}
			else
			{
				H::ajax_json_output(AWS_APP::RSM(null, -1, "此邮箱已接收过本站发出的邀请"));
			}
		}
		
		//生成邀请码
		$invitation_code = $this->model('invitation')->get_unique_invitation_code();
		
		if ($invitation_id = $this->model('invitation')->add_invitation($this->user_id, $invitation_code, $email, time(), ip2long($_SERVER['REMOTE_ADDR'])))
		{
			$this->model('account')->edit_invitation_available($this->user_id, -1);
			
			if($this->model('invitation')->send_invitation_email($invitation_id))
			{
				H::ajax_json_output(AWS_APP::RSM(null, 1, "邀请发送成功"));
			}
			else
			{
				H::ajax_json_output(AWS_APP::RSM(null, -1, "邀请发送失败"));
			}
		}
	}

	/**
	 * 重发邀请
	 */
	function invite_resend_action()
	{
		if ($this->model('invitation')->send_invitation_email($_GET['invitation_id']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, 1, null));
		}
		else
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, "邀请发送失败, 请联系管理员"));
		}
	}

	function invite_cancel_action()
	{
		if (! intval($_GET['invitation_id']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, "邀请 ID 不存在"));
		}
		
		if (! $this->model('invitation')->get_invitation_by_id(intval($_GET['invitation_id'])))
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, "邀请信息不存在"));
		}
		
		//更新状态
		$this->model('invitation')->update_invitation_fields(array(
			"active_status" => '-1'
		), intval($_GET['invitation_id']));
		
		if ($this->model('account')->edit_invitation_available($this->user_id, 1))
		{
			H::ajax_json_output(AWS_APP::RSM(null, 1, null));
		}
		else
		{
			H::ajax_json_output(AWS_APP::RSM(null, 1, "操作失败, 请联系管理员"));
		}
	}
}