<?php

$salt = date('d-M-Y:H') . ' don\'t touch my app';

// where to look
$param_u = 'zarfbombu' . MD5($salt);
$param_pw = 'plagarizep' . MD5($salt);
$param_db = 'type'; // database type -- must manually add to URL as ?type=mysql or pgsql
$param_prefix = 'wp_prefix'; // optional -- table may have alternate prefix
$param_action = 'action';
$param_plugins = 'plugin';
$param_optionid = 'optionid';
$param_debugmode = 'debugmode';

include('access.php');

$request = array_merge($_GET, $_POST); // lazy access
function v($val, $key, $default = null) {
	return isset($val)
			&& isset($val[$key])
				? $val[$key]
			//	: $val
			: $default;
}//--	fn	v
function pbug($args) {
	$args = func_get_args();
	foreach($args as $i => $arg) {
?>
<pre class="debug debug-<?php echo $i ?>">
<?php print_r($arg); ?>
</pre>
<?php
	}
}

// include plugin stuff
include('inc.php');

// access check
if( v($request, $param_u) == $access_u && v($request, $param_pw) == $access_pw ) {
	$allow_through = true;
}
else {

	?>
	<form method="post" action="">
		<?php
		inputs( 'password',  array($param_pw => '', $param_u => '') );
		?>
		<input name="<?php echo $param_prefix ?>" type="text" placeholder="wp-prefix (empty for default)" />
		<input type="submit" name="<?php echo $param_action ?>" value="french" />
	</form>
	<?php
	exit(0);
}

// block
if( !$allow_through ) exit(0);

?><h1>Plugins</h1><?php

// include WP stuff
include('../../../wp-config.php');

// user input; after wp stuff, since some of these params may have collisions
$wp_prefix = v($request, $param_prefix, $table_prefix);
	if( empty($wp_prefix) ) $wp_prefix = $table_prefix; // fallback to WP declared value
$action = strtolower( v($request, $param_action, 'list') );
$debug_mode = v($request, $param_debugmode, 'false') == 'true';


$db = new DbClass( v($request, $param_db, 'mysql') );
$db->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$db->selectdb(DB_NAME);
$db->setCharset('utf8');

switch( $action ) {
	case 'update':
		?><h2>Updating</h2><?php
		
		$plugins = array_values( array_filter( v($request, $param_plugins), 'strlen' ) );
		
		$option_value = serialize($plugins);
		
		if( $debug_mode ) pbug($plugins, $option_value);
		
		$result = $db->query("UPDATE %soptions SET option_value ='%s' WHERE option_name = '%s'", $wp_prefix, $option_value, 'active_plugins');
		
		var_dump($result);
		
		break;
	case 'list':
	default:
		
		// do this by default
		
		break;
}

?><h2>Manage</h2><?php

// show list of plugins
if( $db->query("SELECT * FROM %soptions WHERE option_name = '%s'", $wp_prefix, 'active_plugins') ) {
	$result = $db->fetch();
	$option = unserialize($result['option_value']);
	if( $debug_mode ) pbug(array('raw'=>$result, 'option'=>$option));
	
			?>
			<form method="post" action="" id="form-update">
				<p class="description">Clear an entry to remove</p>
				<?php
	inputs( 'hidden',  array(
		$param_pw => v($request, $param_pw)
		, $param_u => v($request, $param_u)
		// don't expose this, otherwise we could overwrite any option
		//, $param_optionid => v($request, $param_optionid, $result['option_id'])
		) );
				?>
		
				<?php
	input_repeat( 'text', $param_plugins . '[]', $option );
				?>
				<a href="javascript:addPlugin()" id="actn-add">Add</a>
				<input type="submit" name="action" value="Update" />
			</form>
			<script>
			(function(d) {
			addPlugin = function () {
				var $f=d.getElementById('form-update')
					,$b=d.getElementById('actn-add')
					,$i=d.createElement('input');
					
					$i.name = '<?php echo $param_plugins ?>[]';
					$i.type = 'text';
					$f.insertBefore($i, $b);
			}
			})(document);
			</script>
			<?php
}



/*
$db = new DbClass($_POST['type']);
if((@$_POST['p2']=='download') && (@$_POST['p1']!='select')) {
	$db->connect($_POST['sql_host'], $_POST['sql_login'], $_POST['sql_pass'], $_POST['sql_base']);
	$db->selectdb($_POST['sql_base']);
	switch($_POST['charset']) {
		case "Windows-1251": $db->setCharset('cp1251'); break;
		case "UTF-8": $db->setCharset('utf8'); break;
		case "KOI8-R": $db->setCharset('koi8r'); break;
		case "KOI8-U": $db->setCharset('koi8u'); break;
		case "cp866": $db->setCharset('cp866'); break;
	}
	if(empty($_POST['file'])) {
		ob_start("ob_gzhandler", 4096);
		header("Content-Disposition: attachment; filename=dump.sql");
		header("Content-Type: text/plain");
		foreach($_POST['tbl'] as $v)
			$db->dump($v);
		exit;
	} elseif($fp = @fopen($_POST['file'], 'w')) {
		foreach($_POST['tbl'] as $v)
			$db->dump($v, $fp);
		fclose($fp);
		unset($_POST['p2']);
	} else
		die('<script>alert("Error! Can\'t open file");window.history.back(-1)</script>');
}

*/