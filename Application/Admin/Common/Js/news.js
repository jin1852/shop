$(function(){
	$("#select").change(function(){
		//console.log($("#select option:selected").val());
		var $proTypeId = $("#select option:selected").val();
		var proTypeId = {proTypeId:$proTypeId};
		$.get(path,proTypeId,function(data){
			var data = eval(data);
			//console.log(data[0]);
			//console.log(data.length);
			var str = null;
			var str1 = '<option value="0">--请选择品牌--</option>'
			for(var i = 0;i<data.length;i++){
				str += '<option value='+data[i].brandId+'>'+data[i].brandName+'</option>';
			}
			$("#brands").show().html(str1+str);
		});
	});
	$("#brands").change(function(){
		//console.log($("#brands option:selected").val());
		var $proTypeId = $("#select option:selected").val();
		//console.log($proTypeId);
		var $prodId = $("#brands option:selected").val();
		//console.log($prodId);
		var arr = {prodId:$prodId,proTypeId:$proTypeId};
		//var prodId = {prodId:$prodId,proTypeId:$proTypeId};
		$.get(path1,arr,function(data){
			var data = eval(data);
			 //console.log(data);
			//console.log(data.length);
			var str = null;
			var str1 = '<option value="0">--请选择商品--</option>'
			for(var i = 0;i<data.length;i++){
				str += '<option value='+data[i].prodId+'>'+data[i].prodName+'</option>';
			}
			$("#prods").show().html(str1+str);
		});
	});

	
})