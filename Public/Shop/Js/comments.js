$(function(){
	var $str1 = '<span style="color:#E52727;font-weight:blod;font-size:14px">\(1分\)</span>';
	var $str2 = '<span style="color:#E52727;font-weight:blod;font-size:14px">\(2分\)</span>';
	var $str3 = '<span style="color:#E52727;font-weight:blod;font-size:14px">\(3分\)</span>';
	var $str4 = '<span style="color:#E52727;font-weight:blod;font-size:14px">\(4分\)</span>';
	var $str5 = '<span style="color:#E52727;font-weight:blod;font-size:14px">\(5分\)</span>';
	var	$span = '<span style="color:#f90;">★</span>';
	$('.form .code1 span').bind('click',function(){
		var $this = $(this);
		var $index = $this.index();
		/*for(var i=0;i<$index;i++){
			console.log($str($index));
		}*/
		/*console.log($index);*/
		if($index == 0){
			$($span).replaceAll('.code1 span:lt(1)');
			$('.code1 span:eq(5)').html($str1);
		}else if($index == 1){
			$($span).replaceAll('.code1 span:lt(2)');
			$('.code1 span:eq(5)').html($str2);
		}else if($index == 2){
			$($span).replaceAll('.code1 span:lt(3)');
			$('.code1 span:eq(5)').html($str3);
		}else if($index == 3){
			$($span).replaceAll('.code1 span:lt(4)');
			$('.code1 span:eq(5)').html($str4);
		}else if($index == 4){
			$($span).replaceAll('.code1 span:lt(5)');
			$('.code1 span:eq(5)').html($str5);
		}
		var $code1 = parseInt($index) + 1;
		$('.form input[name=Gcode]').val($code1);
		/*$.ajax({
			url:"index.php/comments/do_comments",
			type:'post',
			data:{'Gcode':'1'},
			dataType:'json',
			success:function($d){alert($d)},
		});*/
	});
	$('.form .code2 span').bind('click',function(){
		var $this = $(this);
		var $index = $this.index();
		//console.log($index);
		if($index == 0){
			$($span).replaceAll('.code2 span:lt(1)');
			$('.code2 span:eq(5)').html($str1);
		}else if($index == 1){
			$($span).replaceAll('.code2 span:lt(2)');
			$('.code2 span:eq(5)').html($str2);
		}else if($index == 2){
			$($span).replaceAll('.code2 span:lt(3)');
			$('.code2 span:eq(5)').html($str3);
		}else if($index == 3){
			$($span).replaceAll('.code2 span:lt(4)');
			$('.code2 span:eq(5)').html($str4);
		}else if($index == 4){
			$($span).replaceAll('.code2 span:lt(5)');
			$('.code2 span:eq(5)').html($str5);
		}
		var $code2 = parseInt($index) + 1;
		$('.form input[name=Scode]').val($code2);
	});
	$('.form .code3 span').bind('click',function(){
		var $this = $(this);
		var $index = $this.index();
		//console.log($index);
		if($index == 0){
			$($span).replaceAll('.code3 span:lt(1)');
			$('.code3 span:eq(5)').html($str1);
		}else if($index == 1){
			$($span).replaceAll('.code3 span:lt(2)');
			$('.code3 span:eq(5)').html($str2);
		}else if($index == 2){
			$($span).replaceAll('.code3 span:lt(3)');
			$('.code3 span:eq(5)').html($str3);
		}else if($index == 3){
			$($span).replaceAll('.code3 span:lt(4)');
			$('.code3 span:eq(5)').html($str4);
		}else if($index == 4){
			$($span).replaceAll('.code3 span:lt(5)');
			$('.code3 span:eq(5)').html($str5);
		}
		var $code3 = parseInt($index) + 1;
		$('.form input[name=Tcode]').val($code3);
	});
	/*判定是否能够提交信息*/
 	var radio=$("input[name='commentsdj']");
 	var isSelect=false; //记录是否有被选中的
 	$("#submit").click(function(){
 		/*判断是否给本次购物一个总评*/
		radio.each(function(index){
			if(radio.eq(index).attr("checked")){
				isSelect=true;
			}
		})
		if(!isSelect){
			alert("请您给本次购物一个评价吧！");
			return false;
		}
		/*判断是否给本次购买的商品打分*/
		var Gcode = $(".form input[name=Gcode]").val();
		//console.log(Gcode);
		if(Gcode == ""){
			alert('请您给本次购买的商品打个分！');
			return false;
		}
		/*判断是否对商品进行了评价*/
		var content = $('#text').val();
		if(content==""){
			alert("请您对购买的商品进行评价！");
			return false;
		}
		/*判断是否对我们的服务进行了评价*/
		var Scode = $(".form input[name='Scode']").val();
		if(Scode==""){
			alert("请您给我们的服务打个分！");
			return false;
		}
		/*判断是否给本次购物的物流打分*/
		var Tcode = $(".form input[name='Tcode']").val();
		if(Tcode==""){
			alert("请您给本次的物流打个分！");
			return false;
		}
		/*判断是否留下买家印象*/
		var impression = $(".form input[name='impression']").val();
		if(impression == ""){
			alert("请您留下您对本次购买的商品的印象吧！");
			return false;
		}
	});
	/*添加买家印象*/
	var $arr = new Array();
	$("#yx span").toggle(function(){
		var $this = $(this);
		var $index = $this.index();
		$arr.push($index);//在数组的最后一个元素后加入新元素
		$this.css("background-color","#FDEDD2");
		$(".form input[name=impression]").val($arr);
	},function(){
		var $this = $(this);
		var $index = $this.index();
		//删除数组中指定元素
		Array.prototype.remove = function(value) {
			var len = this.length;
			for(var i=0,n=0;i<len;i++){
				if(this[i] != value){
					this[n++] = this[i];
				}else{
					console.log(i);
				}
			}
			this.length = n;
		};
		var arr = $('.form input[name=impression]').val();
		var r = arr.split(',');//将字符串以','分隔，并返回数组
		r.remove($index);
		$this.css("background-color","#eee");
		$('.form input[name=impression]').val(r);
	})
})
