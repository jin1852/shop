$(function(){
	//点击新地址
	$("#consignee_radio_new").click(function() {

		//显示表单
		$("#consignee-form").removeClass('hide');
		$("#use-new-address").addClass('item-selected');
		$("#consignee-list > div").removeClass('item-selected');
		$(".item-action").attr('style','display:none'); 

	});




	//点击默认地址
	$("#consignee_radio").click(function() {

		//隐藏表单
		$("#consignee-form").addClass('hide');
		$("#consignee-list > div,#consignee-list >span").addClass('item-selected').removeClass('hide'); 
		$("#use-new-address").removeClass('item-selected');

	});


	//在默认地址上移入移出
	$("#consignee-list").mouseover(function() {

		//移入样式
		$("#consignee-list > div").addClass('item-selected');
		$(".item-action").attr('style','display:inline');

	}).mouseout(function() {

		//移出样式
		$("#consignee-list > div").removeClass('item-selected');
		$(".item-action").attr('style','display:none');

	});


	//修改默认地址
	$("#edit-address").click(function() {

		//放值
		//收货人
		$("#consignee_name").val($("input[name='hidden-receiver']").val()); 
		//省
		//$("#consignee_province").append("<option value='0'>"+$("input[name='hidden-province']").val()+"</option>");
		//市
		//$("#span_city").append("<option value='0'>"+$("input[name='hidden-city']").val()+"</option>");
		//区
		//$("#consignee_county").append("<option value='0'>"+$("input[name='hidden-county']").val()+"</option>");
		//地址详情
		$("#consignee_address").val($("input[name='hidden-address']").val());
		//地址别名
		$("#areaNameTxt").html($("input[name='hidden-addressbie']").val());
		//手机
		$("#consignee_mobile").val($("input[name='hidden-tel']").val());
		//固定电话
		$("#consignee_phone").val($("input[name='hidden-hometel']").val());
		//邮箱
		$("#consignee_email").val($("input[name='hidden-email']").val());

		//显示
		$("#consignee-form").removeClass('hide'); 



	});



	//删除收货人信息
	$("#del-address").click(function(){

		//获取地址id
		var addressId = $("input[name='hidden-aId']").val();
		
		$.post('delAddress',{addressId:addressId},function(data){

			if(data == 2) {

				alert('饭可以乱吃，数据不能乱删');
			}

			$("#consignee-list div.item").addClass('hide');
			
			//显示表单
			$("#consignee_radio_new").attr("checked",true);
			$("#consignee-form").removeClass('hide');
			$("#use-new-address").addClass('item-selected');
			$("#consignee-list > div").removeClass('item-selected');
			$(".item-action").attr('style','display:none'); 
		});

	});




	//修改地址
	$("#userAddress").click(function() {

		//收货人样式
		$("#step-1").addClass('step-current');
		$("#addressInfo-verify").removeClass('hide');
		$("#addressOkInfo").addClass('hide');
		$("#consignee-list div.item").addClass('item-selected');
		$("#use-new-address").removeClass('item-selected');
		$("#userAddress").html("<a id='saveConsigneeTitleMinDiv' style='color:#005EA7;' href='#'>保存收货人信息</a>");
		$("#consignee-form").addClass('hide');

		$("#consignee-list div.item").removeClass('hide');

		//支付方式样式
		$("#payment-ship_edit_action").html("<font color='#B0B0B0'>如需修改，请先保存收货人信息</font>");

		//订单提交样式
		$("#submit_check_info_message").removeClass('hide');
		$("#order-submit").addClass('checkout-submit-disabled');
		$("#submit_check_info_message > span").html("您需先保存<a href='#consigneeFocus' style='color:#005EA7;'>收货人信息</a>，再提交订单");
	});




	var msg = '';
	//收货人验证
	$("#consignee_name").blur(function(){

		//失去焦点时获取值
		msg = $(this).val();

		if(msg == ''){
			
			//没值显示错误提示
			$("#name_div_error").html('请您填写收货人姓名');
			$("#name_div").addClass('message');consignee_name

		}else{
			
			//有值清空错误提示
			$("#name_div_error").empty();
			$("#name_div").removeClass('message');
		}
		
	});



	//详细地址验证
	$("#consignee_address").blur(function() {

		msg = $(this).val();
		if(msg == ''){

			$("#address_div_error").html('请您填写收货人详细地址');
			$("#address_div").addClass('message');

		}else{

			$("#address_div_error").empty();
			$("#address_div").removeClass('message');
		}

	});



	//手机验证
	$("#consignee_mobile").focus(function(){

		msg = $("#consignee_phone").val();

		if(msg) {

			$("#call_div_error").empty();
			$("#call_div").removeClass('message');

		}

	}).blur(function() {

		msg = $(this).val(); //获取当前对象值
		var re = /^1\d{10}$/;  //定义正则

		$("#consignee_phone").focus(function(){

			$("#call_div_error").empty();
			$("#call_div").removeClass('message');

		});

		if(msg && (re.test(msg))){

			//存在，清除样式
			$("#call_div_error").empty();
			$("#call_div").removeClass('message');

		}else{

			//值为空，提示错误
			$("#call_div_error").empty();
			$("#call_div_error").html('请您输入正确的手机号码');
			$("#call_div").addClass('message');
		
		}
	});


	//验证固定电话
	$("#consignee_phone").focus(function(){

	 	msg = $("#consignee_mobile").val();

		if(msg) {
			
			$("#call_div_error").empty();
			$("#call_div").removeClass('message');

		}

	}).blur(function() {

		msg = $(this).val(); //获取当前对象值
		var re = /^0\d{2,3}-?\d{7,8}$/; //定义正则
	
		if(msg && (re.test(msg))) {

			$("#call_div_error").empty();
			$("#call_div").removeClass('message');

		}else{

			//值为空，提示错误
			$("#call_div_error").empty();
			$("#call_div_error").html('请您输入正确的固定电话号码');
			$("#call_div").addClass('message');

		}
	});

	//邮箱验证
	$("#consignee_email").blur(function() {

		msg = $(this).val();
		re = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;

		if(msg) {

			if(!re.test(msg)) {

				$("#email_div_error").html('请您填写正确的邮箱');
				$("#email_div").addClass('message');
			}
		}

	});



	//省市区
	
	/*
	//单击加载省
	$("#consignee_province").click(function(){

		//获取省父id值
		var pId = $(this).attr('pId');

		//向服务器请求
		$.post('getRegion',{pId:pId},function(json) {

			$.each(json,function(i,v) {

				$("#consignee_province").append("<option diqu="+v['diqu']+" value='"+v['areaid']+"'>"+v['diqu']+"</option>");
			});
		});	

	});

	*/
	//改变省，获取市
	$("#consignee_province").change(function(){

		//获取市父id值
		var pId = $(this).val();

		//写到详细地址那
		var addr = $("#consignee_province").find("option:selected").html();
		
		$("#areaNameTxt").html(addr);

		//向服务器请求
		$.post('getRegion',{pId:pId},function(json) {

			//清空市select中的<option>项，不然查询多次的值都会保留
			$("#consignee_city > option:not(:first)").remove();

			$.each(json,function(i,v) {

				$("#consignee_city").append("<option diqu="+v['diqu']+" value='"+v['areaid']+"'>"+v['diqu']+"</option>");
			});
		});	

	});


	//改变市，获取区
	$("#consignee_city").change(function(){

		//获取市父id值
		var pId = $(this).val();

		//写到详细地址那
		var provinceText = $("#consignee_province").find("option:selected").html();
		var cityText = $("#consignee_city").find("option:selected").html();

		var addr = provinceText + cityText;
		$("#areaNameTxt").html(addr);

		//向服务器请求
		$.post('getRegion',{pId:pId},function(json) {

			//清空市select中的<option>项，不然查询多次的值都会保留
			$("#consignee_county > option:not(:first)").remove();

			$.each(json,function(i,v) {

				$("#consignee_county").append("<option diqu="+v['diqu']+" value='"+v['areaid']+"'>"+v['diqu']+"</option>");
			});
		});	

	});


	//改变区
	$("#consignee_county").change(function(){

		//获取市父id值
		var pId = $(this).val();
		
		//写到详细地址那
		var provinceText = $("#consignee_province").find("option:selected").html();
		var cityText = $("#consignee_city").find("option:selected").html();
		var countyText = $("#consignee_county").find("option:selected").html();
		var addr = provinceText + cityText +countyText;
		$("#areaNameTxt").html(addr);

		
	});



	//保存收货人信息
	$("#saveConsigneeTitleDiv").click(function(){

		var oldaddressStatus = $("#consignee_radio").attr('checked');
		var newaddressStatus = $("#consignee_radio_new").attr('checked');
		var formisHide = $("#consignee-form").hasClass('hide');

		//编辑后保存
		if((oldaddressStatus == 'checked') && !formisHide) {

			//调用提交函数
			addressAction();

		}else if((newaddressStatus == 'checked')){

			//调用提交函数
			addressAction();

		}else{

			//收货人样式
			$("#userAddress > a").html("<font color='#B0B0B0'>如需修改，请先保存支付及配送方式</font>");
			$("#addressInfo-verify").addClass('hide');
			$("#addressOkInfo").removeClass('hide');
			$("#step-1").removeClass('step-current');
			
			//支付方式样式
			$("#step-2").addClass('step-current');
			$("#payment-ok").addClass('hide');
			$("#payment-terms").removeClass('hide');
			$("#shipment").removeClass('hide');
			$(".save-consignee-payship").html("由于您更改了收货人信息，请重新设置并<a style='color:#005EA7;' href='#''>保存支付及配送方式</a>");

			//订单提交样式
			$("#submit_check_info_message > span").html("您需先保存<a href='#' style='color:#005EA7;'>支付及配送方式</a>，再提交订单");
		}

	});



	//定义数组接收值
	var paymentAndDelivery = {};
	paymentAndDelivery['payment'] = $("#pay-method-1").val();
	paymentAndDelivery['delivery'] = $("#jd-shipment").val();

	//选择支付方式
	$("input[name='payment']").click(function() {

		$("#payment-terms div.item").removeClass('item-selected');
		$(this).parents(".item").addClass('item-selected');
		paymentAndDelivery['payment'] = $(this).val();

	});

	//选择配送方式
	//京东快递
	$("#jd-shipment").click(function() {

		paymentAndDelivery['delivery'] = $(this).val();
		$("#pick-shipment-way-category").removeClass('way-category-selected');
		$("#jd-shipment-way-category").addClass('way-category-selected');
	});


	//上门自提
	$("#pick-shipment").click(function() {

		paymentAndDelivery['delivery'] = $(this).val();
		$("#jd-shipment-way-category").removeClass('way-category-selected');
		$("#pick-shipment-way-category").addClass('way-category-selected');
	});


	//点击修改支付配送方式改样式
	$("#payment-ship_edit_action").click(function() {

		//收货人样式
		$("#userAddress > a").html("<font color='#B0B0B0'>如需修改，请先保存支付及配送方式</font>");
		$("#addressInfo-verify").addClass('hide');
		$("#addressOkInfo").removeClass('hide');
		$("#step-1").removeClass('step-current');
		
		//支付方式样式
		$("#step-2").addClass('step-current');
		$("#payment-ok").addClass('hide');
		$("#payment-terms").removeClass('hide');
		$("#shipment").removeClass('hide');
		$("#payment-ship_edit_action").html("<a style='color:#005EA7;' href='#'>保存支付及配送方式</a>");

		//订单提交样式
		$("#submit_check_info_message > span").html("您需先保存<a href='#' style='color:#005EA7;'>支付及配送方式</a>，再提交订单");
		$("#order-submit").addClass('checkout-submit-disabled');
		$("#submit_check_info_message").removeClass('hide');

	});



	//保存支付配送信息
	$("#savePaymentDeliveryTitleDiv").click(function(){

		//获取新增地址Id
		paymentAndDelivery['addressId'] = $("#addressInfo-verify input[name='hidden-aId']").val();

		//确认发送到服务器
		$.post('editAddress',paymentAndDelivery,function(data) {
		
			//判断是否保存成功
			if(data == 2) {

				alert('亲，地址要填对哦');
				return false;

			}else{

				//支付方式样式
				$("#step-2").removeClass('step-current');
				$("#payment-ok").removeClass('hide');
				$("#payment-terms").addClass('hide');
				$("#shipment").addClass('hide');
				$("#payment-ship_edit_action").html("<a href='javascript:void(0)'>[修改]</a>");
				$(".save-consignee-payship").addClass('hide');

				//改变支付显示
				$("input[name='payment']").each(function(i){

					if(this.value == paymentAndDelivery['payment']){
						
						$(".payment-selected").html(this.getAttribute("payname"));

					}
				});


				//改变配送显示
				if(paymentAndDelivery['delivery'] == 0){
						
					$(".way-item").html("京东快递");
					
				}else{

					$(".way-item").html("上门自提");
				}
			


				//收货人信息样式
				$("#userAddress").html("<a href='javascript:void(0)'>[修改]</a>");

				//订单提交样式
				$("#submit_check_info_message > span").html("您需先保存<a href='#' style='color:#005EA7;'>支付及配送方式</a>，再提交订单");
				$("#order-submit").removeClass('checkout-submit-disabled');
				$("#submit_check_info_message").addClass('hide');
			} 

		});

	});


	//使用劵样式
	$(".order-coupon > div").toggle(function(){

		//清除全部样式
		$(".order-coupon >div").removeClass('toggle-active');
		$(".toggle-wrap").addClass('hide');

		//添加对象样式
		$(this).addClass('toggle-active')
		$(this).children(".toggle-wrap").removeClass('hide');

	},function(){

		$(this).removeClass('toggle-active')
		$(this).children(".toggle-wrap").addClass('hide');

	});



	//提交订单
	
	$("#order-submit").click(function() {

		//跳转到订单成功页
		if($(this).hasClass('checkout-submit-disabled')) {
			return false;
		}else{
			window.location.href ='orderOk';
		}
		

	});



	//确定信息函数
	function addressAction() {

		//定义一个数组存放表单值
		var formValues = {};
		 
		//循环表单所有项
		$("form :input").each(function(i){
			
			//把值存放数组
			formValues[this.id] = this.value;

		});	

		//判断表单值是否为空
		if((formValues['consignee_mobile'] == '') && (formValues['consignee_phone']) == '') {
			
			//值为空，提示错误
			$("#call_div_error").html('请您输入正确的号码');
			$("#call_div").addClass('message');
			return false;
	
		}else if((formValues['consignee_province'] == -1) || (formValues['consignee_city'] == -1)){

			//没值显示错误提示
			$("#area_div_error").html('请您填写地址');
			$("#area_div").addClass('message');
			return false;

		}else if(formValues['consignee_name'] == ''){

			//没值显示错误提示
			$("#name_div_error").html('请您填写收货人姓名');
			$("#name_div").addClass('message');
			return false;

		}else if(formValues['consignee_address'] == ''){

			//没值显示错误提示
			$("#address_div_error").html('请您填写收货人详细地址');
			$("#address_div").addClass('message');
			return false;
		}

		if(formValues['consignee_mobile']) {

			var len = formValues['consignee_mobile'].length;
			if(len < 11) {

				//值长度不为11位，提示错误
				$("#call_div_error").html('请您输入正确的号码');
				$("#call_div").addClass('message');
				return false;
			}

		}
		//把值改成服务器能识别的格式
		var addressInfo = {};

		addressInfo['addressId'] = $("#consignee-list input[name='hidden-addressId']").val();
		addressInfo['userId'] = $("#addressInfo-verify input[name='hidden-uId']").val();
		addressInfo['receiver'] = formValues['consignee_name'];
		addressInfo['provinceId'] = formValues['consignee_province'];
		addressInfo['cityId'] = formValues['consignee_city'];
		addressInfo['countyId'] = formValues['consignee_county'];
		addressInfo['address'] = formValues['consignee_address'];
		addressInfo['tel'] = formValues['consignee_mobile'];
		addressInfo['hometel'] = formValues['consignee_phone'];
		addressInfo['email'] = formValues['consignee_email'];
		addressInfo['addressbie'] = $("#areaNameTxt").html();
	
		//把修改的地址发送到服务器中
		$.post('editAddress',addressInfo,function(data){
	
			//判断是否修改成功	
			if(data == 'false') {

				alert('亲，地址要填对哦');
				return false;

			}else if(data == 'num'){

				alert('地址超过了3条不能再添加新地址了');
				return false;

			}else{

				//把新地址Id放入
				if(data > 0) {

					$("#addressInfo-verify input[name='hidden-aId']").val(data);
				}
				//把修改的值放入开头展示
				$("#addressOkInfo > p").empty()
					.append(addressInfo['receiver']+"&nbsp;&nbsp;"+addressInfo['tel']+"&nbsp;&nbsp;"+addressInfo['hometel']+"&nbsp;&nbsp;"+addressInfo['email']+"<br />"+addressInfo['addressbie']+"&nbsp;&nbsp;"+addressInfo['address']);

				//改变点击修改后的展示
		
				$("#consignee_radio").parent().empty().append('<input id="consignee_radio" class="hookbox" type="radio" checked="" value="" name="consignee_radio"><b>'+addressInfo['receiver']+'</b>'+addressInfo['addressbie']+addressInfo['address']+addressInfo['tel'])

				//收货人样式
				$("#userAddress > a").html("<font color='#B0B0B0'>如需修改，请先保存支付及配送方式</font>");
				$("#addressInfo-verify").addClass('hide');
				$("#addressOkInfo").removeClass('hide');
				$("#step-1").removeClass('step-current');
				
				//支付方式样式
				$("#step-2").addClass('step-current');
				$("#payment-ok").addClass('hide');
				$("#payment-terms").removeClass('hide');
				$("#shipment").removeClass('hide');
				$(".save-consignee-payship").html("由于您更改了收货人信息，请重新设置并<a style='color:#005EA7;' href='#''>保存支付及配送方式</a>");

				//订单提交样式
				$("#submit_check_info_message > span").html("您需先保存<a href='#' style='color:#005EA7;'>支付及配送方式</a>，再提交订单");
			}

		});
								
	}




})