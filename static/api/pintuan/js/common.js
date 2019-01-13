	

    //根据AjAx获取内容
    function getContainer(url,page,sort,method,isappend)
	{
        if(page=="" || page==undefined)  page = 1;
        $.post(url,{page:page,sort:sort,sortmethod:method,isappend:isappend},function(data){
            if(data.status==1){
                if (parseInt(data.page.pagesize) > 0) {
                    template.config("escape", false);
                    var strData = template('loadContainer', data);
                    if(isappend==1){
                        $("#container").append(strData);
                    }else{
                        $("#container").html(strData);
                    }
                    if (data.page.hasNextPage == true) {
                        $("input[name='currentPage']").val( parseInt(page) + 1);
                    }
                    $('#container img').lazyload({
                        placeholder : "/static/images/no_pic.png",
                        effect : "fadeIn", //使用淡入特效
                        failure_limit : 10,  //容差范围
                        skip_invisible : true,  //加载隐藏的图片，弄人为不加载
                        threshold: 50
                    });
                    document.addEventListener('touchmove', function (e) {
                        $("img").scrollLoading();
                    }, false);
                    document.addEventListener('DOMContentLoaded', function(){
                        $("img").scrollLoading();
                    }, false);
                }
            }
        },"JSON");
    }

    //根据AjAx获取内容
    function getOrderList(url,page,callback)
    {
        if(page=="" || page==undefined)  page = 1;
        $.post(url,{page:page},function(data){
            if(data.status==1){
                if (parseInt(data.page.pagesize) > 0) {
                    template.config("escape", false);
                    var strData = template('loadContainer', data);
                    $("#container").append(strData);

                    if (data.page.hasNextPage == true) {
                        $("input[name='currentPage']").val( parseInt(page) + 1);
                    }
                    $('body img').lazyload({
                        placeholder : "/static/images/no_pic.png",
                        effect : "fadeIn", //使用淡入特效
                        failure_limit : 10,  //容差范围
                        skip_invisible : true,  //加载隐藏的图片，弄人为不加载
                        threshold: 50
                    });
                    document.addEventListener('touchmove', function (e) {
                        $("img").scrollLoading();
                    }, false);
                    document.addEventListener('DOMContentLoaded', function(){
                        $("img").scrollLoading();
                    }, false);

                    if(callback !== undefined && callback !== ""){
                        callback();
                    }
                }
            }
        },"JSON");
    }

    //获取产品评论
    function getCommit(UsersID, productsid, total){
        var html="";
        var obj={
            'action':'getCommit',
            'productsid':productsid,
            'Users_ID': UsersID,
            'pagesize':10
        };
        var page = $("input[name='page']").val();
        var count = total;
        var presize =(page-1)*obj.pagesize;
        if(presize<=count){
            obj.page=page;
            $.post("/api/"+ UsersID +"/pintuan/xiangqing/"+obj.productsid+"/",obj,function(data){
                if(data.status==1){
                    $("#commit").empty();
                    var data=data.data;
                    for(var i=0;i<data.length;i++){
                        html+="<div class=\"pj\">";
                        html+="   <span class=\"l\">"+data[i].CreateTime+"</span>";
                        html+="   <span class=\"pjt r\">"+data[i].pingfen+"</span>";
                        html+="   <div class=\"clear\"></div>";
                        html+="   <div>"+data[i].Note+"</div>";
                        html+="</div>";
                    }
                    var totalPage = parseInt(count/obj.pagesize);
                    if(page>=totalPage+1){
                        html+="";
                    }else{
                        html+="<div class='gengduo'><a href='javascript:getCommit()'>加载更多</a></div>";
                    }
                    page++;
                    $("input[name='page']").val(page);
                    $("#commit").html(html);
                }else{
                    $("#commit").html("");
                }
            },"json");
        }
    }

    function selectTag(showContent,selfObj){
        // 操作标签
        var tag = document.getElementById("tags").getElementsByTagName("li");
        var taglength = tag.length;
        for(i=0; i<taglength; i++){
            tag[i].className = "";
        }
        selfObj.parentNode.className = "selectTag";
        // 操作内容
        for(i=0; j=document.getElementById("tagContent"+i); i++){
            j.style.display = "none";
        }
        document.getElementById(showContent).style.display = "block";
    }