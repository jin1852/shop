$(function(){
	/*图片切换*/
	var Index = 0;
	$("#left_simg .b2").click(function(){
		var $this = $(this);
		var $leng = $('.b3').length;
		if($leng > 7){
			$this.parent().find(".b3:eq(0)").animate({marginLeft:"-62px"},function(){
			});
		}
	});
	$("#left_simg .b1").click(function(){
		var $this = $(this);

		$this.parent().find(".b3:eq(0)").animate({marginLeft:"0px"},function(){
		});
	});
	$("#left_simg ul li:not(.b1,.b2)").hover(function(){
		var $this = $(this);
		var $index = $(this).index()-1;
		//console.log($index);
		$this.css("border","0px");
		$this.css("border","2px solid red");
		$("#left_bimg li").removeClass('bimgs1');
		$("#fdj li").removeClass('bimgs1');
		$("#left_bimg li").eq($index).addClass('bimgs1');
		$("#fdj li").eq($index).addClass('bimgs1');
	},function(){
		$(this).css("border","1px solid #ccc");
	});
	
	/*地址选择*/
	$("#adtitle").click(
		function(){
			$(".adlist").show();
		});
	$(".adlist").mouseleave(function(){
		$(".adlist").hide();
	});	
	$("ul[name=a] li").live('click',function(){
		var text=$(this).text();
		$("span[name=a]").text(text);
		$("ul[name=a]").hide();
		$("ul[name=b]").show();
		var u = $(".ad0 span[name=a]").text();
		var diqu={diqu:u};
		$.post(path,diqu,function($data){
			//console.log($data);
			$.each(eval($data),function(i,n){
				$.each(n,function(m,d){
					//console.log(d);
					$(".ad0 ul[name=b]").append("<li>"+d+"</li>");
				})
			})
		});
	});
	$("ul[name=b] li").live('click',function(){
		var text=$(this).text();
		//console.log(text);
		$("span[name=b]").text(text);
		$("ul[name=b]").hide();
		// $("span[name=c]").removeClass("show");
		$("ul[name=c]").show();
		$("span[name=c]").show();
		var u = $(".ad0 span[name=b]").text();
		//console.log(u);
		var diqu={diqu:u};
		$.post(path,diqu,function($data){
			//console.log($data);
			//console.log(eval($data));
			$.each(eval($data),function(i,n){
				//console.log(i + "---" + n);
				$.each(n,function(m,d){
					//console.log(d);
					$(".ad0 ul[name=c]").append("<li>"+d+"</li>");
				})
			})
		});
	});
	$("ul[name=c] li").live('click',function(){
		var text=$(this).text();
		//console.log(text);
		$("span[name=c]").text(text);
		$(".adlist").hide();

		var title=$("span[name=a]").text();
			title+=$("span[name=b]").text();
			title+=$("span[name=c]").text();
		$("#adtitle").text(title);
	});
	$(".ad0 span").click(function(){
		var abc=$(this).attr("name");
		//console.log(typeof eval("abc"));
		//console.log(abc);
		$(".adlist ul").hide();
		$("ul[name="+abc+"]").show();	
	});
	/*图片放大镜效果*/
	//鼠标悬停事件
	$("#left_bimg li").mouseover(function(){
		//console.log($(this).index());
		var $index = $(this).index();
		var img_width = $(this).width();//小图的宽度
		var img_height = $(this).height();//小图的高度
		var img_offset = $(this).offset();//小图的坐标位置
		//console.log(img_width,img_height,img_offset);
		//使fdj显示，并同时控制位置
		$("#fdj").show().offset({'top':'img_offset.top','left':'img_offset.left'});
		/*console.log(img_offset.top,img_offset.left);*/
	}).mouseout(function(){
		$("#fdj").hide();//鼠标离开fdj隐藏
	}).mousemove(function(e){ //鼠标移动事件
		var $this = $(this);
		var $index = $this.index();
		console.log($index);
		//console.log(e.target.getAttribute('imgid'));
		// if(e.target.getAttribute('imgid')){
		// 	$index = e.target.getAttribute('imgid');
		// }
		//console.log($index);
		var $fdj = $("#fdj").find('img');//找到大图对象
		//var $index = $fdj.index();

		//console.log($index);
		var $img = $this.find('img');//找到小图对象
		//console.log('aaaa');
		//console.log($fdj);
		//console.log($img);
		var x = e.pageX;//找到鼠标的横坐标
		var y = e.pageY;//找到鼠标的纵坐标
		/*console.log(x,y);*/
		var xleft = $img.offset().left;//小图的横坐标位置
		var ytop = $img.offset().top;//小图的纵坐标位置
		/*console.log(xleft,ytop);*/
		var dianh = y - ytop;//鼠标相对小图的纵坐标位置
		var dianw = x - xleft;//鼠标相对于小图的横坐标位置
		/*console.log(dianh,dianw);*/
		//$fdj.css('display','block');
		var	bwidth = $fdj.eq($index).width();//获取大图的宽度
		var	bheight = $fdj.eq($index).height();//获取大图的高度
		

		//console.log(bwidth,bheight);
		var swidth = $img.width();//获取小图的宽度
		var sheight = $img.height();//获取小图的高度
		/*console.log(swidth,sheight);*/
		var $fdjdiv = $("#fdj");//找到放大镜的div对象
		var fdjwidth = $fdjdiv.width();//放大镜div的宽度
		var fdjheight = $fdjdiv.height();//放大镜div的高度
		/*console.log(fdjwidth,fdjheight);*/
		var blw = bwidth/swidth;//大图与小图的宽度比
		var blh = bheight/sheight;//大图与小图的高度比
		/*console.log(dianw*blw,dianh*blh);*/
		/*通过比例求fdjdiv的scrollTop和scrollLeft的坐标*/
		var dx = dianw*blw - fdjwidth/2;
		var dy = dianh*blh - fdjheight/2;
		$fdjdiv.scrollTop(dy);
		$fdjdiv.scrollLeft(dx);
	});
	/*选择商品型号*/
	$(".a5").hover(function(){
		$(this).css({"border":"2px solid red","color":"red"});
	},function(){
		$(this).css({"border":"1px solid #ccc","color":"#777"});
	}).click(function(){
		var $str = $(this).html();
		$('.checked').show().html("<span class='banben'>已选择："+'\"'+$str+'\"</span>');
		$(this).css({"border":"2px solid red","color":"red"});

	});



	/*改变购物数量*/
	var num = null;
	var number = 0;
	$(".a7").click(function(){
		var $this = $(this);
		var $index = $this.index();
		/*console.log($index);
		*/
		num = $(".a7").parent().children(":input").attr("value");
		number = parseInt(num);
		if($index == 1){
			if(number > 1){
				number--;
			}else{
				number = 1;
			}
		}
		/*console.log(number);*/
		if($index == 3){
			number++;
		}
		$this.parent().children(":input[name=n]").attr('value',number);
	});
	/*京东优惠服务*/
	$(".yh1").hover(function(){
		var $this = $(this);
		$(".yh2").show();
		$this.css({"border":"2px solid red","color":"red"});
	},function(){
		$(this).css({"border":"1px solid #ccc","color":"#777"});
	});
	$(".yh").hover(function(){
		$(this).css({"border":"2px solid red","color":"red"});
	},function(){
		$(this).css({"border":"1px solid #ccc","color":"#777"});
	}).toggle(function(){
		var $str = $(this).children("span").html();
		$(".checked").show().append(" <span class='xuanze1'>"+$str+"</span>");
	},function(){
		$(".checked .xuanze1").html('').hide();
	});
	$(".yh2").hover(function(){
		$(this).css({"border":"2px solid red","color":"red"});
	},function(){
		$(this).css({"border":"1px solid #ccc","color":"#777"});
		$(this).hide();
	}).toggle(function(){
		var $str = $(this).children("span").html();
		$(".checked").show().html(" <span class='xuanze2'>"+$str+"</span>");
	},function(){
		$(".checked .xuanze2").html('').hide();
	});
	/*加入购物车*/
	$(".buy ul li:eq(0)").bind('click',function(){
		var prodId = $("#right_content_center input[name='goodsProdId']").val();
		//var areas = $("#adtitle").html();
		//var checked = $(".checked span").html();
		//var shopping = {prodId:productId,area:areas,check:checked};
		var num = $("#right_content_check input[name='n']").val();
		var str = prodId +"="+ num;
	
		//发送数据
		$.post(address,str,function(data){

			//返回值判断有没有成功
			if(data == 1) {
				alert("加入购物车失败");
				return false;
			}

			window.location.href = addressIndex;
			
		})
	})


	//配件组合选择加入购物车
	$("#tuiJian").click(function(){

		var prodIdNum = {};

		$("#peijian input[name='check']").each(function(index,val) {
		
			if($(val).attr('checked') == 'checked') {
			
				prodIdNum[$(val).attr('prodId')] = 1;

			}
		});
		
		prodIdNum[$(".tjspxq li").attr('prodId')] = 1;

		$.post(address,prodIdNum,function(data) {
			if(data == 1) {
				alert('添加失败，请重新加入');
				return false;
			}else{
				window.location.href = addressIndex;
			}
		});

	})


	//人气组合选择加入购物车
	$("#renqi").click(function(){

		var prodIdNum = {};

		$("#renqizuhe input[name='check']").each(function(index,val) {
	
			if($(val).attr('checked') == 'checked') {
			
				prodIdNum[$(val).attr('prodId')] = 1;

			}
		});
		
		prodIdNum[$(".tjspxq1 li").attr('prodId')] = 1;

		$.post(address,prodIdNum,function(data) {
		
			if(data == 1) {
				alert('添加失败，请重新加入');
				return false;
			}else{
				window.location.href = addressIndex;
			}
		});

	})
	

	//推荐已选配件数量
	$("#peijian input[name='check']").click(function(){
			
		var Pnum = 0;
		$("#peijian input[name='check']").each(function(index,val) {
			
			if($(val).attr('checked') == 'checked') {
			
				Pnum++;

			}
		});

		$("#tuijianNum").html("已选择"+Pnum+"个配件");
		
	});


	//人气已选配件数量
	$("#renqizuhe input[name='check']").click(function(){
		
		var Rnum = 0;
		$("#renqizuhe input[name='check']").each(function(index,val) {
			
			if($(val).attr('checked') == 'checked') {
			
				Rnum++;

			}
		});

		$("#renqiNum").html("已选择"+Rnum+"个配件");

	});


	/*游戏本排行*/
	$(".t_daohang ul li").bind('mouseover',function(){
		/*$(this).unbind('mouseover');*/
		var $this = $(this);
		var $index = $(this).index();
		if($index == 0){
			$this.siblings().removeClass('xz');
			$this.addClass('xz');
			$('.shangpin2').hide();
			$('.shangpin3').hide();
			$('.shangpin1').show();
		}else if($index == 1){
			$this.siblings().removeClass('xz');
			$this.addClass('xz');
			$('.shangpin1').hide();
			$('.shangpin3').hide();
			$('.shangpin2').show();
		}else if($index == 2){
			$this.siblings().removeClass('xz');
			$this.addClass('xz');
			$('.shangpin1').hide();
			$('.shangpin2').hide();
			$('.shangpin3').show();
		}
	});
	/*推荐搭配*/
	$('.tjpj .bt ul li').bind('click',function(){
		var $this = $(this);
		var $index = $this.index();
		if($index == 0){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.tjspxq1').hide();
			$('.dh').show();
			$('.tjspxq').show();
		}else if($index == 1){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.tjspxq').hide();
			$('.dh').hide();
			$('.tjspxq1').show();
		}
	});
	/*选中的配件*/
	var $money = 0;
	var $t_price = 0;
	var $price = 0;
	var $price1 = 0;
	var $t_price1 = 0;
	$(".goods2 :input").live('click',function(){
		var $this = $(this);
		/*console.log($this.attr("checked"));*/
		/*判断配件是否选中*/
		if($this.attr("checked") == "checked"){
			$money = $this.next().html();
		}else{
			$money = -$this.next().html();
		}
		$n_money = parseFloat($money);
		$price = $(".t_price span span").html();
		$price1 = $(".t_price del span").html();
		$t_price = parseFloat($price) + parseFloat($money);
		$t_price1 = parseFloat($price1) + parseFloat($money);
		$(".t_price span span").html($t_price.toFixed(2));
		$(".t_price del span").html($t_price1.toFixed(2));
	});
	/*商品介绍及各种参数*/
	$(".spjs .bt ul li").bind('click',function(){
		var $this = $(this);
		var $index = $this.index();
		console.log($index);
		if($index == 0){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.goods_messages').show();
			$('.pics').show();
			$('.fwcr').show();
			$('.qlsm').show();
			$(".goods_messages").siblings().show();
			$("#canshu").hide();
			$("#bzqd").hide();
			$("#shfw").hide();
		}else if($index == 1){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.goods_messages').hide();
			$('.pics').hide();
			$("#bzqd").hide();
			$("#shfw").hide();
			$("#canshu").show();
			$('.fwcr').show();
			$('.qlsm').show();
			$('.goods_messages').siblings().show();
		}else if($index == 2){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.pics').hide();
			$("#bzqd").show();
			$("#shfw").hide();
			$("#canshu").hide();
			$('.goods_messages').hide();
			$('.pics').siblings().show();
			$("#shfw").hide();
			$("#canshu").hide();
			
		}else if($index == 3){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.spzp').siblings().show();
			/*$('.spzp').show();*/
			$('.goods_messages').hide();
			$('.pics').hide();
			$('.fwcr').hide();
			$('.qlsm').hide();
			$("#bzqd").hide();
			$("#shfw").hide();
			$("#canshu").hide();
		}else if($index == 4){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$("#shfw").show();
			$('.fwcr').show();
			$('.goods_messages').siblings().show();
			$("#bzqd").hide();
			$("#canshu").hide();
			$('.goods_messages').hide();
			$('.pics').hide();
		}
	});
	/*回复板块*/
	$('.hf ul .c_hf').toggle(function(){
		$(this).parent().nextAll(".callback1").show();
	},function(){
		$(this).parent().nextAll(".callback1").hide();
	});
	$(".yhhfnr ul li").hover(function(){
		var $this = $(this);
		$this.find(".hfsj span").show().toggle(function(){
			$this.find(".callback").show();
		},function(){
			$this.find(".callback").hide();
		})
	},function(){
		$(this).find(".hfsj span").hide();
	});
	$(".plxq .bt ul li").click(function(){
		var $this = $(this);
		var $index = $this.index();
		if($index == 0){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.p_content2').hide();
			$('.p_content3').hide();
			$('.p_content4').hide();
			$('.p_content1').show();
		}
		if($index == 1){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.p_content1').hide();
			$('.p_content3').hide();
			$('.p_content4').hide();
			$('.p_content2').show();
		}
		if($index == 2){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.p_content2').hide();
			$('.p_content1').hide();
			$('.p_content4').hide();
			$('.p_content3').show();
		}
		if($index == 3){
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
			$('.p_content2').hide();
			$('.p_content3').hide();
			$('.p_content1').hide();
			$('.p_content4').show();
			$this.siblings().removeClass('title1');
			$this.siblings().addClass('title2');
			$this.addClass('title1');
		}
	});
	//alert(window.location.pathname);//设置或获取对象指定的文件名或路径
	//alert(window.location.href);//设置或获取整个URL为字符串
	//alert(window.location.port);//设置或获取与URL关联的端口号
	//alert(window.location.protocol);//设置或获取URL的协议部分
	//alert(window.location.hash);//设置或获取href属性在井号"#"后面的分段
	//alert(window.location.host);//设置或获取location或URL的hostname和Port号
	//alert(window.location.search);//设置或获取href属性中跟在问好后面的部分
	/*增加有用数量*/
	$('.hf ul .youyong').bind('click',function(){
		//alert('aaaaaaa');
		console.log($(this).find('span').html());
		var number = parseInt($(this).find('span').html()) + 1;
		$(this).find('span').html(number);
	})
})
/*$(function(){
	$('.pro1').click(function(){
			var $str = $(this).html();
		});
})*/
 function demo(name){
 //	alert('aaaaaaa');
 	var proTypeId = $(name).attr('typeid');
 	//console.log(proTypeId);
 	 var url=window.location.pathname;
 	 var reg=/.*php/;
	 url=url.match(reg);
	 url=url+'/Details/details/id/';
	 console.log(url);
 	var typeId={typeId:proTypeId};
 	$.post(fenlei,typeId,function(data){
 		// data = JSON.parse(data);
 		data = eval('('+data+')');
 		//console.log(data);
 		var str = '';
 		for(var i=0;i<data.length;i++){
 			str += '<li class="jia">&nbsp;+&nbsp;</li><li class="goods1"><div class="mimg"><a href="'+url+data[i].prodId+'"><img class="tidai" src="'+imgurl+data[i].img+'" width="100px" height="100px"></a></div><div class="descr"><a href="'+data[i].prodId+'">'+data[i].prodName+'</a></div><div class="price"><input type="checkbox" name="check" value="1">￥<span>'+data[i].price2+'</span></div><div class="m_goods"><a href="#">更多'+data[i].proName+'</a></div></li>';
 		}
 		$('#peijian').html(str);
 	})
}
function accessories(name){
	//alert('aaaaaa');
	var prodId = $(name).attr('prodId');
	//console.log(prodId);
	var url=window.location.pathname;
 	 var reg=/.*php/;
	 url=url.match(reg);
	 url=url+'/Details/details/id/';
	var prod = {prod:prodId};
	$.post(peijian,prod,function(data){
		data = eval('('+data+')');
		//console.log(data.length);
 		var str = '';
		for(var i=0;i<data.length;i++){
			for(var j=0;j<data[i].length;j++){
				//console.log(data[i][j]);
				//console.log(i);
				str += '<li class="jia">&nbsp;+&nbsp;</li><li class="goods1"><div class="mimg"><a href="'+data[i][j].prodId+'"><img class="tidai" src="'+imgurl+data[i][j].img+'" width="100px" height="100px"></a></div><div class="descr"><a href="'+data[i][j].prodId+'">'+data[i][j].prodName+'</a></div><div class="price"><input type="checkbox" name="check" value="1">￥<span>'+data[i][j].price2+'</span></div><div class="m_goods"><a href="#">更多'+data[i][j].proName+'</a></div></li>';
			}
		}
 		$('#peijian').html(str);
	});
}


$(function(){
	$('.submit-button').live('click',function(){

		//alert('aaaaaaaaaaaaaa');
		var $this=$(this);
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
			url=url+'/Compare/careProds';
		var uname=$('.inputbox input[name="uname"]').val();
		//console.log($('input[name=uname]').eq(1).val());
		//console.log(uname);
		var upass=$('input[name=upass]').val();
		//console.log($('input[name=upass]').eq(1).val());
		//alert(upass);
		if(uname.length<4){
			$("#errorinfo").prev().children().val('用户名错误');
			console.log($("#errorinfo").prev().children().val());
			return
		}
		if(upass.length<=5){
			$("#errorinfo").prev().children().val('密码错误');
			return
		}
		var data=$(this).parents('form').serialize();
		$.ajax({
			url:url,
			data:data,
			type:'post',
			success:function(rdata){
				console.log(rdata);
				if(rdata==0){
					window.location.reload();
				}else if(rdata==1){
					$("#errorinfo").prev().children().val('密码错误');
				}else if(rdata==2){
					$("#errorinfo").prev().children().val('用户名不存在');
				}else{
					$("#errorinfo").prev().children().val('邮箱错误');
				}
			}
		});
	});
	/*再次输入时清除错误提示*/

	$('#care').live('click',function(e){

		var $this=$(this);
		var offset=$this.offset();
		var top=offset.top;
		//console.log($('.thickbox'),'bbbbbbbbbbbbbbb');
		var thickbox=$('.thickbox').offset();
		var thickTop=thickbox.top;
			thickTop=thickTop+(top-800)/2;
		if(sessionExist==-1){
			/*用户没有登录时，弹出登录窗口*/
			$('.thickbox').offset({top:thickTop});
			$('.thickdiv').show();		
			$('.thickbox').show();
		}else{
			/*存数据库session*/
			/*用户登录时需要做的，相关操作*/
			var $this=$(this);
			var url=window.location.pathname;
			var reg=/.*php/;
				url=url.match(reg);
				url=url+'/Compare/do_care';

			var data=$this.attr('name');
			var prodId={prodId:data};
			$.ajax({
				url:url,
				data:prodId,
				type:'post',
				success:function(rdata){
					console.log(rdata);
					if(rdata==0){
						$('.infotext h1').text('^_^关注成功');
					}else if(rdata==1){
						$('.infotext h1').text('~>_<~+已关注过，感谢支持');
					}else{
						$('.infotext h1').text('⊙﹏⊙‖∣关注失败');
					}
					$('.careBox').fadeIn('slow',function(){
					var $this=$(this);
					setTimeout(function(){
							$this.fadeOut('slow');
						},3);
					});
				}
			});
		}
	});
/*隐藏登录提示*/
	$('.boxtitle a').click(function(){
			$('.thickbox').offset({top:800});
			$('.thickbox').hide();
			$('.thickdiv').hide();
	});
	/*$("#duibi").click(function(){
		//隐藏对比栏
		$("#pop-compare").attr("style","");

		//设置属性
		$("#success-cart").attr("style","display:block;bottom:0px");

		//获取prodId
		var pid = $(this).attr("prodId");

	
	});*/
	//对比栏,
	var compareNum=compareListNum;
	$("#duibi").click(function(){
		
		//添加背景色属性和显示
		var $othis=$(this);
		var id=$(this).attr('name');
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
		if(!$othis.hasClass('btn-compare-s-active')){

			$("#pop-compare").attr("style","display:block;bottom:0px");

			if(compareNum>=4){
				return;
			}

			$othis.addClass('btn-compare-s-active');
			
			//购物栏隐藏
			url=url+'/Compare/compare';
			var data={prodId:id};
			$.ajax({
				url:url,
				data:data,
				type:'get',
				success:function(rdata){
					console.log(rdata);
					if(rdata!=1){
						var $this=$('#diff-items').children("dl[class='item-empty']").first();
							$this.children('dt').empty().append(rdata['str1']);
							$this.children('dd').empty().append(rdata['str2']);
							$this.removeAttr('class');
							$this.attr('id','cmp_item_'+rdata['prodId']);
						var $pthis=$('#diff-items').children("dl[class='item-empty']");
						//console.log($pthis.size());
						if($pthis.size()<=2){
							$('#goto-contrast').css({backgroundColor:'#E4393C',color:'#fff'});
						}else{
							$('#goto-contrast').css({backgroundColor:'#ddd',color:'#ccc'});
						}
						compareNum++;
					}
				},
				dataType:'json'
			});
	}else{
		//清除选择背景色属性
			//$othis.addClass('btn-compare-s-active');
			$othis.removeClass('btn-compare-s-active');
			url=url+'/Compare/removeCompare';
			var data={prodId:id};
			$.ajax({
				url:url,
				data:data,
				type:'get',
				success:function(rdata){
					console.log(rdata);
					del(rdata);
				}
			});
		}
	});
/*删除对比时的效果处理*/
	function del(rdata){
		if(rdata>0){
				var $this=$('#diff-items').children("dl[id='cmp_item_"+rdata+"']");
				var status=$this.next().attr('class');
				if(status=='item-empty'){
					var index=$this.index();
						$this.children('dt').empty().append(index+1);
						$this.children('dd').empty().append('您还可以继续添加');
						$this.removeAttr('id');
						$this.attr('class','item-empty');
				}else{
					$this.remove();
					var $ethis=$('#diff-items').children("dl[class='item-empty']").first();
					var num=$('#diff-items').children("dl[class='item-empty']").size();
					console.log(num);
					var index=$ethis.index();
					//console.log(index);
					if(index==-1){
						$('#diff-items').append('<dl class="item-empty"><dt>4</dt><dd>您还可以继续添加</dd></dl>');
					}else{
						$ethis.before('<dl class="item-empty"><dt>'+(4-num)+'</dt><dd>您还可以继续添加</dd></dl>');
					}
				}
				var $pthis=$('#diff-items').children("dl[class='item-empty']");
				//console.log($pthis.size());
				if($pthis.size()<=2){
					$('#goto-contrast').css({backgroundColor:'#E4393C',color:'#fff'});
				}else{
					$('#goto-contrast').css({backgroundColor:'#ddd',color:'#ccc'});
				}
				compareNum--;
			}else{
				return -1;
			}
	}
	$("#pop-compare .hide-me").click(function(){
		$("#pop-compare").hide();
	});
	/*清空所有对比*/
	$('.del-items').click(function(){
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
			url=url+'/Compare/removeCompare';
		var data={prodId:'all'};
		$.ajax({
			url:url,
			data:data,
			type:'get',
			success:function(rdata){
				console.log(rdata);
				if(rdata==0){
					$('#diff-items dl').not('.item-empty').each(function(i){
						$(this).children('dt').empty().append(i+1);
						$(this).children('dd').empty().append('您还可以继续添加');
						$(this).removeAttr('id');
						$(this).attr('class','item-empty');
					});
					$('#duibi').removeClass('btn-compare-s-active');
					$('#goto-contrast').css({backgroundColor:'#ddd',color:'#ccc'});
					compareNum=0;
				}
			}
		});
	});
	$('.del-comp-item').live('click',
		function(){
			var url=window.location.pathname;
			var reg=/.*php/;
				url=url.match(reg);
				url=url+'/Compare/removeCompare';
			var prodId=$(this).parents('dl').attr('id');
			var id=prodId;
				prodId=parseInt(prodId.substr(9));
			var data={prodId:prodId};
			$.ajax({
				url:url,
				data:data,
				type:'get',
				success:function(rdata){
					$delStatus=del(rdata);
					if($delStatus!=-1){
						$('#duibi').removeClass('btn-compare-s-active');
					}
				}
			});
	});
	/*当对比数小于2时，禁止对比跳转*/
	$('#goto-contrast').click(function(){
		if(compareNum<2){
			return false;
		}
	});
	$('#diff-items dl').live('mouseover',
		function(){
			$(this).find('.del-comp-item').css('visibility','visible');
			//alert($(this).hasClass('.del-comp-item').text());
		}
	);

	
});









