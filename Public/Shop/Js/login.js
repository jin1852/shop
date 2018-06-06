$(function() {
	//定义一个json格式的数据
	var loginInfo = {}; 
	//用户名
	$("#username").focus(function(){
		$("#username + div.checkInfoImg").removeClass('i-success');
		$("#username").next(".checkInfoImg").removeClass('i-error');
		$("#loginname_error").attr('class','hide')
	}).blur(function() {
		loginInfo['uname'] = $.trim($(this).val());
		if(!loginInfo['uname']) {
			$("#username").next(".checkInfoImg").addClass('i-error');
		}else{
			$("#username").next(".checkInfoImg").removeClass('i-error');
			$("#username").next(".checkInfoImg").addClass('i-success');
		}
	});
	//密码
	$("#pwd").focus(function(){
		$("#pwd").next(".checkInfoImg").removeClass('.i-success').remove(".i-error");
		//$("#pwd").next(".checkInfoImg").removeClass('.i-error');
		$("#loginpwd_error").attr('class','hide')
	}).blur(function() {
		loginInfo['upwd'] = $.trim($(this).val());
		if(!loginInfo['upwd']) {
			$("#pwd").next(".checkInfoImg").addClass('i-error');
		}else{
			$("#pwd").next(".checkInfoImg").removeClass('i-error');
			$("#pwd").next(".checkInfoImg").addClass('i-success');
		}
	});
	//验证码
	$("#authcode").focus(function(){
		$("#authcode").next(".checkInfoImg1").removeClass('.i-success');
		$("#authcode").next(".checkInfoImg1").removeClass('.i-error');
		$("#authcode_error").attr('class','hide')
	}).blur(function() {
		loginInfo['code'] = $.trim($(this).val());
		if (!loginInfo['code']) {
			$("#authcode").next(".checkInfoImg1").addClass('i-error');
		} else {
			$("#authcode").next(".checkInfoImg1").removeClass('i-error');
			$("#authcode").next(".checkInfoImg1").addClass('i-success');
		}
	});
	//点击登陆
	$("#loginsubmit").click(function () {
		loginInfo['uname']=$("#username").val();
		loginInfo['upwd'] = $("#pwd").val();
		loginInfo['code'] = $("#authcode").val();
		//判断用户名
		if (!loginInfo['uname']) {
			$("#loginname_error").attr('class', 'error').html("用户名不能为空");
			return false;
		}
		//判断密码
		if (!loginInfo['upwd']) {
			$("#loginpwd_error").attr('class', 'error').html("密码不能为空");
			return false;
		}
		//验证验证码
		if (loginInfo['code']) {
			if(loginInfo['code'].length!=4){
				$("#authcode_error").attr('class', 'error').html("验证码错误");
				return false;
			}
		}else{
			$("#authcode_error").attr('class', 'error').html("验证码不能为空");
			return false;
		}
		//发送数据
		$.post(address,loginInfo,function(data){
			switch (data) {
				//验证通过
				case 0: {
					window.location.href = addressIndex;
					break;
					}
				//用户名不对
				case 1:{
					$("#loginname_error").removeClass('hide');
					$("#loginname_error").attr('class', 'error').html("账户不存在");
					break;
					}
				//密码错误
				case 2: {
					$("#loginpwd_error").removeClass('hide');
					$("#loginpwd_error").attr('class', 'error').html("密码不正确");
					break;
					}
				//验证码错误
				case 3: {
					$("#authcode_error").attr('class', 'error').html("验证码不正确");
					$("#authcode_error").addClass('error');
					break;
					}
			}
		});
	});
	//end
});