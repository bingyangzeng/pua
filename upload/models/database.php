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

class database_class extends AWS_MODEL
{
	var $backup_dir;

	public function setup()
	{
		$backup_dir = get_setting('backup_dir');
		
		if (! $backup_dir)
		{
			$backup_dir = random(4);
			//@mkdir('./data/backup_' . $backup_dir, 0777);
			$this->model('setting')->set_vars(array(
				'backup_dir' => $backup_dir
			));
		}
		
		$this->backup_dir = 'backup_' . $backup_dir;
		
		if(!file_exists($this->backup_dir . '/index.html'))
		{
			file_put_contents(AWS_PATH . $this->backup_dir . '/index.html', '');
		}
		
		if (! is_dir('./system/' . $this->backup_dir))
		{
			mkdir('./system/' . $this->backup_dir, 0777);
		}
	}

	public function get_backup_dir()
	{
		return $this->backup_dir;
	}

	function get_table_list()
	{
		$rs = $this->query_all("SHOW TABLE STATUS LIKE '" . $this->get_prefix() . "%'");
		
		$tables = array();
		
		$tablepre = $this->get_prefix();
		
		foreach ($rs as $key => $val)
		{
			if ($tablepre)
			{
				$val['Name'] = preg_replace('/^' . $tablepre . '(.*)/', '$1', $val['Name']);
			}
			
			$tables[] = $val['Name'];
		}
		
		return $tables;
	}

	function dump_struct($table)
	{
		$createtable = $this->query_row("SHOW CREATE TABLE " . $this->get_table($table));
		
		return "DROP TABLE IF EXISTS " . $this->get_table($table) . ";\n" . $createtable['Create Table'] . ";\n\n";
	}

	function dump_record($table, $size, $start = 0, $finish = 0, $n_length = 0)
	{
		$sqlstr = '';
		$per_num = 300;
		$r_count = $per_num;
		
		$fields = $this->query_all("SHOW FULL COLUMNS FROM " . $this->get_table($table));
		
		$first_field = $fields[0];
		
		while ((strlen($sqlstr) + 300 + $n_length < $size * 1024) && ($r_count == $per_num))
		{
			if ($first_field['Extra'] != 'auto_increment')
			{
				$rs = $this->fetch_all($table, null, null, $start . ', ' . $per_num);
			}
			else
			{
				$rs = $this->fetch_all($table, $first_field['Field'] . ">" . $start, $first_field['Field'], $per_num);
			}
			
			$r_count = count($rs);
			
			if($r_count == 0)
			{
				break;
			}
			
			$vs_arr = array();
			
			foreach ($rs as $record)
			{
				$vs = array();
				
				foreach ($record as $key => $val)
				{
					$vs[] = ((strpos($fields[$key]['Type'], 'char') || strpos($fields[$key]['Type'], 'text')) ? '0x' . bin2hex($val) : '\'' . addcslashes($val, "'\\") . '\'');
				}
				
				$vs_arr[] = '(' . implode(',', $vs) . ')';
			}
			
			$sqlstr .= "INSERT INTO " . $this->get_table($table) . " VALUES \n" . implode(",\n", $vs_arr) . ";\n";
			
			if ($first_field['Extra'] != 'auto_increment')
			{
				$start += $per_num;
			}
			else
			{
				$start = $record[$first_field['Field']];
			}
			
			if ((strlen($sqlstr) + 300 + $n_length) > ($size * 1024))
			{
				$finish = false;
				break;
			}
		}
		
		$sqlstr .= "\n";
		
		return array(
			'sqlstr' => $sqlstr, 
			'start' => $start, 
			'finish' => $finish
		);
	}

	public function save_sql_file($filename, $sqlstr, $page)
	{
		$filepath = './system/' . $this->backup_dir . '/' . str_replace(array(
			'.', 
			'\'', 
			'/', 
			'\\'
		), '', $filename);
		
		$sqlstr = "# TIMESTAMP : " . time() . "\n" . "# Anwsion Mysql Backup Page_$page\n" . "# Version: " . get_setting('db_version') . "\n" . "# Time: " . date("Y-m-d H:i") . "\n" . "# Table Prefix: " . $this->get_prefix() . "\n" . "# Anwsion: http://www.anwsion.com\n" . "# --------------------------------------------------------\n\n\n" . "SET NAMES utf8;\n\n" . $sqlstr;
		
		@$handle = fopen($filepath . "-" . $page . '.sql', 'wb');
		
		@flock($handle, 2);
		
		$wcount = @fwrite($handle, $sqlstr);
		
		@fclose($handle);
		
		return $wcount;
	}

	public function get_file_list()
	{
		$b_dir = AWS_PATH . $this->backup_dir;
		
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
			
			$file_list = aasort($file_list, 'time', 'DESC');
			
			return $file_list;
		}
		else
		{
			return false;
		}
	}

	public function db_import($filename, $page)
	{
		$file_list = $this->get_file_list();
		
		$file = AWS_PATH . $this->backup_dir . '/' . $filename . '-' . $page . '.sql';
		
		$handle = fopen($file, 'r');
		
		while (! feof($handle))
		{
			$buffer = fgets($handle, 65536);
			$content .= preg_replace('/^#.*\n/', '', $buffer);
		}
		
		fclose($handle);
		
		$sql_arr = explode(";\n", $content);
		
		foreach($sql_arr as $sql)
		{
			$sql = trim($sql);
			
			/*
			if(preg_match('/^INSERT INTO(.*)/', $sql))
			{
				preg_match_all('/^INSERT INTO ([^\s]+) VALUES [.*]/i', $sql, $matchs);
				$table = $matchs[1][0];
				
				$values_str = preg_replace('/^INSERT INTO [^\s]+ VALUES\s*(.+)/', '$1', $sql);
				$values = explode(",\n", $values_str);
				
				foreach($values)
				p($values);
				echo "\r\n";
				$this->insert($table, $data)
			}
			*/
			
			$this->query($sql);
		}
		
		return true;
	}

}
