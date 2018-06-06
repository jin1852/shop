$(function(){
	var status1=true;
	var status2=true;
	var status3=true;
	
	$("input[name='upwd']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
			if(val.length>=6){
				var test=/^\w{6,20}$/;
				if(test.test(val)){
					status1=true;
					$("label[for=upwd]").text("密码可用");
					$("label[for=upwd]").css("color","green");
					$this.removeClass('error');
				}else{
					status1=false;
					$this.addClass('error');
					$("label[for=upwd]").text("密码格式不正确");
					$("label[for=upwd]").css("color","red");
				}
			}else{
				status1=false;
				$this.addClass('error');
				$("label[for=upwd]").text("密码至少是6位");
				$("label[for=upwd]").css("color","red");
			}
	})
	$("input[name='r_upwd']").blur(function(){
		var $this=$(this);
		var val=$this.val();
			val=$.trim(val);
		var rval=$("input[name='upwd']").val();
		if(val.length>=6){
			if(val==rval){
				status2=true;
				$("label[for=r_upwd]").text("输入密码相同");	
				$("label[for=r_upwd]").css("color","green");
				$this.removeClass('error');
			}else{
				status2=false;
				$this.addClass('error');
				$("label[for=r_upwd]").text("密码不相同");
				$("label[for=r_upwd]").css("color","red");
			}
		}else{
			status2=false;
			$("label[for=r_upwd]").text("请正确输入密码");
			$("label[for=r_upwd]").css("color","red");
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
					status3=true;
					$this.removeClass('error');
					$("label[for='nickName']").text("昵称可用");
					$("label[for='nickName']").css("color","green");
				}else{
					status3=false;
					$this.addClass('error');
					$("label[for='nickName']").text("昵称格式不正确");
					$("label[for='nickName']").css("color","red");
				}
			}else{
				status3=false;
				$this.addClass('error');
				$("label[for=nickName]").text("昵称名至少是4位");
				$("label[for=nickName]").css("color","red");
			}
	});
	/*数据提交,判断*/
	$("input[type=submit]").click(function(){
		if(status1&&status2&&status3){
			/*Ajax*/
			var $this=$(this);
			var data=$("form").serialize();
			var url=window.location.pathname;
			var reg=/.*php/;
				url=url.match(reg);
				url=url+'/Aedit/gdo_edit';
			$.ajax({
				url:url,
				data:data,
				type:'post',
				success:function(rdata){
					console.log(rdata);
					if(rdata>0){
						alert("修改成功");
						/*连接跳走*/
						$this.prev().children().trigger('click');
					}else if(rdata==0){
						if(!confirm("数据未修改,是否修改")){
							 $this.prev().children().click();
							 $this.prev().click();
						}
					}else{
						alert("修改失败");						
					}
				},
			});
		}else{
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