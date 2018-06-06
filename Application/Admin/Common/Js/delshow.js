function show_confirm(val,con,fun) {
    var r = confirm('是否删除?');
    if (r == true) {
        location.href = app +'/'+con+'/'+fun+"?id="+val+"&status=2";
    }
}