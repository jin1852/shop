<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>UploadiFive Test</title>
<script src="jquery-1.8.3.js" type="text/javascript"></script>
<script src="jquery.uploadify.min.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="uploadify.css">
<style type="text/css">
body {
	font: 13px Arial, Helvetica, Sans-serif;
}
</style>
</head>

<body>
	<h1>Uploadify Demo</h1>
	<form enctype="multipart/form-data">
		<div id="queue"></div>
		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<input type="submit" value="上传">
	</form>

	<script type="text/javascript">
		<?php $timestamp = time();?>
		$(function() {
			$('#file_upload').uploadify({
				//formData用于传递一些数据到后台
				'formData'     : {
					'timestamp' : '<?php echo $timestamp;?>',
					'token'     : '<?php echo md5('unique_salt' . $timestamp);?>'
				},
				'swf'      : 'uploadify.swf',
				'uploader' : 'uploadify.php',
				'method'   : 'post',
				'onUploadSuccess' : function(file, data, response) {
				        alert('The file was saved to: ' + data);
				    }
			});
		});
	</script>
</body>
</html>