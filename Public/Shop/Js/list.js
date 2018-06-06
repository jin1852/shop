$(function(){

	//左边点击事件
	$(".item").click(function(){
		$(this).toggleClass('current1');
	});

	//左边时钟
	var d = Date.parse("2014,10,15");
	var h = 0;
	var m = 0;
	var s = 0;
	var t = setInterval(function(){
		//实例化日期
		var tx = new Date();
		//获取当前时间戳
		var th = tx.getTime();
		//设定有效时间戳
		var a = (d-th)/1000;
		//计算时分秒
		 h = parseInt(a/(60*60));
		 m = parseInt((a%(60*60))/60);
		 s = parseInt(((a%(60*60))%60));
		 $('#clock').html('	剩余<b>'+h+'</b>小时<b>'+m+'</b>分<b>'+s+'</b>秒');
		 if(h==0 && m ==0 & s == 0){
		 	clearInterval(t);
		 }
	},1000);

	//左边销售移入移除
	$('#weekRank li').mouseover(function(){
		$('#weekRank li').addClass('fore1');
		$(this).removeClass('fore1');
	}).mouseout(function(){
		$(this).addClass('fore1');
		$('#weekRank li:eq(0)').removeClass('fore1');
	});

	//右边品牌搜索更多
	$("#select span[option='more']").click(function(){
		$(this).addClass('hide');
		$("#select span[option='less']").removeClass('hide').attr('fold','display:inline');
		$('#select .v-tabs ul').removeClass('hide');
		$('#select div.tabcon').addClass('tacon-multi');
	});
	$("#select span[option='less']").click(function(){
		$(this).addClass('hide').attr('fold','');
		$("#select span[option='more']").removeClass('hide');
		$('#select .v-tabs ul').addClass('hide');
		$('#select div.tabcon').removeClass('tacon-multi');
	});

	$('.p_buy').click(function(){
		var prodId=$(this).attr('prodId');
		var id=prodId+"=1";
		$.post(address,id,function(data){
			//处理返回的数据
			var data = data;
			if(data == 1) {
				alert('嘿，看到我了，就刷新下页面');
			}else{
				window.location.href='/Jd/index.php/Cart/index';
			}
		});
	});


	//点击展示购物栏
	$("#plist a.scart").click(function(){

		//隐藏对比栏
		$("#pop-compare").attr("style","");

		//设置属性
		$("#success-cart").attr("style","display:block;bottom:0px");

		//获取prodId
		var pid = $(this).attr("prodId");

		//拼接成字符串格式，第一个参数为商品Id，第二个为购买数量
		var id = pid+"=1";
		//alert(id);
		//console.log(id);
		//地址在list页面定义的，发送数据到购物车
		$.post(address,id,function(data){

			//处理返回的数据
			var data = data;
			
			if(data == 1) {
				alert('嘿，看到我了，就刷新下页面');
			}
	
		});

	});

	//点击隐藏
	$("#success-cart a.hide-me,#success-cart a.btn-continue").click(function(){

		//设置属性
		$("#success-cart").attr("style"," ");
	});
	//对比栏,
	var compareNum=compareListNum;
	$("#plist a.btn-compare-s").click(function(){
		

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
			$("#success-cart").attr("style"," ");	
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
					$('#plist a.btn-compare-s').removeClass('btn-compare-s-active');
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
						$('a[prodid="a_'+prodId+'"]').removeClass('btn-compare-s-active');
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
	/*显示删除*/
	$('#diff-items dl').live('mouseout',
		function(){
			$(this).find('.del-comp-item').css('visibility','hidden');
		}
	);
	$("#pop-compare .hide-me").click(function(){
		$("#pop-compare").hide();
	});
	$('.submit-button').click(function(){
		var $this=$(this);
		var url=window.location.pathname;
		var reg=/.*php/;
			url=url.match(reg);
			url=url+'/Compare/careProds';
		var uname=$('input[name=uname]').val();
		var upass=$('input[name=upass]').val();
		if(uname.length<=3){
			$("#errorinfo").prev().children().val('用户名错误');
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
	$('.item input').focus(function(){
		$("#errorinfo").prev().children().val('');
	});
	$('.btn-coll').live('click',function(e){
		var $this=$(this);
		var offset=$this.offset();
		var top=offset.top;
		var thickTop=($('.thickbox').offset()).top;
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

			var data=$this.next().attr('name');
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
})