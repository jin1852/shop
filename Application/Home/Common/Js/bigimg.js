$(function(){
	var len = $(".images li").length;
	//console.log(len);
	$("#bimg").hover(function(){
		$(".butter").show();
		$(".prev").hover(function(){
			$(this).css("background","black");
		},function(){
			$(this).css("background","#bbb");
		});
		$(".next").hover(function(){
			$(this).css("background","black");
		},function(){
			$(this).css("background","#bbb");
		});
	},function(){
		$(".butter").hide();
	});
	var curIndex = $(".images li").index();
	//console.log(curIndex);
	$(".prev").click(function(){
		if(curIndex > 0){
			curIndex--;
		}else if(curIndex == 0){
			curIndex = len-1;
		}
		$(".images li").removeClass("img1");
		$(".images li").eq(curIndex).addClass("img1");
	});
	$("#bimg .next").click(function(){
		if(curIndex == len-1){
			curIndex = 0;
		}else{
			curIndex++;
		}
		$(".images li").removeClass("img1");
		$(".images li").eq(curIndex).addClass("img1");
	});
	$("#simg li span").hover(function(){
		$(this).css("border","2px solid red");
	},function(){
		$(this).css("border","1px solid #ccc");
	});
	$("#simg li").click(function(){
		var $this = $(this);
		var $index = $this.index();
		console.log($index);
		$(".images li").removeClass("img1");
		$(".images li").eq($index).addClass("img1");
	})
})