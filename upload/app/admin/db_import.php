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

class db_import
{
	var $max_length = 16384;
	var $max_line = 10000;
	var $line_p_time = 1000;		//每次执行读取行数

	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'black'; //黑名单,黑名单中的检查  'white'白名单,白名单以外的检查
		
		return $rule_action;
	}

	public function __construct()
	{
		if (! $_SESSION['admin_login'])
		{
			HTTP::redirect(get_setting() . '/?/admin/login/');
		}
		
		admin_session_class::init();
	}

	public function index_action()
	{
		$filename = rawurldecode($_GET['filename']);
		
		if (! $filename)
		{
			HTTP::redirect('/admin/tool/database/');
		}
		
		TPL::assign('page_title', '还原数据库');
		
		TPL::assign('filename', $filename);
		
		TPL::assign('user_id', USER::get_client_uid());
		
		TPL::assign('menu_list', AWS_APP::model('admin_group')->get_menu_list(1, 302));
		
		TPL::output('admin/tool/database_import');
	}

	public function import_process_action()
	{
		HTTP::no_cache_header();
		
		@set_time_limit(0);
		
		$filename = $_GET['filename'];
		$page = $_GET['page'];
		$totalqueries = $_GET['totalqueries'];
		$_GET['start'] = floor($_GET['start']);
		$_GET['foffset'] = floor($_GET['foffset']);
		$_GET['mb_sum'] = floor($_GET['mb_sum']);
		
		$linenumber = $_GET['start'];
		
		$comment[] = '#';
		$comment[] = '-- ';
		$comment[] = 'DELIMITER';
		$comment[] = '/*!';
		
		@set_time_limit(0);
		
		$filename = $_GET['filename'];
		
		$file_list = $this->get_file_list(dirname($filename));
		
		$filename_s = pathinfo($filename, PATHINFO_FILENAME);
		
		if (! $file_list[$filename_s])
		{
			H::redirect_msg('备份文件不存在。', '?/admin/tool/database/');
		}
		
		$page = (! $page) ? 1 : $page;
		
		if ($page <= $file_list[$filename_s]['page'])
		{
			$db_config = AWS_APP::config()->get('database')->master;
			
			$dbconnection = @mysql_connect($db_config['host'], $db_config['username'], $db_config['password']);
			
			if ($dbconnection)
			{
				$db = mysql_select_db($db_config['dbname']);
			}
			
			@mysql_query("SET NAMES utf8", $dbconnection);
			
			$filepath = realpath(AWS_PATH) . '/' . $filename . '-' . $page . '.sql';
			
			if (! $file = @fopen($filepath, "rb"))
			{
				die('无法读取文件');
			}
			else if (@fseek($file, 0, SEEK_END) == 0)
			{
				$filesize = ftell($file);
			}
			
			if(fseek($file, $_GET['foffset']) != 0)
			{
				die ("无法定位：" . $_GET['foffset'] . "\n");
			}
			
			$query = '';
			$delimiter = ';';
			$querylines = 0;
			$queries = 0;
			$inparents = false;
			$string_quotes = '\'';
			
			while ($linenumber < $_GET['start'] + $this->line_p_time || $query != "")
			{
				$dumpline = "";
				
				while (! feof($file) && substr($dumpline, - 1) != "\n" && substr($dumpline, - 1) != "\r")
				{
					$dumpline .= fgets($file, $this->max_length);
				}
				
				if ($dumpline === "")
				{
					break;
				}
				
				if ($_GET['foffset'] == 0)
				{
					$dumpline = preg_replace('|^\xEF\xBB\xBF|', '', $dumpline);
				}
				
				$dumpline = str_replace("\r\n", "\n", $dumpline);
				$dumpline = str_replace("\r", "\n", $dumpline);
				
				if (! $inparents && strpos($dumpline, "DELIMITER ") === 0)
				{
					$delimiter = str_replace("DELIMITER ", "", trim($dumpline));
				}
				
				if (! $inparents)
				{
					$skipline = false;
					
					reset($comment);
					
					foreach ($comment as $comment_value)
					{
						if (trim($dumpline) == "" || strpos(trim($dumpline), $comment_value) === 0)
						{
							$skipline = true;
							break;
						}
					}
					
					if ($skipline)
					{
						$linenumber ++;
						continue;
					}
				}
				
				$dumpline_deslashed = str_replace("\\\\", "", $dumpline);
				
				$parents = substr_count($dumpline_deslashed, $string_quotes) - substr_count($dumpline_deslashed, "\\$string_quotes");
				
				if ($parents % 2 != 0)
				{
					$inparents = ! $inparents;
				}
				
				$query .= $dumpline;
				
				if (! $inparents)
				{
					$querylines ++;
				}
				
				if (preg_match('/' . preg_quote($delimiter) . '$/', trim($dumpline)) && ! $inparents)
				{
					$query = substr(trim($query), 0, - 1 * strlen($delimiter));
					
					if (! mysql_query($query, $dbconnection))
					{
						echo ("<p class=\"error\">Error at the line $linenumber: " . trim($dumpline) . "</p>\n");
						echo ("<p>Query: " . trim(nl2br(htmlentities($query))) . "</p>\n");
						echo ("<p>MySQL: " . mysql_error() . "</p>\n");
						die;
					}
					
					$totalqueries ++;
					
					$queries ++;
					$query = "";
					$querylines = 0;
				}
				
				$linenumber ++;
			}
			
			$foffset = ftell($file);
			
			fclose($file);
			
			if($foffset - $_GET['foffset'] > 0)
			{
				$mb_sum = $_GET['mb_sum'] + round(($foffset - $_GET['foffset']) / 1048576, 2);
			}
			else 
			{
				$mb_sum = $_GET['mb_sum'];
			}
			
			if ($linenumber < $_GET['start'] + $this->line_p_time)
			{
				$page++;
				$linenumber = 0;
				$foffset = 0;
			}
			
			$q_data =array(
				'filename' => $filename,
				'page' => $page,
				'start' => $linenumber,
				'foffset' => $foffset,
				'totalqueries' => $totalqueries,
				'mb_sum' => $mb_sum,
				'percent' => ceil((($page - 1 + $foffset / $filesize) / $file_list[$filename_s]['page']) * 100),
			);
			
			foreach($q_data as $key => $val)
			{
				$q_arr[] = $key . '-' . $val; 
			}
			
			H::ajax_json_output(AWS_APP::RSM($q_data, 1, "导入成功"));
		}
		else if($page > $file_list[$filename_s]['page'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, 2, "导入成功"));
		}
	}
	
	public function get_file_list($backup_dir)
	{
		$b_dir = AWS_PATH . $backup_dir;
		
		if (is_dir($b_dir))
		{
			$file_list = array();
				
			$read_dir = dir($b_dir);
			
			while ($file = $read_dir->read())
			{
				$file = $b_dir . '/' . $file;
				if (preg_match('/\.sql$/i', $file))
				{
					$handle = fopen($file, 'r');
						
					$timestamp = preg_replace('/^# TIMESTAMP\s:\s([0-9]+)\s*/s', '$1', fgets($handle, 256));
						
					fclose($handle);
						
					$fsize = filesize($file);
						
					$s_name = preg_replace('/(.+)\-[0-9]+/', '$1', pathinfo($file, PATHINFO_FILENAME));
						
					$page = preg_replace('/.*\-([0-9]+)/', '$1', pathinfo($file, PATHINFO_FILENAME));
						
					$file_list[$s_name]['time'] = $timestamp;
						
					$file_list[$s_name]['page'] ++;
						
					$file_list[$s_name]['size'] += $fsize;
				}
			}
				
			$read_dir->close();
				
			return $file_list;
		}
		else
		{
			return false;
		}
	}
}