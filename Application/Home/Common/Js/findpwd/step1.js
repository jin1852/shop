$(function(){
	//错误提示
	$('input[name=uname]').focus(function(){
		$(this).parent().css('border','2px solid #efa100');
		$('#name-error').html('请输入您的用户名或邮箱');
	});
	$('input[name=uname]').blur(function(){
		$(this).parent().css('border','1px solid #bbb');
		$('#name-error').html('');
	});
	$('input[name=code]').focus(function(){
		$(this).parent().css('border','1px solid #efa100')
		$('#code-error').html('');
	});
	$('input[name=code]').blur(function(){
		$(this).parent().css('border','1px solid #bbb');
	});
	$(".item :button").click(function(){
		var ourl=window.location.pathname;
		var reg=/.*.php/;
		 	ourl=ourl.match(reg);
		var uname=$('input[name=uname]').val();
		var code=$('input[name=code]').val();
			uname=$.trim(uname);
			code=$.trim(code);
		var ulength=uname.length;
		var clength=code.length;
		/*判定是否输入了信息*/
		if(ulength==0||clength==0){
			if(ulength==0&&clength==0){
				$('input[name=uname]').parent().css('border','2px solid #FF0000');
				$('#name-error').html('<font style=\'color:#CC0000\'>请输入您的用户名或邮箱</font>');
				$('input[name=code]').parent().css('border','2px solid #FF0000');
				$('#code-error').html('<font style=\'color:#CC0000\'>请输入验证码</font>');
			}else if(ulength==0){
				$('input[name=uname]').parent().css('border','2px solid #FF0000');
				$('#name-error').html('<font style=\'color:#CC0000\'>请输入您的用户名或邮箱</font>');
			}else if(clength==0){
				$('input[name=code]').parent().css('border','2px solid #FF0000');
				$('#code-error').html('<font style=\'color:#CC0000\'>请输入验证码</font>');
			}
			return;
		}
		//信息验证
		var data={uname:uname,code:code};
		var	url=ourl+'/Findpwd/check_step1';
		$.ajax({
			url:url,
			data:data,
			type:'post',
			success:function(rdata){
				if(rdata==-1){
					$('input[name=code]').parent().css('border','2px solid #FF0000');
					$('#code-error').html('<font style=\'color:#CC0000\'>请输入验正确证码</font>');
					$('.getcode img').click();
				}else if(rdata==-2){
					$('input[name=uname]').parent().css('border','2px solid #FF0000');
					$('#name-error').html('<font style=\'color:#CC0000\'>您输入的账户名不存在，请核对后重新输入</font>');
				}else if(rdata==0){
					window.location.href=ourl+'/Findpwd/step2';
				}
			}
		});
	});
});