$(function(){
	$("a[name=gisdeleted]").prev().click(function(){
		var $this=$(this);
		if(session['levelId']>=$this.next().next().attr('value')){
			alert("修改个人信息请使用管理员信息修改模块");
			return false;
		}else{
			return true;
		}
	});
	/*实现ajax动态修改状态*/
	$("a[name=gisdeleted]").click(function(){
		var $this=$(this);
		var val=$this.attr('value');
		var status=$this.children('i').attr('value');
		var data={"adminId":val,'isdeleted':status};
		console.log(status);
		if(session['levelId']>$this.next().attr('value')){
			alert("权限不够");
			return;
		}
		if(status==0){
			status=1;
		}else{
			status=0;
		}
		console.log(status);
		$.ajax({
			url:'gdo_delete',
			data:data,
			type:'post',
			success:function(data){
				if(data>0){
					$this.children('i').attr('value',status);
					if(status==0){
						$this.children('i').removeClass('icon-lock');
						$this.children('i').addClass('icon-unlock');
					}else{
						$this.children('i').removeClass('icon-unlock');
						$this.children('i').addClass('icon-lock');
					}
				}else{
					alert("锁定失败");
				}
			}
		});
	});
	///*实现ajax动态删除*/
	//$("a[value!=1][name='delete']").click(function(){
	//	var $this=$(this);
	//	var val=$this.prev().attr('value');
	//	var data={"adminId":val};
	//	console.log(data,val);
	//	if(session['levelId']>=$this.attr('value')){
	//		alert("权限不够");
	//		return;
	//	}
	//	if($this.attr('value')==2){
	//		if(!confirm("确定要删除")){
	//			return;
	//		}
	//	}
	//	$.ajax({
	//		url:'gDelete',
	//		data:data,
	//		type:'post',
	//		success:function(data){
	//			if(data>0){
	//				var rowIndex=$this.parent().parent().get(0).rowIndex;
	//				document.getElementsByTagName('table')[0].deleteRow(rowIndex);
	//			}else{
	//				alert("删除失败");
	//			}
	//		}
	//	});
	//});
	/*下拉 效果显示*/
	$(".icon-table").next('i').toggle(
		function(){
			$(this).parent().parent().next().slideDown("slow");
			$(this).removeClass('icol-application-put');
			$(this).addClass('icol-application-get');
		},
		function(){
			$(this).parent().parent().next().slideUp("slow");
			$(this).removeClass('icol-application-get');
			$(this).addClass('icol-application-put');
		}
	)
});