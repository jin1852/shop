<?php

$targetFolder = '/JD/Public/uploads'; // Relative to the root
$verifyToken = md5('unique_salt' . $_POST['timestamp']);

if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {

			$upload = new \Think\Upload();// 实例化上传类 //实例化tp的上传类
			$upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
			$upload->rootPath = '/JD/Public/Uploads/'; //相对于站点根目录jd
			$upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)

			$info = $upload->upload(); //开始上传
			if(!$info){
				$this->error($upload->getError());
			}else{
		    		foreach($info as $v) {
		    			$pic['file'] = $v['savepath'].$v['savename']; //获取文件名
		    			$pic['smimg'] = $v['savepath'].'sm_'.$v['savename']; //获取缩略图的文件名
		    			$pic['img'] = $upload->rootPath.$v['savepath'].$v['savename']; //获取完整的图片地址
		    			$image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
		    			$image->open($pic['img']);
		    			
		    			$image->thumb(50, 50)->save($upload->rootPath.$v['savepath'].'xs_'.$v[savename]); //根据网站需要生成50*50的缩略图
		    			$image->thumb(160, 160)->save($upload->rootPath.$v['savepath'].'sm_'.$v[savename]); //生成160*160的缩略图
		    			return $pic; //返回相关信息数组
		    	}
		    }

		echo '1';
	} else {
		echo '非法文件名.';
	}
}

	

	// if (in_array($fileParts['extension'],$fileTypes)) {
	// 	move_uploaded_file($tempFile,$targetFile);
	// 	echo '1';
	// } else {
	// 	echo '非法文件名.';
	// }
?>