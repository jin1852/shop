$(function(){
	var mtext={
			'r_name':"4到20位字母，数字，下划线及“_”组成",
			'r_pass':"6到20位字母，数字，下划线及“_”组成,不建议使用纯数字，纯字母，纯符号",
			'r_rpass':"再次输入密码",
			'r_phone':"完成验证后你可以通过该邮箱登录和找回密码"
		};
	var etext=new Array(
			"请输入用户名",
			"请输入密码",
			"请输入密码",
			"请输入邮箱",
			"请输入验证码"

		);
	var status0=false;
	var status1=false;
	var status2=false;
	var status3=false;
	var status4=false;
	/*去掉提示样式*/
	$("input:lt(4)").focus(function(){
		var index=$(this).parent().next().attr('id');
		$(this).parent().css("borderColor","#ddd");
		$(this).parent().next().removeClass("errshow");
		$(this).parent().next().text(mtext[index]);
		$(this).parent().next().addClass('show');
	});
	$("input:eq(4)").focus(function(){
		$(this).parent().css("borderColor","#ddd");
		$(this).parent().next().next().removeClass("errshow");
	});
	/*添加默认提示效果并验证 */
	$("input:eq(0)").blur(function(){
		$(this).parent().next().removeClass('show');
		checkname(this);//用户名的相关验证
	});
	$("input:eq(1)").blur(function(){
		$(this).parent().next().removeClass('show');
		checkpass(this);//密码的验证
	});
	$("input:eq(2)").blur(function(){
		$(this).parent().next().removeClass('show');
		checkrpass(this);//二次密码的验证
	});
	$("input:eq(3)").blur(function(){
		$(this).parent().next().removeClass('show');
		checkemail(this);//邮箱的验证
	});
	$("input[type=submit]").click(function(){
		checkform();//验证表单整体的函数
	});
	$("input[name='code']").blur(function(){
		checkcode(this);//验证验证码的函数
	});
	function checkcode(obj){
		var val=$(obj).val();
			val=$.trim(val);
		var data={code:val};
		if(val.length==4){
			$.ajax({
				url:'check_code',
				data:data,
				type:'post',
				success:function(data){
					if(data==1){
						$("#r_code").prev().prev().css("borderColor","#ddd");
						$("#r_code").removeClass("errshow");
						$("#r_code").addClass('show');
						$("#r_code").text("验证码正确");
						status4=true;
					}else{
						$("#r_code").prev().prev().css("borderColor","red");
						$("#r_code").addClass("errshow");
						$("#r_code").text("验证码不正确");
						status4=false;
					}
				}
			});
		}
	}
	function checkemail(obj){
		var val=$(obj).val();
		if(val.length){
			var re=/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/;
			if(re.test(val)){
				var data={useremail:val};
				$.ajax({
					url:'check_name_email',
					data:data,
					type:'post',
					success:function(data){
						if(data==0){
							$("#r_phone").prev().css("borderColor","#ddd");
							$("#r_phone").removeClass("errshow");
							$("#r_phone").addClass('show');
							// $("#r_phone").css("color","green");
							$("#r_phone").text("邮箱可用");
							status3=true;	
						}else{
							$("#r_phone").prev().css("borderColor","red");
							$("#r_phone").addClass("errshow");
							$("#r_phone").text("邮箱已经被使用");
							status3=false;
						}

					}
				});
			}else{
				$("#r_phone").prev().css("borderColor","red");
				$("#r_phone").addClass("errshow");
				$("#r_phone").text("邮箱格式不正确");
				status3=false;
			}
		}else{
			$(".inputbox:eq(3)").css("borderColor","red");
			$("#r_phone").addClass("errshow");
			$("#r_phone").text(etext[3]);
			status3=false;
		}
	}
	function checkrpass(obj){
		var val=$(obj).val();
		if(val.length){
			var re=/^\w{6,20}$/;
			if(re.test(val)){
				var val1=$("input:eq(1)").val();
				if(val==val1){
					$("#r_rpass").prev().css("borderColor","#ddd");
					$("#r_rpass").removeClass("errshow");
					$("#r_rpass").addClass('show');
					$("#r_rpass").text("密码相同");
					status2=true;
				}else{
					$("#r_rpass").prev().css("borderColor","red");
					$("#r_rpass").addClass("errshow");
					$("#r_rpass").text("密码不相同");
					status2=false;
				}				
			}else{
				$("#r_rpass").prev().css("borderColor","red");
				$("#r_rpass").addClass("errshow");
				$("#r_rpass").text("密码格时错误");
				status2=false;
			}

		}else{
			$(".inputbox:eq(2)").css("borderColor","red");
			$("#r_rpass").addClass("errshow");
			$("#r_rpass").text(etext[2]);
			status2=false;
		}
	}
	function checkpass(obj){
		var val=$(obj).val();
		if(val.length){
			var re=/^\w{6,20}$/;
			if(re.test(val)){
				$("#r_pass").prev().css("borderColor","#ddd");
				$("#r_pass").removeClass("errshow");
				$("#r_pass").addClass('show');
				$("#r_pass").text("密码可用");
				status1=true;				
			}else{
				$("#r_pass").prev().css("borderColor","red");
				$("#r_pass").addClass("errshow");
				$("#r_pass").text("密码格式错误");
				status1=false;
			}

		}else{
			$(".inputbox:eq(1)").css("borderColor","red");
			$("#r_pass").addClass("errshow");
			$("#r_pass").text(etext[1]);
			status1=false;
		}
	}
	function checkname(obj){
		var val=$(obj).val();
		if(val.length){
			var re=/^\w{4,20}$/;
			if(re.test(val)){
				var data={uname:val};
				$.ajax({
					url:'check_name_email',
					data:data,
					type:'post',
					success:function(data){
						if(data==0){
							$("#r_name").prev().css("borderColor","#ddd");
							$("#r_name").removeClass("errshow");
							$("#r_name").addClass('show');
							$("#r_name").text("用户名可用");
							status0=true;	
						}else{
							$("#r_name").prev().css("borderColor","red");
							$("#r_name").addClass("errshow");
							$("#r_name").text("用户名已存在");
							status0=false;
						}

					}
				});
							
			}else{
				$("#r_name").prev().css("borderColor","red");
				$("#r_name").addClass("errshow");
				$("#r_name").text("用户名不可用");
				status0=false;
			}

		}else{
			$(".inputbox:eq(0)").css("borderColor","red");
			$("#r_name").addClass("errshow");
			$("#r_name").text(etext[0]);
			status0=false;
		}
	}
	function checkform(){
		if(status0&&status1&&status2&&status3&&status4){
			var uname=$("input[name='uname']").val();
			var upwd=$("input[name='upwd']").val();
				upwd=hex_md5(upwd);
			var useremail=$("input[name='useremail']").val();
			var data={uname:uname,upwd:upwd,useremail:useremail};
				$.ajax({
					url:'do_register',
					data:data,
					type:'post',
					success:function(data){
						if(data==0){
							alert("服务器忙，请稍后再试");
						}else{
							var gourl=window.location.pathname;
							var reg=/.*php/;
								gourl=gourl.match(reg);
							window.location.href=gourl+"/Index/index";
							$("form").get(0).reset();
							status0=false;
							status1=false;
							status2=false;
							status3=false;
							status4=false;
						}
					}
				});
			return;
		}
		if(!status0){
			$(".inputbox:eq(0)").css("borderColor","red");
			$("#r_name").addClass("errshow");
			$("#r_name").text(etext[0]);
		}
		if(!status1){
			$(".inputbox:eq(1)").css("borderColor","red");
			$("#r_pass").addClass("errshow");
			$("#r_pass").text(etext[1]);
		}
		if(!status2){
			$(".inputbox:eq(2)").css("borderColor","red");
			$("#r_rpass").addClass("errshow");
			$("#r_rpass").text(etext[2]);
		}
		if(!status3){
			$(".inputbox:eq(3)").css("borderColor","red");
			$("#r_phone").addClass("errshow");
			$("#r_phone").text(etext[3]);
		}
		if(!status4){
			$(".inputbox:eq(4)").css("borderColor","red");
			$("#r_code").addClass("errshow");
			$("#r_code").text(etext[4]);
		}
	}
});