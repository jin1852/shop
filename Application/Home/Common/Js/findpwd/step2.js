$(function(){
	var ourl=window.location.pathname;
	var reg=/.*.php/;
		ourl=ourl.match(reg);
	$('.item :button').click(function(){
		var findmethod=$('select[name=findmethod]').val();
		var useremail=$('input[name=email').val();
		var data={findmethod:findmethod,useremail:useremail};
		var url=ourl+'/Findpwd/send_check';
		$.ajax({
			url:url,
			data:data,
			type:'post',
			success:function(rdata){
				if(rdata==1){
					window.location.href=ourl+'/Findpwd/email_success';
				}else if(rdata==2){
					console.log("短信发送中");
				}else{
					alert("发送验证信息失败");
				}
			},
			error:function(rdata){
				alert('网络通信异常');
			}
		});
	});
});