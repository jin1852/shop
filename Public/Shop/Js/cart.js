$(function(){
	
	//全选，全不选  toggle这个他自身不选

	$("#toggle-checkboxes,#toggle-checkboxes_down").click(function(){

		var chickChecked = $(this).attr('checked');

		//获取当前对象checked值
	 	checked = $(this).attr('checked');

	 	//判断值
	 	if(checked){

	 		//等于true
	 		$(":checkbox").attr('checked',true);
	 		
	 	}else{

	 		//等于false
	 		$(":checkbox").attr('checked',false);
	 	}

	});


	//单选
	$("input[name='boxs']").click(function() {
		var boxsLength = $("input[name='boxs']").length;
		var checkedValue = 0;
		//alert($("input[name='boxs']").index(0).attr('checked'));
		for(var i=0;i<boxsLength;i++) {
			var aa = $("input[name='boxs']").eq(i).attr('checked');
			if(aa) {
			
				checkedValue++;
			}
			
		}
		
		if(checkedValue <boxsLength) {
			$("#toggle-checkboxes,#toggle-checkboxes_down").attr('checked',false);
		}else{
			$("#toggle-checkboxes,#toggle-checkboxes_down").attr('checked',true);
		}
	});



	//定义数组存放要删除购物车商品的Id
	var cartProdId = {};

	//单个删除
	$('.cart-remove').click(function(){

		//弹出删除框
		$('#deleteBackground').removeClass('hide');
		$(".thickbox").attr("style","left: 499.5px; top: 200px; width: 342px; height: 153px;").removeClass('hide');
		$('#deleteCartGoods').removeClass('hide');
		$('.ftx04').html("确定从购物车中删除选中商品？");

		cartProdId[$(this).attr('prodId')] = $(this).attr('prodId');
		
	});


	//多选删除
	$("#remove-batch").click(function(){

		//判断选中的商品
		$(".p-checkbox input").each(function(i) {
		
			var checkedStatus = $(this).attr('checked');

			//选中把id值放入数组中
			if(checkedStatus == "checked"){

				cartProdId[$(this).val()] = $(this).val();
			}

		});	


		var i = 0; //计数
		$.each(cartProdId, function(index, json){
			
			i++;

		})

		if(i <= 0) {

			alert("至少要选一个");

			//隐藏删除框
			$('#deleteBackground').addClass('hide');
			$(".thickbox").addClass('hide');
			$('#deleteCartGoods').addClass('hide');
			return false;
		}

		//弹出删除框
		$('#deleteBackground').removeClass('hide');
		$(".thickbox").attr("style","left: 499.5px; top: 200px; width: 342px; height: 153px;").removeClass('hide');
		$('#deleteCartGoods').removeClass('hide');
		$('.ftx04').html("确定从购物车中删除所有选中商品？");

		
	});


	
	//点击确定删除
	$("#btnRemoveConfirm").click(function() {

		//发送
		$.post("delCartGood",cartProdId,function(data) {

			if(data == 2) {

				alert('删除商品失败，请刷新');

			}else{


				$.each(cartProdId,function(i,val) {

					$("#prodId"+val).remove();
				})
				actions();


				//隐藏删除框
				$('#deleteBackground').addClass('hide');
				$(".thickbox").addClass('hide');
				$('#deleteCartGoods').addClass('hide');

				//清空
				cartProdId = {};

			}

		});



		

	});


	//取消删除
	$("#cancelRemoveConfirm,.thickclose").click(function(){

		//隐藏删除框
		$('#deleteBackground').addClass('hide');
		$(".thickbox").addClass('hide');
		$('#deleteCartGoods').addClass('hide');
		$("#loginTest").addClass('hide');;
		cartProdId = {};
	});

/*
	//选择地区
	$("#selectP,bgimages").click(function() {

		//获取地区父Id
		var pId = $("#selectP").attr('pId');

		//请求服务器
		$.post('getRegion',{pId:1},function(data){

			if(data){
				
				for(var i in data){
					if(i == 0 ) {

						continue;
						
					}else {

						$("#selectP").append("<option value="+data[i]['areaid']+">"+data[i]['diqu']+"</option>");
					}
				}
			}
		});

	});
*/

	//定义变量
	var tsum = 0;	//所有购买数量初始化
	var tprice = 0; //所以总价初始化
	var prodId = 0; //id
	var price2 = 0; //单价
	var num = 0; //数量

	//调用函数
	actions();

	//单击触发事件
	$('.subtract').click(function(){
		
		//取触发对象的值
		var act = $(this).children().html();
		var data = $(this).siblings('.quantity-text').val();
		

		//根据值判断执行加和减
		if(act == '-') {

			//减值
			if(data > 1){
				data--;
			}

		}else{

			//加值
			data++;

		}

		//把运算后值写回去
		$(this).siblings('.quantity-text').val(data);

		//调用函数
		actions();

	});


	

	//定义计算函数,得到总数，总价，各个对象数量，单价
	function actions() {
		tsum = 0;	//所有购买数量初始化
		tprice = 0; //所以总价初始化

		//获取购物车商品个数
		var len = $(".preferential").length;

		//遍历
		for(var i=0;i<len;i++) {

			//获取价格，数量
			price2 = $('.preferential').eq(i).find("span").html();
			num = $('.quantity-form').eq(i).find("input").val();

			//求总价和总数
			tsum += Number(num);
			tprice += Number(num)*Number(price2);

		}
	
		//写入总价和总数
		$("#totalSkuPrice").html("￥"+tprice);
		$("#selectedCount").html(tsum);
		$("#finalPrice").html("￥"+tprice);

	}


	//单击触发结算
	$("#cartMessage").click(function() {

		//序列化
		var str = $(".quantity-text").serialize();
		if(str) {
			
			str = str +'&tsum='+ tsum +'&tprice='+ tprice;
		
			//把购物车信息发送到订单信息页
			$.post('addCart',str,function(data){
				
				//处理返回的数据
				var data = data;

				if(data == 1){

					alert('网络延迟，请重新提交');
					return false;

				}else{

					$.post('orderInfo',function(data){
				
						//检测有没有登陆
						if(data == 2) {

							//弹出登陆窗口
							$("#deleteBackground").removeClass('hide');
							$(".thickbox").attr("style","left: 464.5px; top: 80px; width: 412px; height:  460px;").removeClass('hide');
							$("#loginTest").removeClass('hide');
							return false;

						}else{
							
							window.location.href='orderInfo';
						} 

					});
				}

			});

		}else{

			alert('没有商品，请添加商品');
		}

	});


	

	//登陆用户样式
	$("#loginname").focus(function(){

		$(this).addClass('highlight1');
		$("#loginname_error").addClass('hide');

	}).blur(function(){

		var userMessage = $(this).val();
		
		$("#loginname_succeed").removeClass('invisible');
		if(!userMessage) {

			$("#loginname_succeed").html(imagesError);

		}else{

			$("#loginname_succeed").html(imagesSuccess);		

		}
		
	});


	//登陆密码框样式
	$("#nloginpwd").focus(function(){

		$(this).addClass('highlight1');
		$("#loginpwd_error").addClass('hide');

	}).blur(function(){

		var pwsMessage = $(this).val();
		
		$("#loginpwd_succeed").removeClass('invisible');
		if(!pwsMessage) {

			$("#loginpwd_succeed").html(imagesError);
			$("#loginpwd").val(pwsMessage)
		}else{

			$("#loginpwd_succeed").html(imagesSuccess);
	
		}
		
	});
	


	//登陆验证码样式
	$("#authcode").focus(function(){

		$(this).addClass('highlight1');
		$("#authcode_error").addClass('hide');

	}).blur(function(){

		$(this).attr('class','text');
		var codeMessage = $(this).val();		
		$("#authcode_succeed").removeClass('invisible');

		if(!codeMessage) {

			$("#authcode_succeed").html(imagesError);

		}else{

			$("#authcode_succeed").html(imagesSuccess);
			
		}
		
	});



	//验证码刷新
	var srcVal = $("#JD_Verification1").attr('src')
	$("#nextCode").click(function(){	

		$("#JD_Verification1").attr('src',srcVal+'?'+Math.random())

	});


	//点击登陆
	$("#loginsubmitframe").click(function(){
		
		//定义一个json格式
		var loginInfo = {};
		
		//获取用户名
		loginInfo['uname'] = $.trim($("#loginname").val());

		if(!loginInfo['uname']){

			$("#loginname_error").removeClass('hide').html("用户名不能为空");
			$("#loginname_error").addClass('error');
			return false;
		}

		//获取密码
		loginInfo['upwd'] = $.trim($("#nloginpwd").val());

		if(!loginInfo['upwd']){

			$("#loginpwd_error").removeClass('hide').html("密码不能为空");
			$("#loginpwd_error").addClass('error');
			return false;
		}

		//验证码
		loginInfo['code'] = $.trim($("#authcode").val());

		if(!loginInfo['code']){
			
			$("#authcode_error").removeClass('hide').html("验证码不能为空");
			$("#authcode_error").addClass('error');
			return false;
		}


		//发送数据
		$.post("/jd/index.php/Login/doLogin",loginInfo,function(data){

			//验证通过
			if(data == 0) {

				window.location.href = "orderInfo";

			}

			//用户名不对
			if(data == 1) {

				$("#loginname_error").removeClass('hide');
				$(".fore2 > span").addClass('hidden');
				$("#loginname_error").addClass('error').html("要想过，信息要填对");
			}

			if(data == 2) {

				$("#loginpwd_error").removeClass('hide');
				$(".fore2 > span").addClass('hidden');
				$("#loginpwd_error").addClass('error').html("密码不输入对,别想走");
			}

			if(data == 3) {

				$("#authcode_error").removeClass('hide').html("验证码不正确");
				$("#authcode_error").addClass('error');
			}

		});

	});

	
	//购买同样商品的展示
	$("#demo2 li a.btn").live('click',function() {
		
		var prod = $(this).attr('prodId');
		
		$.post('addCart',prod+'=1',function(data) {

			if(data == 1) {
				alert('请刷新');
				return false;
			}else{

				window.location.href='index';
			}
		});
	});


	//图片滚动
	$("#demo2").append($("#demo2").html());

	var speed=30;
	function Marquee() {
		
		if($("#demo1").scrollLeft() <=0) {
			$("#demo1").scrollLeft(878);
		}else{
			$("#demo1").scrollLeft($("#demo1").scrollLeft()-1);
		}
		
	}

	var MyMar=setInterval(Marquee,speed) 
    $("#demo1").mouseover(function() {
        clearInterval(MyMar);
    } )
    $("#demo1").mouseout(function() {
        MyMar=setInterval(Marquee,speed);
    } )

})