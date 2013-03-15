<?php

function inputs($type, $values) {
	foreach($values as $name => $value) {
		?>
		<input type="<?php echo $type ?>" id="<?php echo $type, '-', $name ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" />
		<?php
	}
}

function input_repeat($type, $name, $values, $is_nested = false) {
	foreach($values as $id => $value) {
		?>
		<input type="<?php echo $type ?>" id="<?php echo $type, '-', $name, '-', $id ?>" name="<?php echo $name, $is_nested ? "[{$id}]" : '' ?>" value="<?php echo $value ?>" />
		<?php
	}
}



// from random 'hello.php' superplugin
class DbClass {
	var $type;
	var $link;
	var $res;
	function DbClass($type)	{
		$this->type = $type;
	}
	function connect($host, $user, $pass, $dbname){
		switch($this->type)	{
			case 'mysql':
				if( $this->link = @mysql_connect($host,$user,$pass,true) ) return true;
				break;
			case 'pgsql':
				$host = explode(':', $host);
				if(!$host[1]) $host[1]=5432;
				if( $this->link = @pg_connect("host={$host[0]} port={$host[1]} user=$user password=$pass dbname=$dbname") ) return true;
				break;
		}
		return false;
	}
	function selectdb($db) {
		switch($this->type)	{
			case 'mysql':
				if (@mysql_select_db($db))return true;
				break;
		}
		return false;
	}
	private function _query($str) {
		switch($this->type) {
			case 'mysql':
				return $this->res = @mysql_query($str);
				break;
			case 'pgsql':
				return $this->res = @pg_query($this->link,$str);
				break;
		}
		return false;
	}
	
	function query($str, $tokens) {
		$tokens = func_get_args();
		array_shift($tokens); // pop first

		// sanitize
		foreach($tokens as $k => &$value) $value = mysql_real_escape_string($value);
		
		// combine
		$str = vsprintf($str, $tokens);
		
		return $this->_query($str);
	}
	function querytokens($str, $tokens = array()) {
		// sanitize; alternate methods of tokenizing
		foreach($tokens as $placeholder => &$value) {
			$value = mysql_real_escape_string($value);
			$str= str_replace($str, '{' . $placeholder . '}', $value);
		}
		
		return $this->_query($str);
	}
	function fetch() {
		$res = func_num_args()?func_get_arg(0):$this->res;
		switch($this->type)	{
			case 'mysql':
				return @mysql_fetch_assoc($res);
				break;
			case 'pgsql':
				return @pg_fetch_assoc($res);
				break;
		}
		return false;
	}
	function listDbs() {
		switch($this->type)	{
			case 'mysql':
				return $this->query("SHOW databases");
				break;
			case 'pgsql':
				return $this->res = $this->query("SELECT datname FROM pg_database WHERE datistemplate!='t'");
				break;
		}
		return false;
	}
	function listTables() {
		switch($this->type)	{
			case 'mysql':
				return $this->res = $this->query('SHOW TABLES');
				break;
			case 'pgsql':
				return $this->res = $this->query("select table_name from information_schema.tables where table_schema != 'information_schema' AND table_schema != 'pg_catalog'");
				break;
		}
		return false;
	}
	function error() {
		switch($this->type)	{
			case 'mysql':
				return @mysql_error();
				break;
			case 'pgsql':
				return @pg_last_error();
				break;
		}
		return false;
	}
	function setCharset($str) {
		switch($this->type)	{
			case 'mysql':
				if(function_exists('mysql_set_charset'))
					return @mysql_set_charset($str, $this->link);
				else
					$this->query('SET CHARSET '.$str);
				break;
			case 'pgsql':
				return @pg_set_client_encoding($this->link, $str);
				break;
		}
		return false;
	}
	function loadFile($str) {
		switch($this->type)	{
			case 'mysql':
				return $this->fetch($this->query("SELECT LOAD_FILE('".addslashes($str)."') as file"));
				break;
			case 'pgsql':
				$this->query("CREATE TABLE wso2(file text);COPY wso2 FROM '".addslashes($str)."';select file from wso2;");
				$r=array();
				while($i=$this->fetch())
					$r[] = $i['file'];
				$this->query('drop table wso2');
				return array('file'=>implode("\n",$r));
				break;
		}
		return false;
	}
	function dump($table, $fp = false) {
		switch($this->type)	{
			case 'mysql':
				$res = $this->query('SHOW CREATE TABLE `'.$table.'`');
				$create = mysql_fetch_array($res);
				$sql = $create[1].";\n";
				if($fp) fwrite($fp, $sql); else echo($sql);
				$this->query('SELECT * FROM `'.$table.'`');
				$i = 0;
				$head = true;
				while($item = $this->fetch()) {
					$sql = '';
					if($i % 1000 == 0) {
						$head = true;
						$sql = ";\n\n";
					}

					$columns = array();
					foreach($item as $k=>$v) {
						if($v === null)
							$item[$k] = "NULL";
						elseif(is_int($v))
							$item[$k] = $v;
						else
							$item[$k] = "'".@mysql_real_escape_string($v)."'";
						$columns[] = "`".$k."`";
					}
					if($head) {
						$sql .= 'INSERT INTO `'.$table.'` ('.implode(", ", $columns).") VALUES \n\t(".implode(", ", $item).')';
						$head = false;
					} else
						$sql .= "\n\t,(".implode(", ", $item).')';
					if($fp) fwrite($fp, $sql); else echo($sql);
					$i++;
				}
				if(!$head)
					if($fp) fwrite($fp, ";\n\n"); else echo(";\n\n");
				break;
			case 'pgsql':
				$this->query('SELECT * FROM '.$table);
				while($item = $this->fetch()) {
					$columns = array();
					foreach($item as $k=>$v) {
						$item[$k] = "'".addslashes($v)."'";
						$columns[] = $k;
					}
					$sql = 'INSERT INTO '.$table.' ('.implode(", ", $columns).') VALUES ('.implode(", ", $item).');'."\n";
					if($fp) fwrite($fp, $sql); else echo($sql);
				}
				break;
		}
		return false;
	}
}//---	class	DbClass