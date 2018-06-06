$(function(){
	/*实现ajax动态修改砖头删除*/
	$("a[name=gisdeleted]").click(function(){
		var $this=$(this);
		var val=$this.attr('value');
		var status=$this.children('i').attr('value');
		var data={"adminId":val,'isdeleted':status};
		console.log(status);
		if(status==0){
			status=1;
		}else{
			status=0;
		}
		console.log(status);
		action('gdo_delete','修改失败');
		
	});
	function action(url,str){
		$.ajax({
			url:url,
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
					alert(str);
				}
			}
		});
	}
});