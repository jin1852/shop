$(function(){
	//导航效果
	$('#top_con .item').hover(function(){
		$(this).addClass('hover');
	},function(){
		$(this).removeClass('hover');
	})

	$('#top_con .item .close').click(function(){
		$(this).parent().parent().removeClass('hover');
	})

	//幻灯片开始
	var iSlideNow = 0;
	var timer = null;
	var $lis = $('#top_con .top_con_center .slide ul>li');
	var $spans = $('#top_con .top_con_center .slide_control span');
	
	autoPlay();	//初始化调用执行
	function autoPlay(){
		timer = setInterval(function(){
			var $length = $lis.length;
			iSlideNow%=$length;
			run();
			iSlideNow++;
		},2000);
	}

	//播放动画的函数
	function run(){
		$lis.removeClass('selected');
		$spans.removeClass('selected');
		$($lis[iSlideNow]).addClass('selected');
		$($spans[iSlideNow]).addClass('selected');
	}

	$spans.hover(function(){
		clearInterval(timer);
		iSlideNow = $(this).index();
		console.log(iSlideNow);
		run();
	},function(){
		autoPlay();
	});

	$lis.hover(function(){
		clearInterval(timer);
	},function(){
		autoPlay();
	})
	//幻灯片结束

	//slide开始
	var $hotPrev = $('#top_con .hot_prev');
	var $hotNext = $('#top_con .hot_next');
	var $ul = $('#top_con .hot_slide ul');
	var $slideLis = $ul.children();
	var $ulWidth = $slideLis.eq(0).outerWidth(true) * $slideLis.length;
	//这里减1是因为css中设置的缘故
	var $containerWidth = $('#top_con .mscroll').width() -1;
	var iNow = 0;
	//动态的设置设置ul的宽度
	$ul.css('width',$ulWidth);
	//这里是可以滚动的最大次数(= 长度的比例 - 1)
	$total = Math.ceil($ul.width() / $containerWidth) - 1;
	$hotNext.click(function(){
		//需要先判断ul是否在运动中，如果不在的话就执行运动
		if(!($ul.is(':animated'))){
			iNow++;
		if(iNow > $total){
			$final = 0;
			iNow = 0;
		}else{
			$final = $ul.position().left - $containerWidth;
		}
			$ul.stop().animate({'left':$final});
		}		
	})

	$hotPrev.click(function(){
		if(!($ul.is(':animated'))){
			iNow--;
		if(iNow < 0){
			$final = - $total * $containerWidth;
			iNow = $total;
		}else{
			$final = $ul.position().left + $containerWidth;
		}
			$ul.stop().animate({'left':$final});
		}		
	})

	function listPlay($obj){
		var $titles = $obj.find('.p_list .smt');
		var $contents = $obj.find('.p_list .sm');
		var $arrow = $obj.find('.p_list .tab_arrow');
		
		$titles.each(function(i){
			//自定义属性，用于将tiltes 和contents对应起来
			this.index = i;
		})

		$titles.on('mouseover',function(){
			$contents.eq(this.index).addClass('curr').siblings().removeClass('curr');
			$arrow.animate({
				left:this.offsetLeft
			},'fast')
		})
	}

	listPlay($('#electronics'));
	listPlay($('#digitals'));
	listPlay($('#clothing'));
	listPlay($('#jewellery'));
	listPlay($('#life'));
	listPlay($('#boby'));
	listPlay($('#food'));
	listPlay($('#book'));

	$('.slide .slide_controls span').on('mouseover',function(){
		var $index = $(this).index();
		var $slideItem = $(this).parent().prev().find('.slide_items');
		var iWidth = $slideItem.children()[0].offsetWidth;
		$slideItem.animate({'left':-iWidth*$index});
		$(this).addClass('curr').siblings().removeClass('curr');
	})

	var $floors = $('#floor_list').children(); //找到内容主题部分的元素
	
	//点击btn到达相应的位置
	var $btns = $('#floorlist .backpanel-inner div');
	$btns.on('click',function(){
		var $index = $(this).index();
		var $active = $(this).children().eq(0);
		$height = $floors.eq($index).offset().top;
		$gap = $height - 100; // 计算需要滚动的距离
		$('html, body').animate({scrollTop:$gap},100);
		$btns.children().removeClass('curr');
		$active.addClass('curr');
	});

	//页面发生滚动的时候自动显示相应的模块
	var $goto = $('.goto');
	$(window).on("scroll",function(){
		var $scrollTop = $(document).scrollTop();
		var $str = ''; //存储对应的data-fid + floor_fore
		//console.log($scrollTop);
		$floors.each(function(){
			if($(this).offset().top - $(this).height() + 50 < $scrollTop){
				$str = "floor_fore" + $(this).attr('data-fid');
				
			}
		});

		if($str!="" && $btns.find('.curr').parent().attr('class') != $str){
			$btns.each(function(){
				if($(this).attr('class') == $str){
					$(this).children(0).addClass('curr');
				}else{
					$(this).children(0).removeClass('curr');
				}
			})
		}

		//滚动条拉上去的时候，去掉第一个的样式
		if($str==""){
			$btns.eq(0).children(0).removeClass('curr');
		}

		if($scrollTop > 200){	//滚动条出现的条件，需要拉动滚动条的距离大于200
			$goto.css('display','block');
		}else{
			$goto.css('display','none');
		}

	});


	//back_to_top效果
	$('#back_top').find('.back_to_top a').on('click',function(){
		//console.log('aaaa');
		$('html,body').animate({scrollTop:0},400);
	})
})