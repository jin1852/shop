$(function(){
	//收藏到源代码
	function AddFavorite(title, url) {
     try {
    	window.external.addFavorite(url, title);
   		} catch (e) {
    	try {
     		window.sidebar.addPanel(title, url, "");
    	} catch (e) {
      		alert("抱歉，您所使用的浏览器无法完成此操作。\n\n加入收藏失败，请使用Ctrl+D进行添加");
     	}
    }
  }

	$('#top_nav').find('.favorite').on('click',function(){
		AddFavorite(document.title, window.location.href);
	})

	//切换地址
	$('#top_nav').find('.province li').on('click',function(){
		$('#top_nav').find('.address strong').text($(this).find('a').text());
	})

	//点击关闭顶部图片ad
	$('.top_banner').find('a.p_close').on('click',function(){
		console.log('aa');
		$(this).parent().css('display','none');
	})

	//搜索提示
	$hints = $('#header .suggest');
	$('#header .keywords').on('keyup',function(){
		if($(this).val() == ""){
			$hints.css('display','none');
			return;
		}
		$.ajax({
			type:"GET",
			data:'query='+$(this).val(),
			//url:'index.php/index/getSugessions',
			url:infoUrl,
			dataType:'json',
			success:function(data){
				var shtml = '';
				if(data && (data.length > 0)){
					for(var i=0;i<data.length;i++){
						shtml += '<li><a href="http://localhost'+goodsUrl+'id/'+data[i].prodId+'">'+data[i].prodName+'</li>';
					}
					$hints.html(shtml);
					$hints.css('display','block');
				}else{
					$hints.css('display','none');
				}
			}
		})
	})

})