<?php

if (isset($_FILES['photo']) && !$_FILES['photo']['error'])
{
  $filename = $_FILES['photo']['name'];
  if (preg_match('/\.(php|cgi)$/', $filename))
  {
    $filename .= '.txt';
  }

  if (file_exists($filename))
  {
    echo 'Upload error, "' . $filename . '" already exists.';
    die();
  }

  if (!move_uploaded_file($_FILES['photo']['tmp_name'], $filename))
  {
    echo 'Upload error, please check folder permisson';
  }
  else
  {
    echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filename;
  }
  die();
}

?>
<!DOCTYPE HTML>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>文件上传</title>
<style type="text/css" media="screen">
* {
	margin:0;padding:0;
	font-family: Tahoma, "Microsoft Yahei", Arial, Serif;
	color:#222;
}
html,body {
	height:100%;width:100%;
	background:#515151;
}
#drag {
	position: absolute;
	top:10%;left:10%;right:10%;bottom:10%;
	border:3px dashed #333;
	border-radius:2px;
	background:#515151;
}
#drag-text{
	position: absolute;
	top:50%;left:0;right:0;
	margin-top:-20px;
	height:40px;
	line-height:40px;
	text-align:center;
	font-size:20px;
	color:#999;
	cursor:pointer;
}
#drag-file{
	position: absolute;
	display: none;
	top:0;left:0;bottom:0;right:0;
	opacity: 0;
	filter:alpha(opacity=0);
}
#rs{
	position: absolute;
	top:10%;left:10%;right:10%;bottom:10%;
	border-radius:2px;
	background:#eee;
	box-shadow:0 0 3px #000;
	text-align:center;
	display: none;
}
.rs-btn{
	height:45px;
	position: absolute;
	bottom:0;left:0;right:0;
}
.rs-top{
	position: absolute;
	top:10px;bottom:55px;left:10px;right:10px;
}
.rs-top-va-outer{
	text-align: center;
	width: 100%;
	height: 100%;
	display: table;
}
.rs-top-va-inner{
	display: table-cell;
	vertical-align: middle;
}
#rs-img{
	max-height:100%;
	max-width:100%;
			border-radius:5px;
	background:#fff;
}
#rs-text{
	background-color: #eee;
	padding: 8px 3px;
	font-size: 16px;
	border: none;
	box-shadow:0 0 3px #333 inset;
	border-radius: 2px;
	text-align: center;
	width: 80%;
}
</style>

<div id="drag">
	<div id="drag-text">
	请点击此处上传...
	</div>
	<input type="file" id="drag-file" />
</div>
<div id="rs">
	<div class="rs-top"><img src="" id="rs-img"/></div>
	<div class="rs-btn"><input type="text" id="rs-text" readonly="readonly" onclick="this.select()" onmouseover="this.select()" onfocus="this.select()"/></div>
</div>
<script type="text/javascript">
document.getElementById('drag-text').addEventListener('click', function() {
	document.getElementById('drag-file').click();
}, false);

document.getElementById('drag-file').addEventListener('change', function() {
	var files = document.getElementById('drag-file').files;
    var maxsize = 1024 * 1024 * <?php echo min(intval(ini_get('post_max_size')), intval(ini_get('upload_max_filesize'))) ?>;
	if(files.length == 0){
		alert('文件不存在, 请重试');
		return false;
	}
    if(files[0].size >= maxsize) {
        alert('文件过大(大于 ' + maxsize + ')，取消上传');
        return false;
    }

	var data = new FormData();
	data.append('photo', files[0]);

	var xhr = new XMLHttpRequest();
	xhr.open("POST", location.href);
	xhr.upload.addEventListener('progress', function(evt) {
	    if (evt.lengthComputable) {
                var percent = evt.loaded / evt.total * 100;
                document.getElementById('drag-text').innerHTML = Math.ceil(percent) + '%';
            }
        }, false);
	xhr.onreadystatechange = function(){
		if(xhr.readyState == 4 && xhr.status == 200){
			document.getElementById('drag-text').innerHTML = '';
			document.getElementById('rs').style.display = 'block';
			document.getElementById('rs-img').src = xhr.responseText;
			document.getElementById('rs-text').value = xhr.responseText;
		}
	}
	xhr.send(data);
	document.getElementById('drag-text').innerHTML = '文件上传中...';
	document.getElementById('drag-file').disabled = true;
}, false);
</script>

