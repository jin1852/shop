$(function(){	
	/*这是头部的图片轮效果区域*/
	var length=$("#middle .images li").length;
	var index=length-1;
	var controller=setInterval(function(){
		if(index<0){
			index=length-1;
		}
		showTurn(index);
		showStyle(index);
		index--;
	},3000);
	/*定义触发事件*/
	$("#middle .num li").hover(
			function(){
				clearInterval(controller);
				showTurn($(this).index());
				showStyle($(this).index());
				var k=$(this).index();
				console.log(k);
				index=k--;
			},
			function(){
				controller=setInterval(function(){
				if(index<0){
					index=length-1;
				}
				showTurn(index);
				showStyle(index);
				index--;
			},3000);
		}
	);
	/*控制图片展示函数*/
	function showTurn(index){
		console.log('a',index);
		var top=-(index)*240;
		$("#middle .images").stop();
		$("#middle .images").animate({
			top:top
		},1000);
	}
	/*底部不列表控制函数*/
	function showStyle(index){
		$("#middle .num li").css("backgroundColor","#aaa");
		$("#middle .num li").eq(index).css("backgroundColor","#ca2f2c");
	}

	/*middle3中的js效果代码*/
	var index1=0;
	var controller1=0;
	controller1=setInterval(function(){
		index1=!index1;
		showStyle1(index1);
		showTurn1(index1);
	},4000);
	$(".middle_title li").click(function(){
		showStyle1($(this).index());
		showTurn1($(this).index());
	})

	function showTurn1(index1){
		if(index1==0){
			$(".middle3 ul li[na='0']").show();
			$(".middle3 ul li[na='1']").hide();
		}else{
			$(".middle3 ul li[na='1']").show();
			$(".middle3 ul li[na='0']").hide();
		}
	}
	function showStyle1(index1){
		$(".middle_title li").css("backgroundColor","#aaa");
		$(".middle_title li").eq(index1).css("backgroundColor","red");
	}
});