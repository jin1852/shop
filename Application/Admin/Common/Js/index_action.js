$(function(){
    /*实现首页橱窗导航栏表ajax动态删除*/
	$("a[name='delete']").live('click',function(){
		var $this=$(this);
		var val=$this.attr('value');
		var data={indexCCId:val};
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
			url=url+'/window/do_delete';
		$.ajax({
			url:url,
			data:data,
			type:'post',
			success:function(data){
				if(data==1){
					window.location.href=window.location.href;
			   	}else if(data=='-1'){
			   		console.log(data);
					alert("没有数据被删除");
				}else{
					window.location.href=window.location.href;
				}
			}
		});
	});
	/*实现橱窗大类别表ajax动态删除*/
	$("a[name='ccdelete']").live('click',function(){
		var $this=$(this);
		var val=$this.attr('value');
		var data={cctptypeId:val};
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
			url=url+'/window/do_ccdelete';
		$.ajax({
			url:url,
			data:data,
			type:'post',
			success:function(data){
				if(data==1){
					window.location.href=window.location.href;
			   	}else if(data=='-1'){
			   		console.log(data);
					alert("没有数据被删除");
				}
			}
		});
	});
	/*下拉 效果显示*/
	$(".icon-table").next('i').toggle(
		function(){
			$(this).parent().parent().next().slideUp("slow");
			$(this).removeClass('icol-application-get');
			$(this).addClass('icol-application-put');
		},
		function(){
			$(this).parent().parent().next().slideDown("slow");
			$(this).removeClass('icol-application-put');
			$(this).addClass('icol-application-get');
		}	
	)
});