$(function(){
	$("th[name=add]").removeClass("sorting");
	$('th[name=add]').unbind('click');
	$("div .mws-button-row").last().remove();
});