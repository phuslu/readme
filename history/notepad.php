<?php

$data_directory = '_notepad';

function sanitize($string) {
	return preg_replace("/[^a-zA-Z0-9]+/", "", $string);
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (empty($_GET['f']) || sanitize($_GET['f']) !== $_GET['f']) {
	header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/readme');
	die();
}

$name = sanitize($_GET['f']);
$path = $data_directory . '/' . $name;

if (isset($_POST['t']) && $name != 'readme') {
	file_put_contents($path, $_POST['t']);
	die();
}

if (preg_match('/^curl\//', $_SERVER ['HTTP_USER_AGENT'])) {
	echo file_get_contents($path);
	die();
}
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?php print $name; ?></title>
<link href="data:image/x-icon;base64,R0lGODlhEAAQAKIGAL7FzEBUaLK5wnOBkJCbp9nd4f///wAAACH5BAEAAAYALAAAAAAQABAAAANJaKrR7msFMga1o8UguvcBECyDUJ1lJjIdWgrharxfG49cDQL8SNeqXk4H4/V+n2BB9GjGjCOJcQrdUHmFZZRxnW4NzyvhC3ZCEgA7" rel="icon" type="image/x-icon" />
<style type="text/css">
body { margin: 0; background: #ebeef1; }
#content { font-size: 100%; margin: 0; padding: 20px; overflow-y: auto; resize: none; width: 100%; height: 100%; min-height: 100%; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 1px #ddd solid; outline: none; }
.container { position: absolute; top: 20px; right: 20px; bottom: 20px; left: 20px; }
</style>
<div class="container">
<textarea id="content"><?php
	if ($name == 'readme') {
		print '您好，这是一个在线的剪切板。您可以得到专属地址并分享出去，比如 https://'.$_SERVER['HTTP_HOST'].'/你的名字';
	} elseif (file_exists($path)) {
		print htmlspecialchars(file_get_contents($path), ENT_QUOTES, 'UTF-8');
	}
?></textarea>
</div>
<script>
function upload() {
	if (content !== textarea.value) {
		var temp = textarea.value;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', window.location.href, true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		xhr.onload = function() {
			if (xhr.readyState === 4) {
				content = temp;
				setTimeout(upload, 1000);
			}
		}
		xhr.onerror = function() {
			setTimeout(upload, 1000);
		}
		xhr.send("t=" + encodeURIComponent(temp));
	} else {
		setTimeout(upload, 1000);
	}
}

var textarea = document.getElementById('content');
var content = textarea.value;

textarea.focus();
upload();
</script>
