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


if (!defined('IN_ANWSION'))
{
	die;
}

class tool extends AWS_CONTROLLER
{
	function get_permission_action()
	{
	
	}

	public function setup()
	{
		$this->model('admin_session')->init($this->get_permission_action());
	}

	/**
	 * 清除系统缓存
	 */
	public function cache_clean_action()
	{
		$this->crumb("更新缓存", "admin/tool/cache_clean/");
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 501));
		TPL::output('admin/tool/cache_clean');
	}

	public function cache_clean_process_action()
	{
		$this->model('cache')->clean();
		AWS_APP::cache()->clean();
		
		TPL::assign('message', '成功清除网站全部缓存');
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 501));
		TPL::output('admin/tool/cache_clean');
	}

	public function database_action()
	{
		$this->crumb("数据库维护", "admin/tool/database/");
		
		TPL::assign('backup_dir', $this->model('database')->get_backup_dir());
		TPL::assign('list', $this->model('database')->get_file_list());
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 503));
		TPL::output('admin/tool/database');
	}

	public function database_backup_process_action()
	{
		if ($this->is_post())
		{
			foreach ($_POST as $key => $val)
			{
				$gets[] = $key . '-' . $val;
			}
			
			HTTP::redirect('?/admin/tool/database_backup_process/' . implode('__', $gets));
			exit();
		}
		
		$sqlstr = '';
		$size = intval($_GET['size']);
		$page = intval($_GET['page']);
		$tid = intval($_GET['tid']);
		$start = intval($_GET['start']);
		
		$tables = $this->model('database')->get_table_list();
		
		if (! $tid && $page == 0)
		{
			foreach ($tables as $table)
			{
				$sqlstr .= $this->model('database')->dump_struct($table);
			}
		}
		
		for ($finish = true; ($tid < count($tables)) && (strlen($sqlstr) + 300 < $size * 1024) && $finish; $tid ++)
		{
			$dump_query = $this->model('database')->dump_record($tables[$tid], $size, $start, $finish, strlen($sqlstr));
			
			$sqlstr .= $dump_query['sqlstr'];
			$start = $dump_query['start'];
			$finish = $dump_query['finish'];
			
			if ($finish)
			{
				$start = 0;
			}
		}
		
		! $finish && $tid --;
		
		if (trim($sqlstr))
		{
			$page ++;
			
			$retval = $this->model('database')->save_sql_file($_GET['filename'], $sqlstr, $page);
			
			if ($retval)
			{
				H::redirect_msg('正在备份，成功导出分卷 ' . $page . '...', '?/admin/tool/database_backup_process/size-' . rawurlencode($size) . '__page-' . rawurlencode($page) . '__tid-' . rawurlencode($tid) . '__start-' . rawurlencode($start) . '__filename-' . rawurlencode($_GET['filename']));
			}
			else
			{
				die('无法保存文件，请检查 system 目录是否有写入权限');
			}
		}
		else
		{
			//提示备份完成，显示文件列表
			$this->crumb("数据库备份", "admin/tool/database/");
			
			for ($i = 1; $i <= $page; $i ++)
			{
				$backup_list[] = '/system/backup_' . get_setting('backup_dir') . '/' . $_GET['filename'] . '-' . $i . '.sql';
			}
			
			TPL::assign('list', $backup_list);
			TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 503));
			TPL::output('admin/tool/database_backup_success');
		}
	}

	public function database_revert_process_action()
	{
		$filename = $_GET['filename'];
		
		$page = intval($_GET['page']);
		
		@set_time_limit(0);
		
		if (! $filename)
		{
			HTTP::redirect('?/admin/tool/database/');
		}
		
		$file_list = $this->model('db_manage')->get_file_list();
		
		$page = ! $page ? 1 : $page;
		
		if ($page <= $file_list[$filename]['page'])
		{
			$retval = $this->model('db_manage')->revert($filename, $page);
			
			H::redirect_msg('正在还原，成功导入分卷 ' . $page . '...', '?/admin/tool/database_revert_process/filename-' . rawurlencode($_GET['filename']) . '__page-' . rawurlencode(++ $page));
		}
		else
		{
			H::redirect_msg('成功导入 ' . count($file_list) . ' 个分卷，全部备份文件还原成功。');
		}
	}

	public function reputation_rebuild_action()
	{
		$this->crumb("更新威望数据", "admin/tool/reputation_rebuild/");
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 504));
		TPL::output('admin/tool/reputation_rebuild');
	}

	public function rebuild_reputation_process_action()
	{
		$this->crumb("更新威望数据", "admin/tool/reputation_rebuild/");
		
		if ($this->is_post())
		{
			$per_page = intval($_POST['per_page']);
			
			HTTP::redirect('?/admin/tool/rebuild_reputation_process/per_page-' . $per_page);
		}
		
		if (!$_GET['page'])
		{
			$_GET['page'] = 1;
		}
		
		$per_page = intval($_GET['per_page']);
		$page = intval($_GET['page']);
		$interval = intval($_GET['interval']);
		
		$user_count = $this->model('account')->get_user_count();
		
		if ($page * $per_page < $user_count) //未处理完，继续处理
		{
			$this->model('reputation')->calculate((($page * $per_page) - $per_page), $per_page);
			
			$current = ($page * $per_page) > $user_count ? $user_count : ($page * $per_page);
			
			$interval = ($interval == 0) ? 3 : $interval;
			
			$url = '?/admin/tool/rebuild_reputation_process/page-' . ($page + 1) . '__per_page-' . $per_page . '__interval-' . $interval;
			
			H::redirect_msg('总共 ' . $user_count . ' 个用户，当前更新到第 ' . $current . ' 个用户', $url, $interval);
		}
		else //提示处理完成
		{
			TPL::assign('msg', '威望数据更新完成，总共更新 ' . $user_count . ' 个用户的威望数据。');
			TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 504));
			TPL::output('admin/tool/reputation_rebuild');
		}
	}

	public function bbcode_to_markdown_action()
	{
		if ($this->is_post())
		{
			H::redirect_msg('正在处理问题内容第 1 页，请稍候...', '?/admin/tool/bbcode_to_markdown/model-question', 2);
		}
		
		if ($model = $_GET['model'])
		{
			$per_page = 100;
			
			$page = $_GET['page'];
			
			if (!$_GET['interval'])
			{
				$_GET['interval'] = 3;
			}
			
			$done = false;
				
			switch ($model)
			{
				case 'question' :
					
					$model_name = '问题';
					
					if ($question_list = $this->model('question')->search_questions_list(false, array('page' => $page, 'per_page' => $per_page)))
					{
						foreach ($question_list as $key => $val)
						{
							$this->model('question')->update_question_field($val['question_id'], array(
								'question_detail' => FORMAT::bbcode_2_markdown($val['question_detail'])
							));
						}
						
						$page++;
					}
					else
					{
						$page = 1;
						$model = 'answer';
						$model_name = '回复';
					}
					
					break;
				case 'answer' :
					
					$model_name = '回复';

					if ($answer_list = $this->model('answer')->get_answer_list(null, calc_page_limit($page, $per_page), 'answer_id ASC'))
					{
						foreach ($answer_list as $key => $val)
						{
							$this->model('answer')->update_answer_by_id($val['answer_id'], array(
								'answer_content' => FORMAT::bbcode_2_markdown($val['answer_content'])
							));
						}
					
						$page++;
					}
					else
					{
						$page = 1;
						$model = 'topic';
						$model_name = '话题';
					}
					
					break;
					
				case 'topic' :
					
					$model_name = '话题';

					if ($topic_list = $this->model('topic')->get_topic_list(null, calc_page_limit($page, $per_page), false, 'topic_id ASC'))
					{
						foreach ($topic_list as $key => $val)
						{
							$this->model('topic')->update('topic', array(
								'topic_description' => FORMAT::bbcode_2_markdown($val['topic_description'])
							), 'topic_id = ' . intval($val['topic_id']));
						}
					
						$page++;
					}
					else
					{
						$done = true;
					}
					
					break;
			}
			
			if ($done)
			{
				TPL::assign('message', 'BBcode 转换完成');
			}
			else
			{
				$url = '?/admin/tool/bbcode_to_markdown/model-' . $model . '__page-' . $page . '__per_page-' . $per_page . '__interval-' . $_GET['interval'];
				
				H::redirect_msg('正在处理' . $model_name . '内容第 ' . $page . ' 页，请稍候...', $url, $_GET['interval']);
			}
		}
		
		$this->crumb('转换 BBcode');
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 505));
		TPL::output('admin/tool/bbcode_to_markdown');
	}

	public function search_index_action()
	{
		$this->crumb("更新搜索索引", "admin/tool/search_index/");
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 502));
		TPL::output('admin/tool/search_index');
	}

	public function update_search_index_action()
	{
		if ($this->is_post())
		{
			$per_page = intval($_POST['per_page']);
			
			H::redirect_msg('正在准备更新索引，请稍候...', '?/admin/tool/update_search_index/model-question__per_page-' . $per_page);
		}
		
		if ($_GET['model'])
		{
			$per_page = intval($_GET['per_page']);
			
			$_GET['page'] = (!$_GET['page']) ? 1 : intval($_GET['page']);
			
			$_GET['interval'] = (!$_GET['interval']) ? 3 : intval($_GET['interval']);
			
			$done = false;
				
			switch ($_GET['model'])
			{
				case 'question' :
					
					$model_name = '问题';
					
					//if ($question_list = $this->model('question')->search_questions_list(false, array('page' => $_GET['page'], 'per_page' => $per_page, 'sort_key' => 'question_id', 'order' => 'ASC')))
					if ($question_list = $this->model('question')->query_all("SELECT question_id, question_content FROM " . get_table('question') . " ORDER BY question_id ASC LIMIT " . calc_page_limit($_GET['page'], $per_page)))
					{
						foreach ($question_list as $key => $val)
						{
							$this->model('search_index')->push_index('question', $val['question_content'], $val['question_id']);
						}
					}
					else
					{
						$done = true;
					}
					
				break;
			}
			
			if ($done)
			{
				TPL::assign('message', '搜索更新索引完成');
			}
			else
			{
				H::redirect_msg('正在处理' . $model_name . '索引第 ' . $_GET['page'] . ' 页，请稍候...', '?/admin/tool/update_search_index/model-' . $_GET['model'] . '__page-' . ++$_GET['page'] . '__per_page-' . $per_page . '__interval-' . $_GET['interval'], $_GET['interval']);
			}
		}
		
		$this->crumb('更新搜索索引');
		
		TPL::assign('menu_list', $this->model('admin_group')->get_menu_list($this->user_info['group_id'], 505));
		
		TPL::output('admin/tool/search_index');
	}
}
