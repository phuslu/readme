<?php

// error_reporting(0);

function __($message) {
	$messages = array(
		'Download' => '文件下载',
		'Gateway' => '网关管理',
		'Log Analysis' => '日志分析',
		'Monitor' => '性能监控',
		'Server Information' => '服务器参数',
		'IP Address' => 'IP 地址',
		'Client Infomation' => '客户端信息',
		'Uname' => '内核标识',
		'OS' => '操作系统',
		'Server Software' => '服务器软件',
		'Language' => '语言',
		'Port' => '端口',
		'User' => '用户',
		'Hostname' => '主机名称',
		'Server Port' => '端口',
		'Prober Path' => '探针路径',
		'Server Realtime Data' => '服务器实时数据',
		'Time' => '当前时间',
		'Uptime' => '已运行时间',
		'CPU Model' => 'CPU 型号',
		'L2 Cache' => '二级缓存',
		'Frequency' => '频率',
		'CPU Instruction Set' => 'CPU 指令集',
		'BIOS Version' => 'BIOS 版本',
		'Board Vendor' => '主板厂商',
		'HardDisk Model' => '硬盘型号',
		'CPU Usage' => 'CPU 使用状况',
		'CPU Temperature' => 'CPU 温度',
		'GPU Temperature' => 'GPU 温度',
		'Memory Usage' => '内存使用状况',
		'Physical Memory' => '物理内存',
		'Used' => '已用',
		'Cached' => '已缓存',
		'Free' => '空闲',
		'Percent' => '使用率',
		'Total Space' => '总空间',
		'Disk Usage' => '硬盘使用状况',
		'Loadavg' => '系统平均负载',
		'Sockets' => '网络连接数',
		'Network Usage' => '网络使用状况',
		'Tx' => '出网',
		'Rx' => '入网',
		'Realtime' => '实时',
		'Network Neighborhood' => '网络邻居',
		'Type' => '类型',
		'Device' => '设备',
		'PHP Information' => 'PHP 信息',
		'Version' => '版本',
		'Zend OpCache' => 'Zend OpCache',
		'Server API' => '服务器接口',
		'Memory Limit' => '内存限制',
		'POST Max Size' => '最大上传大小',
		'Upload Max FileSize' => '最大上传文件',
		'Max Execution Time' => '最大执行时间',
		'Default Socket Timeout' => '默认 Socket 超时',
		'PHP Extension' => 'PHP 扩展',
		'Prober' => '探针',
		'Turbo Version' => '极速版',
		'Back to top' => '返回顶部',
	);

	if (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh') {
		print isset($messages[$message]) ? $messages[$message] : $message;
	} else {
		print $message;
	}
}

function human_filesize($bytes) {
	if ($bytes == 0)
		return '0 B';

	$units = array('B','K','M','G','T');
	$size = '';

	while ($bytes > 0 && count($units) > 0)
	{
		$size = strval($bytes % 1024) . ' ' .array_shift($units) . ' ' . $size;
		$bytes = intval($bytes / 1024);
	}

	return $size;
}

function get_remote_addr()
{
	if (isset($_SERVER["HTTP_X_REAL_IP"]))
	{
		return $_SERVER["HTTP_X_REAL_IP"];
	}
	else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
	{
		return preg_replace('/^.+,\s*/', '', $_SERVER["HTTP_X_FORWARDED_FOR"]);
	}
	else
	{
		return $_SERVER["REMOTE_ADDR"];
	}
}

function get_server_addr()
{
	if ($_SERVER["SERVER_ADDR"] != "127.0.0.1")
	{
		return $_SERVER["SERVER_ADDR"];
	}
	else
	{
		return gethostbyname(php_uname('n'));
	}
}

function get_stat()
{
	$content = file('/proc/stat');
	$array = array_shift($content);
	$array = preg_split('/\s+/', trim($array));
	return array_slice($array, 1);
}

function get_sockstat()
{
	$info = array();

	$content = file('/proc/net/sockstat');
	foreach ($content as $line) {
		$parts = explode(':', $line);
		$key = trim($parts[0]);
		$values = preg_split('/\s+/', trim($parts[1]));
		$info[$key] = array();
		for ($i = 0; $i < count($info); $i += 2) {
			$info[$key][$values[$i]] = $values[$i+1];
		}
	}

	return $info;
}

function get_cpuinfo()
{
	$info = array();

	if (!($str = @file("/proc/cpuinfo")))
		return false;

	$str = implode("", $str);
	@preg_match_all("/processor\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $processor);
	@preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);

	if (count($model[0]) == 0)
	{
		@preg_match_all("/Hardware\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
	}
	@preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);

	if (count($mhz[0]) == 0)
	{
		$values = @file("/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_max_freq");
		$mhz = array("", array(sprintf('%.3f', intval($values[0])/1000)));
	}

	@preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
	@preg_match_all("/(?i)bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);
	@preg_match_all("/(?i)(flags|Features)\s{0,}\:+\s{0,}(.+)[\r\n]+/", $str, $flags);

	$zh = (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh');

	if (is_array($model[1]))
	{
		$info['num'] = sizeof($processor[1]);
		$info['model'] = $model[1][0];
		$info['frequency'] = $mhz[1][0];
		$info['bogomips'] = $bogomips[1][0];
		if (count($cache[0]) > 0)
			$info['l2cache'] = trim($cache[1][0]);
		$info['flags'] = $flags[2][0];
	}

	return $info;
}

function get_uptime()
{
	if (!($str = @file('/proc/uptime')))
		return false;

	$zh = (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh');

	$uptime = '';
	$str = explode(' ', implode('', $str));
	$str = trim($str[0]);
	$min = $str / 60;
	$hours = $min / 60;
	$days = floor($hours / 24);
	$hours = floor($hours - ($days * 24));
	$min = floor($min - ($days * 60 * 24) - ($hours * 60));
	$duint = !$zh ? (' day'. ($days > 1 ? 's ':' ')) : '天';
	$huint = !$zh ? (' hour'. ($hours > 1 ? 's ':' ')) : '小时';
	$muint = !$zh ? (' minute'. ($min > 1 ? 's ':' ')) : '分钟';

	if ($days !== 0)
		$uptime = $days.$duint;
	if ($hours !== 0)
		$uptime .= $hours.$huint;
	$uptime .= $min.$muint;

	return $uptime;
}

function get_tempinfo()
{
	$info = array();

	if ($str = @file('/sys/class/thermal/thermal_zone0/temp'))
		$info['cpu'] = $str[0]/1000.0;

	#if ($str = @file('/sys/class/thermal/thermal_zone10/temp'))
	#	$info['gpu'] = $str[0]/1000.0;

	return $info;
}

function get_meminfo()
{
	$info = array();

	if (!($str = @file('/proc/meminfo')))
		return false;

	$str = implode('', $str);
	preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
	preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

	$info['memTotal'] = round($buf[1][0]/1024, 2);
	$info['memFree'] = round($buf[2][0]/1024, 2);
	$info['memBuffers'] = round($buffers[1][0]/1024, 2);
	$info['memCached'] = round($buf[3][0]/1024, 2);
	$info['memUsed'] = round($info['memTotal']-$info['memFree']-$info['memBuffers']-$info['memCached'], 2);
	$info['memUsedPercent'] = (floatval($info['memTotal'])!=0)?round($info['memUsed']/$info['memTotal']*100,2):0;
	$info['memBuffersPercent'] = (floatval($info['memTotal'])!=0)?round($info['memBuffers']/$info['memTotal']*100,2):0;
	$info['memCachedPercent'] = (floatval($info['memTotal'])!=0)?round($info['memCached']/$info['memTotal']*100,2):0;

	$info['swapTotal'] = round($buf[4][0]/1024, 2);
	$info['swapFree'] = round($buf[5][0]/1024, 2);
	$info['swapUsed'] = round($info['swapTotal']-$info['swapFree'], 2);
	$info['swapPercent'] = (floatval($info['swapTotal'])!=0)?round($info['swapUsed']/$info['swapTotal']*100,2):0;

	foreach ($info as $key => $value) {
		if (strpos($key, 'Percent') > 0)
			continue;
		if ($value < 1024)
			$info[$key] .= ' M';
		else
			$info[$key] = round($value/1024, 3) . ' G';
	}

	return $info;
}

function get_loadavg()
{
	if (!($str = @file('/proc/loadavg')))
		return false;

	$str = explode(' ', implode('', $str));
	$str = array_chunk($str, 4);
	$loadavg = implode(' ', $str[0]);

	return $loadavg;
}

function get_distname()
{
	foreach (glob('/etc/*release') as $name)
	{
		if ($name == '/etc/centos-release' || $name == '/etc/redhat-release' || $name == '/etc/system-release')
		{
			return array_shift(file($name));
		}

		$release_info = @parse_ini_file($name);

		if (isset($release_info['DISTRIB_DESCRIPTION']))
			return $release_info['DISTRIB_DESCRIPTION'];

		if (isset($release_info['PRETTY_NAME']))
			return $release_info['PRETTY_NAME'];
	}

	return php_uname('s').' '.php_uname('r');
}

function get_boardinfo()
{
	$info = array();

	if (is_file('/sys/class/dmi/id/bios_vendor'))
	{
		$info['BIOSVendor'] = array_shift(file('/sys/class/dmi/id/bios_vendor', FILE_IGNORE_NEW_LINES));
		$info['BIOSVersion'] = array_shift(file('/sys/class/dmi/id/bios_version', FILE_IGNORE_NEW_LINES));
		$info['BIOSDate'] = array_shift(file('/sys/class/dmi/id/bios_date', FILE_IGNORE_NEW_LINES));
	}

	if (is_file('/sys/class/dmi/id/board_name'))
	{
		$info['boardVendor'] = array_shift(file('/sys/class/dmi/id/board_vendor', FILE_IGNORE_NEW_LINES));
		$info['boardName'] = array_shift(file('/sys/class/dmi/id/board_name', FILE_IGNORE_NEW_LINES));
		$info['boardVersion'] = array_shift(file('/sys/class/dmi/id/board_version', FILE_IGNORE_NEW_LINES));
	}
	else if (is_file('/sys/class/dmi/id/product_name'))
	{
		$info['boardVendor'] = array_shift(file('/sys/class/dmi/id/product_name', FILE_IGNORE_NEW_LINES));
		$info['boardName'] = '';
		$info['boardVersion'] = '';
	}

	if (is_dir('/dev/disk/by-id'))
	{
		if ($names=array_filter(scandir('/dev/disk/by-id'), function($k) { return $k[0] != '.' && strpos($k, 'DVD-ROM') === false; }))
		{
			$parts = explode("_", array_shift($names));
			$parts = explode("-", array_shift($parts), 2);
			$info['diskVendor'] = strtoupper($parts[0]);
			$info['diskModel'] = $parts[1];
		}
	}

	return $info;
}

function get_diskinfo()
{
	$info = array();

	$info['diskTotal'] = round(@disk_total_space('.')/(1024*1024*1024),2);
	$info['diskFree'] = round(@disk_free_space('.')/(1024*1024*1024),2);
	$info['diskUsed'] = round($info['diskTotal'] - $info['diskFree'],2);
	$info['diskPercent'] = 0;
	if (floatval($info['diskTotal']) != 0)
		$info['diskPercent'] = round($info['diskUsed']/$info['diskTotal']*100, 2);

	return $info;
}

function get_netdev()
{
	$info = array();

	$strs = @file('/proc/net/dev');
	for ($i = 2; $i < count($strs); $i++ )
	{
		$parts = preg_split('/\s+/', trim($strs[$i]));
		$dev = trim($parts[0], ':');
		$info[$dev] = array(
			'rx' => intval($parts[1]),
			'human_rx' => human_filesize($parts[1]),
			'tx' => intval($parts[9]),
			'human_tx' => human_filesize($parts[9]),
		);
	}

	return $info;
}

function get_netarp()
{
	$info = array();

	$seen = array();
	$strs = @file('/proc/net/arp');
	for ($i = 1; $i < count($strs); $i++ )
	{
		$parts = preg_split('/\s+/', $strs[$i]);
		if ('0x2' == $parts[2] && !isset($seen[$parts[3]])) {
			$seen[$parts[3]] = true;
			$info[$parts[0]] = array(
				'hw_type' => $parts[1]=='0x1'?'ether':$parts[1],
				'hw_addr' => $parts[3],
				'device' => $parts[5],
			);
		}
	}

	return $info;
}

function my_json_encode($a=false)
{
	if (is_null($a))
		return 'null';
	if ($a === false)
		return 'false';
	if ($a === true)
		return 'true';
	if (is_scalar($a))
	{
		if (is_float($a))
		{
			return floatval(str_replace(',', '.', strval($a)));
		}
		if (is_string($a))
		{
			static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
			return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
		}
		else
			return $a;
	}
	$isList = true;
	for ($i = 0, reset($a); $i < count($a); $i++, next($a))
	{
		if (key($a) !== $i)
		{
			$isList = false;
			break;
		}
	}
	$result = array();
	if ($isList)
	{
		foreach ($a as $v)
			$result[] = my_json_encode($v);
		return '[' . join(',', $result) . ']';
	}
	else
	{
		foreach ($a as $k => $v)
			$result[] = my_json_encode($k).':'.my_json_encode($v);
		return '{' . join(',', $result) . '}';
	}
}

if (!function_exists('json_encode'))
{
	function json_encode($a, $flag)
	{
		return my_json_encode($a);
	}
}

switch ($_GET['method']) {
	case 'phpinfo':
		phpinfo();
		exit;
	case 'sysinfo':
		echo json_encode(array(
			'stat' => get_stat(),
			'stime' => date('Y-m-d H:i:s'),
			'uptime' => get_uptime(),
			'tempinfo' => get_tempinfo(),
			'meminfo' => get_meminfo(),
			'loadavg' => get_loadavg(),
			'sockstat' => get_sockstat(),
			'diskinfo' => get_diskinfo(),
			'netdev' => get_netdev(),
		));
		exit;
}

$time_start = microtime(true);
$stat = get_stat();
$LC_CTYPE = setlocale(LC_CTYPE, 0);
$uname = php_uname();
$stime = date('Y-m-d H:i:s');
$distname = get_distname();
$server_addr = get_server_addr();
$remote_addr = get_remote_addr();
$uptime = get_uptime();
$cpuinfo = get_cpuinfo();
$tempinfo = get_tempinfo();
$meminfo = get_meminfo();
$loadavg = get_loadavg();
$sockstat = get_sockstat();
$boardinfo = get_boardinfo();
$diskinfo = get_diskinfo();
$netdev = get_netdev();
$netarp = get_netarp();

@header("Content-Type: text/html; charset=utf-8");

?><!DOCTYPE html>
<meta charset="utf-8">
<title><?php echo $_SERVER['SERVER_NAME']; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<style>
body {
	margin: 0;
	font-family: "Helvetica Neue", "Luxi Sans", "DejaVu Sans", Tahoma, "Hiragino Sans GB", "Microsoft Yahei", sans-serif;
}
.container {
	padding-right: 15px;
	padding-left: 15px;
	margin-right: auto;
	margin-left: auto;
}
@media(min-width:768px) {
	.container {
		max-width: 750px;
	}
}
@media(min-width:992px) {
	.container {
		max-width: 970px;
	}
}
@media(min-width:1200px) {
	.container {
		max-width: 1170px;
	}
}
</style>

<div class="container">

<table></table>

<table>
	<tr>
	<th colspan="4"><?php __('Server Information'); ?></th>
	</tr>
	<tr>
	<td><?php __('Hostname'); ?></td>
	<td colspan="3"><?php $os = explode(' ', $uname); echo $os[1];?>(<?php echo $server_addr; ?>)&nbsp;&nbsp;
		<button class="link" onclick="alert(navigator.userAgent)"><?php __('Client Infomation'); ?>:</button>
		<span id="remoteip"><?php echo $remote_addr;?></span>
		<span id="iploc"></span>
	</td>
	</tr>
	<tr>
	<td><?php __('Uname'); ?></td>
	<td colspan="3"><?php echo $uname;?></td>
	</tr>
	<tr>
	<td><?php __('OS'); ?></td>
	<td><?php echo $distname; ?></td>
	<td><?php __('Language'); ?></td>
	<td><?php echo $LC_CTYPE=='C'?'POSIX':$LC_CTYPE;?></td>
	</tr>
	<tr>
	<td><?php __('Server Software'); ?></td>
	<td><?php echo $_SERVER['SERVER_SOFTWARE'] . " php/" . phpversion();?></td>
	<td><?php __('Server Port'); ?></td>
	<td><?php echo $_SERVER['SERVER_PORT'];?></td>
	</tr>
	<tr>
	<td><?php __('Prober Path'); ?></td>
	<td colspan="3"><?php echo str_replace('\\','/',__FILE__)?str_replace('\\','/',__FILE__):$_SERVER['SCRIPT_FILENAME'];?></td>
	</tr>
</table>

<table>
	<tr>
	<th colspan="4"><?php __('Server Realtime Data'); ?></th>
	</tr>
	<tr>
	<td><?php __('Time'); ?></td>
	<td><span id="stime"><?php echo $stime;?></span></td>
	<td><?php __('Uptime'); ?></td>
	<td><span id="uptime"><?php echo $uptime;?></span></td>
	</tr>
	<tr>
	<td><?php __('CPU Model'); ?> [<?php echo $cpuinfo['num'];?>x]</td>
	<td colspan="3">
		<?php echo $cpuinfo['model']; ?>
		| <?php __('Frequency'); ?>: <?php echo $cpuinfo['frequency']; ?> MHz
		| <?php __('L2 Cache'); ?>：<?php echo $cpuinfo['l2cache']; ?>
		| Bogomips: <?php echo $cpuinfo['bogomips']; ?> ×<?php echo $cpuinfo['num'];?>
	</td>
	</tr>
	<tr>
	<td><?php __('CPU Instruction Set'); ?></td>
	<td colspan="3" style="word-wrap: break-word;width: 64em;"><?php echo $cpuinfo['flags'];?></td>
	</tr>
<?php if (isset($tempinfo['cpu'])) : ?>
	<tr>
	<td><?php __('CPU Temperature'); ?></td>
	<td><span id="cpu_temp"><?php echo $tempinfo['cpu'];?></span></td>
	<td><?php __('GPU Temperature'); ?></td>
	<td><span id="gpu_temp"><?php echo $tempinfo['gpu'];?></span></td>
	</tr>
<?php endif; ?>
<?php if (isset($boardinfo['BIOSVendor'])) : ?>
	<tr>
	<td><?php __('BIOS Version'); ?></td>
	<td><?php echo $boardinfo['BIOSVendor'] . " " . $boardinfo['BIOSDate'];?></td>
	<td><?php __('Board Vendor'); ?></td>
	<td><?php echo $boardinfo['boardVendor'] . " " . $boardinfo['boardName'] . " " . $boardinfo['boardVersion'];?></td>
	</tr>
<?php endif; ?>
<?php if (isset($boardinfo['diskModel'])) : ?>
	<tr>
	<td><?php __('HardDisk Model'); ?></td>
	<td colspan="3"><?php echo $boardinfo['diskModel'] . " " . $boardinfo['diskVendor'];?></td>
	</tr>
<?php endif; ?>
	<tr>
	<td><?php __('CPU Usage'); ?></td>
	<td colspan="3">
	<span id="stat_user" class="text-info">0.0</span> user,
	<span id="stat_sys" class="text-info">0.0</span> sys,
	<span id="stat_nice">0.0</span> nice,
	<span id="stat_idle" class="text-info">99.9</span> idle,
	<span id="stat_iowait">0.0</span> iowait,
	<span id="stat_irq">0.0</span> irq,
	<span id="stat_softirq">0.0</span> softirq,
	<span id="stat_steal">0.0</span> steal
	<div class="progress">
	<div id="stat_UserBar" class="progress-bar progress-bar-success" role="progressbar" style="width:1px" ></div>
	<div id="stat_SystemBar" class="progress-bar progress-bar-warning" role="progressbar" style="width:0px" ></div>
	</div>
	</td>
	</tr>
	<tr>
	<td><?php __('Memory Usage'); ?></td>
	<td colspan="3">
	<?php __('Physical Memory'); ?> <span id="meminfo_Total" class="text-info"><?php echo $meminfo['memTotal'];?> </span>
	, <?php __('Used'); ?> <span id="meminfo_Used" class="text-info"><?php echo $meminfo['memUsed'];?></span>
	, <?php __('Cached'); ?> <span id="meminfo_Buffers" class="text-info"><?php echo $meminfo['memBuffers'];?></span>
	/ <span id="meminfo_Cached" class="text-info"><?php echo $meminfo['memCached'];?></span>
	, <?php __('Free'); ?> <span id="meminfo_Free" class="text-info"><?php echo $meminfo['memFree'];?></span>
	, <?php __('Percent'); ?> <span id="meminfo_UsedPercent"><?php echo $meminfo['memUsedPercent'];?></span>%<br>
	<div class="progress">
	<div id="meminfo_UsedBar" class="progress-bar progress-bar-warning" role="progressbar" style="width:<?php echo $meminfo['memUsedPercent'];?>%" ></div>
	<div id="meminfo_BuffersBar" class="progress-bar progress-bar-info" role="progressbar" style="width:<?php echo $meminfo['memBuffersPercent'];?>%" ></div>
	<div id="meminfo_CachedBar" class="progress-bar progress-bar-success" role="progressbar" style="width:<?php echo $meminfo['memCachedPercent'];?>%" ></div>
	</div>
<?php if($meminfo['swapTotal']>0): ?>
	SWAP：<span id="meminfo_swapTotal"><?php echo $meminfo['swapTotal'];?></span>
	, <?php __('Used'); ?> <span id="meminfo_swapUsed"><?php echo $meminfo['swapUsed'];?></span>
	, <?php __('Free'); ?> <span id="meminfo_swapFree"><?php echo $meminfo['swapFree'];?></span>
	, <?php __('Percent'); ?> <span id="meminfo_swapPercent"><?php echo $meminfo['swapPercent'];?></span>%
	<div class="progress"><div id="meminfo_swapBar" class="progress-bar progress-bar-danger" role="progressbar" style="width:<?php echo $meminfo['swapPercent'];?>%" ></div> </div>
<?php endif; ?>
	</td>
	</tr>
	<tr>
	<td><?php __('Disk Usage'); ?></td>
	<td colspan="3">
	<?php __('Total Space'); ?> <?php echo $diskinfo['diskTotal'];?>&nbsp;G，
	<?php __('Used'); ?> <span id="diskinfo_Used"><?php echo $diskinfo['diskUsed'];?></span>&nbsp;G，
	<?php __('Free'); ?> <span id="diskinfo_Free"><?php echo $diskinfo['diskFree'];?></span>&nbsp;G，
	<?php __('Percent'); ?> <span id="diskinfo_Percent"><?php echo $diskinfo['diskPercent'];?></span>%
	<div class="progress"><div id="diskinfo_UsedBar" class="progress-bar progress-bar-black" role="progressbar" style="width:<?php echo $diskinfo['diskPercent'];?>%" ></div> </div>
	</td>
	</tr>
	<tr>
	<td><?php __('Loadavg'); ?></td>
	<td colspan="3" class="text-danger"><span id="loadAvg"><?php echo $loadavg;?></span></td>
	</tr>
	<td><?php __('Sockets'); ?></td>
	<td colspan="3"><span id="sockets"><?php echo $sockstat['sockets']['used'];?></span></td>
	</tr>
</table>

<table class="table table-striped table-bordered table-hover table-condensed">
	<tr><th colspan="5"><?php __('Network Usage'); ?></th></tr>
<?php foreach ($netdev as $dev => $info ) : ?>
	<tr>
	<td style="width:13%"><?php echo $dev;?> : </td>
	<td style="width:29%"><?php __('Rx'); ?>: <span class="text-info" id="<?php printf('netdev_%s_human_rx', $dev);?>"><?php echo $info['human_rx']?></span></td>
	<td style="width:14%"><?php __('Realtime'); ?>: <span class="text-info" id="<?php printf('netdev_%s_delta_rx', $dev);?>">0B/s</span></td>
	<td style="width:29%"><?php __('Tx'); ?>: <span class="text-info" id="<?php printf('netdev_%s_human_tx', $dev);?>"><?php echo $info['human_tx']?></span></td>
	<td style="width:14%"><?php __('Realtime'); ?>: <span class="text-info" id="<?php printf('netdev_%s_delta_tx', $dev);?>">0B/s</span></td>
	</tr>
<?php endforeach; ?>
</table>

<table class="table table-striped table-bordered table-hover table-condensed">
	<tr><th colspan="5"><?php __('Network Neighborhood'); ?></th></tr>
<?php foreach ($netarp as $ip => $info ) : ?>
	<tr>
	<td><?php echo $ip;?> </td>
	<td>MAC: <span class="text-info"><?php  echo $info['hw_addr'];?></span></td>
	<td><?php __('Type'); ?>: <span class="text-info"><?php echo $info['hw_type'];?></span></td>
	<td><?php __('Device'); ?>: <span class="text-info"><?php echo $info['device'];?></span></td>
	</tr>
<?php endforeach; ?>
</table>

<table class="table table-striped table-bordered table-hover table-condensed">
	<tr><th colspan="12"><a href="?method=phpinfo"><?php __('PHP Information'); ?></a></th></tr>
	<tr>
	<td style="width:16.7%"><?php __('Version'); ?></td><td><?php echo phpversion(); ?></td>
	<td style="width:16.7%"><?php __('Zend OpCache'); ?></td><td><?php echo ini_get('opcache.enable') == 1 ? 'On':'Off'; ?></td>
	</tr>
	<tr>
	<td style="width:16.7%"><?php __('Server API'); ?></td><td><?php echo php_sapi_name(); ?></td>
	<td style="width:16.7%"><?php __('Memory Limit'); ?></td><td><?php echo ini_get('memory_limit'); ?></td>
	</tr>
	<tr>
	<td style="width:16.7%"><?php __('POST Max Size'); ?></td><td><?php echo ini_get('post_max_size'); ?></td>
	<td style="width:16.7%"><?php __('Upload Max FileSize'); ?></td><td><?php echo ini_get('upload_max_filesize'); ?></td>
	</tr>
	<tr>
	<td style="width:16.7%"><?php __('Max Execution Time'); ?></td><td><?php echo ini_get('max_execution_time'); ?>s</td>
	<td style="width:16.7%"><?php __('Default Socket Timeout'); ?></td><td><?php echo ini_get('default_socket_timeout'); ?>s</td>
	</tr>
	<tr>
	<td style="width:16.7%"><?php __('PHP Extension'); ?></td>
	<td colspan="9"><?php echo join(' ', get_loaded_extensions()); ?></td>
	</tr>
</table>

<table class="table table-striped table-bordered table-hover table-condensed">
	<tr>
	<td>PHP <?php __('Prober'); ?>(<a href="https://phuslu.github.io/tz.php"><?php __('Turbo Version'); ?></a>) v1.0</td>
	<td>Processed in <?php printf('%0.1f', (microtime(true) - $time_start)*1000);?> ms, <?php echo round(memory_get_usage()/1024, 0).' KB';?> memory usage.</td>
	<td><a href="javascript:scroll(0,0)"><?php __('Back to top'); ?></a></td>
	</tr>
</table>

</div>

<style>
table {
	width: 100%;
	max-width: 100%;
	margin-bottom: 20px;
	border: 1px solid #ddd;
	padding: 0;
	border-collapse: collapse;
}
table th {
	font-size: 14px;
}
table tr {
	border: 1px solid #ddd;
	padding: 5px;
}
table tr:nth-child(odd) {
	background: #f9f9f9
}
table th, table td {
	border: 1px solid #ddd;
	font-size: 14px;
	line-height: 20px;
	padding: 3px;
	text-align: left;
}
table.table-hover > tbody > tr:hover > td,
table.table-hover > tbody > tr:hover > th {
	background-color: #f5f5f5;
}
a {
	color: #337ab7;
	text-decoration: none;
}
a:hover, a:focus {
	color: #2a6496;
	text-decoration: underline;
}
button.link {
	margin: 0;
	font-family: "Helvetica Neue", "Luxi Sans", "DejaVu Sans", Tahoma, "Hiragino Sans GB", "Microsoft Yahei", sans-serif;
	font-size: 14px;
	background: none;
	border: none;
}
.text-info {
	color: #3a87ad;
}
.text-danger {
	color: #b94a48;
}
.progress {
	height:10px;
	width:90%;
	margin-bottom: 20px;
	overflow: hidden;
	background-color: #f5f5f5;
	border-radius: 4px;
	box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}
.progress-bar {
	float: left;
	width: 0;
	height: 100%;
	font-size: 12px;
	color: #ffffff;
	text-align: center;
	background-color: #428bca;
	box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.15);
	transition: width 0.6s ease;
	background-size: 40px 40px;
}
.progress-bar-success {
	background-color: #5cb85c;
}
.progress-bar-info {
	background-color: #5bc0de;
}
.progress-bar-warning {
	background-color: #f0ad4e;
}
.progress-bar-danger {
	background-color: #d9534f;
}
.progress-bar-black {
	background-color: #333;
}
</style>

<script type="text/javascript">
var dom = {
	element: null,
	get: function (o) {
		function F() { }
		F.prototype = this
		obj = new F()
		obj.element = (typeof o == "object") ? o : document.createElement(o)
		return obj
	},
	width: function (w) {
		if (!this.element)
			return
		this.element.style.width = w
		return this
	},
	html: function (h) {
		if (!this.element)
			return
		this.element.innerHTML = h
		return this
	}
};

$ = function(s) {
	return dom.get(document.getElementById(s.substring(1)))
};

$.getData = function (url, f) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', url, true)
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xhr.status == 200) {
			f(xhr.responseText)
		}
	}
	xhr.send()
}

$.getJSON = function (url, f) {
	var xhr = new XMLHttpRequest();
	xhr.open('GET', url, true)
	xhr.onreadystatechange = function() {
		if (xhr.readyState == 4 && xhr.status == 200) {
			if (window.JSON) {
				f(JSON.parse(xhr.responseText))
			} else {
				f((new Function('return ' + xhr.responseText))())
			}
		}
	}
	xhr.send()
}

var stat = <?php echo json_encode($stat); ?>;
var netdev = <?php echo json_encode($netdev); ?>;

function getSysinfo() {
	$.getJSON('?method=sysinfo', function (data) {
		$('#uptime').html(data.uptime)
		$('#stime').html(data.stime)

		if (data.tempinfo.cpu)
			$('#cpu_temp').html(data.tempinfo.cpu+' ℃')
		if (data.tempinfo.gpu)
			$('#gpu_temp').html(data.tempinfo.gpu+' ℃')

		stat_total = 0
		for (var i = 0; i < data.stat.length; i++) {
			stat[i] = data.stat[i] - stat[i]
			stat_total += stat[i]
		}
		$("#stat_user").html((100*stat[0]/stat_total).toFixed(1))
		$("#stat_nice").html((100*stat[1]/stat_total).toFixed(1))
		$("#stat_sys").html((100*stat[2]/stat_total).toFixed(1))
		$("#stat_idle").html((100*stat[3]/stat_total).toFixed(1).substring(0,4))
		$("#stat_iowait").html((100*stat[4]/stat_total).toFixed(1))
		$("#stat_irq").html((100*stat[5]/stat_total).toFixed(1))
		$("#stat_softirq").html((100*stat[6]/stat_total).toFixed(1))
		$("#stat_steal").html((100*stat[7]/stat_total).toFixed(1))
		$("#stat_UserBar").width(100 * (stat[0]+stat[1])/stat_total+'%')
		$("#stat_SystemBar").width((100 * (stat_total-stat[0]-stat[1]-stat[3])/stat_total)+'%')
		stat = data.stat

		$('#meminfo_Total').html(data.meminfo.memTotal)
		$('#meminfo_Used').html(data.meminfo.memUsed)
		$('#meminfo_Free').html(data.meminfo.memFree)
		$('#meminfo_UsedPercent').html(data.meminfo.memUsedPercent)
		$('#meminfo_UsedBar').width(data.meminfo.memUsedPercent)
		$('#meminfo_BuffersBar').width(data.meminfo.memBuffersPercent)
		$('#meminfo_CachedBar').width(data.meminfo.memCachedPercent)

		$('#meminfo_swapTotal').html(data.meminfo.swapTotal)
		$('#meminfo_swapUsed').html(data.meminfo.swapUsed)
		$('#meminfo_swapFree').html(data.meminfo.swapFree)
		$('#meminfo_swapPercent').html(data.meminfo.swapPercent)
		$('#meminfo_swapBar').width(data.meminfo.swapPercent)

		$('#diskinfo_Used').html(data.diskinfo.diskUsed)
		$('#diskinfo_Free').html(data.diskinfo.diskFree)
		$('#diskinfo_Percent').html(data.diskinfo.diskPercent)
		$('#diskinfo_UsedBar').width(data.diskinfo.diskPercent)

		$('#loadAvg').html(data.loadavg)
		$('#sockets').html(data.sockstat.sockets.used)

		for (var dev in netdev) {
			var info = netdev[dev]
			$('#netdev_'+ dev + '_human_rx').html(data.netdev[dev].human_rx)
			$('#netdev_'+ dev + '_human_tx').html(data.netdev[dev].human_tx)
			$('#netdev_'+ dev + '_delta_rx').html(((data.netdev[dev].rx - info.rx)/1024).toFixed(2) + 'K/s')
			$('#netdev_'+ dev + '_delta_tx').html(((data.netdev[dev].tx - info.tx)/1024).toFixed(2) + 'K/s')
		}
		netdev = data.netdev
	});
}

function getIploc() {
	$.getData('https://myip.ipip.net/', function (data) {
		remoteip = document.getElementById('remoteip').innerText
		ip = data.match(/\d+\.\d+\.\d+\.\d+/)[0]
		iploc = data.substring(data.lastIndexOf('：')+1).replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '')
		if (ip !== remoteip) {
			$("#iploc").html('(检测到分流代理，'+ data + ')')
		} else {
			$("#iploc").html('('+iploc+')')
		}
	})
}

window.onload = function() {
	setTimeout(getIploc, 0)
	setInterval(getSysinfo, 1000)
}

</script>
