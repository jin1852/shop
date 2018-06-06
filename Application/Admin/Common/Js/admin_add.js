//后台管理员的添加信息的相关验证
$(function(){
	var status0=false;
	var status1=false;
	var status2=false;
	var status3=false;
	/*验证用户名*/
	$("input[name='uname']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
			if(val.length>=6){
				var test=/^\w{6,20}$/;
				if(test.test(val)){
					var data={'uname':val};
					$.ajax({
						url:"gdo_addtest",
						data:data,
						type:"post",
						success:function(data){
							if(data==0){
								$("label[for=uname]").text("用户名可用");
								$("label[for=uname]").css("color","green");
								$this.removeClass('error');
								status0=true;
							}else{
								status0=false;
								$this.addClass('error');
								$("label[for=uname]").text("用户名已存在");
								$("label[for=uname]").css("color","red");
							}
						}
					});
				}else{
					$this.addClass('error');
					$("label[for=uname]").text("用户名格式不正确");
					$("label[for=uname]").css("color","red");
					status0=false;
				}
			}else{
				$this.addClass('error');
				$("label[for=uname]").text("用户名至少是6位");
				$("label[for=uname]").css("color","red");
				status0=false;
			}
	});
	/*验证密码*/
	$("input[name='upwd']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
			if(val.length>=6){
				var test=/^\w{6,20}$/;
				if(test.test(val)){
					$("label[for=upwd]").text("密码可用");
					$("label[for=upwd]").css("color","green");
					$this.removeClass('error');
					status1=true;
				}else{
					$this.addClass('error');
					$("label[for=upwd]").text("密码格式不正确");
					$("label[for=upwd]").css("color","red");
					status1=false;
				}
			}else{
				$this.addClass('error');
				$("label[for=upwd]").text("密码至少是6位");
				$("label[for=upwd]").css("color","red");
				status1=false;
			}
	})
	$("input[name='r_upwd']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
		var rval=$("input[name='upwd']").val();
		if(val.length>=6){
			if(val==rval){
				$("label[for=r_upwd]").text("输入密码相同");	
				$("label[for=r_upwd]").css("color","green");
				$this.removeClass('error');
				status2=true;	
			}else{
				$this.addClass('error');
				$("label[for=r_upwd]").text("密码不相同");
				$("label[for=r_upwd]").css("color","red");
				status2=false;
			}
		}else{
			$("label[for=r_upwd]").text("请正确输入密码");
			$("label[for=r_upwd]").css("color","red");
			status2=false;
		}
	})
	/*验证昵称*/
	$("input[name='nickName']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
			if(val.length>=4){
				var test=/^\w{4,20}$/;
				if(test.test(val)){
					$this.removeClass('error');
					$("label[for='nickName']").text("昵称可用");
					$("label[for='nickName']").css("color","green");
					status3=true;
				}else{
					$this.addClass('error');
					$("label[for='nickName']").text("昵称格式不正确");
					$("label[for='nickName']").css("color","red");
					status3=false;
				}
			}else{
				$this.addClass('error');
				$("label[for=nickName]").text("昵称名至少是4位");
				$("label[for=nickName]").css("color","red");
				status3=false;
			}
	});
	/*超级管理员添加提醒*/
	$("select[name='levelId']").change(function(){
		var $this=$(this);
		if($(this).val()==1){
			if(!confirm("确定要添加超级管理员吗！！!")){
				$this.children().first().attr("selected",true);
			}
		}
	});
	/*数据提交,判断*/
	$("input[type=submit]").click(function(){
		if(status0&&status1&&status2&&status3){
			/*Ajax*/
			var data=$("form").serialize();
			$.ajax({
				url:'gdo_add',
				data:data,
				type:'post',
				success:function(data){
					if(data>0){
						alert("添加成功");
						$("input[type='reset']").trigger('click');
						status0=false;
						status1=false;
						status2=false;
						status3=false;
						$("label[for=uname]").text("字母数字下划线组成6到20位");
						$("label[for=uname]").css("color","#333");
						$("label[for=upwd]").text("字母数字下划线组成6到20位");
						$("label[for=upwd]").css("color","#333");
						$("label[for=r_upwd]").text("再次输入密码");
						$("label[for=r_upwd]").css("color","#333");
						$("label[for=nickName]").text("字母数字下划线组成4到20位");
						$("label[for=nickName]").css("color","#333");
					}else{
						alert("添加失败");
					}
				}
			});
		}else{
			if(!status0){
				$("input[name='uname']").addClass('error');
			}
			if(!status1){
				$("input[name='upwd']").addClass('error');
			}
			if(!status2){
				$("input[name='r_upwd']").addClass('error');
			}
			if(!status3){
				$("input[name='nickName']").addClass('error');
			}
		}
	});
});