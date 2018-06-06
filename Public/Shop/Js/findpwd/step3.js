$(function(){
	var stel=false;//历史订单手机好的状态
	var spwd=false;//密码的状态
	var scpwd=false;//重复密码的状态
	$('input[name=historytel]').focus(function(){
		$(this).parent().css('border','2px solid #efa100');
		$('#tel-error').html('请输入您已完成订单的收货人手机号');
	});
	/**历史手机号的校验*/
	$('input[name=historytel]').blur(function(){
		var $this=$(this);
		$(this).parent().css('border','1px solid #ddd');
		$('#tel-error').html('');
		var inputTel=$(this).val();
		if(inputTel.length>0){
			var reg=/^0?(13[0-9]|15[012356789]|18[0236789]|14[57])[0-9]{8}$/;
			if(inputTel.match(reg)){
				stel=true;
			}else{
				$this.parent().css('border','2px solid red');
				$('#tel-error').html('<font style="color:#CC0000">手机号格式错误，请重新输入</font>');
				stel=false;
			}
		}else{
			stel=false;
		}
	});
	/*密码的校验*/
	$('input[name=upwd]').focus(function(){
		$(this).parent().css('border','2px solid #efa100');
		$('#upwd-error').html('由字母加数字或符号组成的的6-20位半角字符，区分大小写');
	});
	$('input[name=upwd]').blur(function(){
		var $this=$(this);
		$(this).parent().css('border','1px solid #ddd');
		$('#upwd-error').html('');
		var inputUpwd=$(this).val();
		if(inputUpwd.length>0){
			var reg=/^\w{6,20}$/;
			if(inputUpwd.length>=6){
				if(inputUpwd.match(reg)){
					$('#upwd-error').html('<font style="color:#088605">密码可用</font>');
					spwd=true;
				}else{
					$this.parent().css('border','2px solid red');
					$('#upwd-error').html('<font style="color:#CC0000">密码格式，请重新输入</font>');
					spwd=false;
				}
			}else{
				$this.parent().css('border','2px solid red');
				$('#upwd-error').html('<font style="color:#CC0000">密码长度不正确，请重新输入</font>');
				spwd=false;
			}
		}else{
			spwd=false;
		}
	});
	//重复密码的校验
	$('input[name=cupwd]').focus(function(){
		$(this).parent().css('border','2px solid #efa100');
		$('#cupwd-error').html('请再次输入新密码');
	});
	$('input[name=cupwd]').blur(function(){
		var $this=$(this);
		$(this).parent().css('border','1px solid #ddd');
		$('#cupwd-error').html('');
		var inputCupwd=$(this).val();
		var inputUpwd=$('input[name=upwd]').val();
		if(inputCupwd.length>0){
			if(inputCupwd==inputUpwd){
				$('#cupwd-error').html('<font style="color:#088605">密码相同</font>')
				scpwd=true;
			}else{
				$this.parent().css('border','2px solid red');
				$('#cupwd-error').html('<font style="color:#CC0000">两次输入密码不一致请重新输入</font>');
				scpwd=false;
			}
		}else{
			scpwd=false;
		}
	});
	/*提交时根据3个状态值进行相应的判断*/
	$('input[type=button]').click(function(){
		if(stel&&spwd&&scpwd){
			var hstel=$('input[name=historytel]').val();
			var npwd=$('input[name=upwd]').val();
				npwd=hex_md5(npwd);
			var ourl=window.location.pathname;
			var reg=/.*.php/;
				ourl=ourl.match(reg);
			var data={hstel:hstel,upwd:npwd};
			$.ajax({
				url:ourl+'/Findpwd/do_changepwd',
				data:data,
				type:'post',
				success:function(rdata){
					if(rdata==0){
						window.location.href=ourl+'/Findpwd/step4';
					}else if(rdata==-1){
						$('input[name=historytel]').parent().css('border','2px solid red');
						$('#tel-error').html('<font style="color:#CC000">历史订单手机号不存在</font>');
							stel=false;
					}else{
						alert('服务器繁忙，请稍后再试');
					}
				},
				error:function(){
					alert("网络通信异常");
				}
			});
		}else{
			if(!stel){
				var stelLength=$('input[name=historytel]').val().length;
				if(stelLength<=0){
					$('input[name=historytel]').parent().css('border','2px solid red');
					$('#tel-error').html('<font style="color:#CC0000">请输入手机号</font>');
				}
			}
			if(!spwd){
				var upwdLength=$('input[name=upwd]').val().length;
				if(upwdLength<=0){
					$('input[name=upwd]').parent().css('border','2px solid red');
					$('#upwd-error').html('<font style="color:#CC0000">请再次输入密码</font>');
				}
			}
			if(!scpwd){
				var cupwdLength=$('input[name=cupwd]').val().length;
				if(cupwdLength<=0){
					$('input[name=cupwd').parent().css('border','2px solid red');
					$('#cupwd-error').html('<font style="color:#CC0000">请再次输入新密码</font>');
				}
			}
		}
	});
});