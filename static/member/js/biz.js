$(document).ready(function(){		
		jQuery.validator.addMethod( "isCheckName",function(value,element){       
        var pattern =/^([\'\"\[\]\.\?\{\}\<\>])+$/;    
            if(value !=''){
                if(pattern.exec(value)){
                    return false;
                }
            }
            return true;     
    });  
		jQuery.validator.addMethod( "isArticle",function(value,element){       
        var pattern =/^([\'\"\[\]\.\?\{\}])+$/;

            if(value !=''){
                if(pattern.exec(value)){
                    return false;
                }
            }
            return true;     
    });
		jQuery.validator.addMethod( "isCompire",function(value,element){
        var PriceY = parseFloat($.trim(value));
        var PriceX = parseFloat($.trim($("input[name='PriceX']").val()));
        if(PriceX<PriceY){
           return false;
        }
        return true;     
    });
    jQuery.validator.addMethod( "isSumCompire",function(value,element){
        var PriceX = parseFloat($.trim($("input[name='PriceX']").val()));
        var xiangoutimes = parseFloat($.trim(value));
        var PriceY = parseFloat($.trim($("input[name='PriceY']").val()));
        var split = parseFloat(PriceY/PriceX);
        if(split<xiangoutimes){
           return false;
        }
        return true;     
    });
    jQuery.validator.addMethod("isMobile", function(value, element) {
        var length = value.length;
        var mobile = /^(13[0-9]{9})|(18[0-9]{9})|(14[0-9]{9})|(17[0-9]{9})|(15[0-9]{9})$/;
        return this.optional(element) || (length == 11 && mobile.test(value));
    });
    jQuery.validator.addMethod("isPhone", function(value, element) {    
      var tel = /^(\d{3,4}-?)?\d{7,9}$/g;    
      return this.optional(element) || (tel.test(value));    
    });
    jQuery.validator.addMethod( "isURL",function(value,element){       
        var pattern ="^((https|http|ftp|rtsp|mms)?://)"         
                    + "?(([0-9a-zA-Z_!~*'().&=+$%-]+: )?[0-9a-zA-Z_!~*'().&=+$%-]+@)?" //ftp的user@        
                    + "(([0-9]{1,3}\.){3}[0-9]{1,3}" // IP形式的URL- 199.194.52.184        
                    + "|" // 允许IP和DOMAIN（域名）        
                    + "([0-9a-zA-Z_!~*'()-]+\.)*" // 域名- www.        
                    + "([0-9a-zA-Z][0-9a-zA-Z-]{0,61})?[0-9a-zA-Z]\." // 二级域名        
                    + "[a-zA-Z]{2,6})" // first level domain- .com or .museum        
                    + "(:[0-9]{1,4})?" // 端口- :80        
                    + "((/?)|"         
                    + "(/[0-9a-zA-Z_!~*'().;?:@&=+$,%#-]+)+/?)$";

            if(value !=''){
                var re=new RegExp(strRegex);        
                if(!re.test(str_url)){
                    return false;
                }
            }
            return true;     
    });

		$("#group_edit").validate({
      rules: {
        Account: {
          required: true,
          minlength:3,
          maxlength:30,
          isCheckName:true
        },
        password: {
            required: true,
            minlength:3,
            maxlength:20,
        },
        PassWordA: {
            required: true,
            minlength:3,
            maxlength:20,
            isCompire:true
        },
        Name:{
            required: true,
            minlength:1,
            maxlength:20,
            isCheckName:true
        },
        Address:{
            required: true,
            minlength:1,
            maxlength:40,
            isCheckName:true
        },
        SmsPhone:{
            //required: true,
            isMobile:true,
            number:true
        },
        Contact:{
            required: true,
            minlength:1,
            maxlength:20,
            isCheckName:true
        },
        Phone:{
            required: true,
            isPhone:true,
            number:true
        },
        Email:{
            email:true
        },
        Homepage:{
            isURL:true
        },
        FinanceRate:{
            required: true,
            number:true,
            min:0,
            max:100
        },
        PaymenteRate:{
            required: true,
            number:true,
            min:0,
            max:100
        }
      },
      messages: {
        Account: {
          required: "登录账号不能为空",
          minlength:"登录账号长度应在（3-30）个字符之间",
          maxlength:"登录账号长度应在（3-30）个字符之间",
          isCheckName:"登录账号不能包含\'\"[].?{}<>等字符"
        },
        password: {
            required: "请输入登录密码",
            minlength:"登录密码长度应在（3-20）个字符之间",
            maxlength:"登录密码长度应在（3-20）个字符之间",
        },
        PassWordA: {
            required: "请输入确认登录密码",
            minlength:"确认登录密码长度应在（3-20）个字符之间",
            maxlength:"确认登录密码长度应在（3-20）个字符之间",
            isCompire:true
        },
        Name:{
            required: "请输入商家名称",
            minlength:"商家名称长度应在（1-20）个字符之间",
            maxlength:"商家名称长度应在（1-20）个字符之间",
            isCheckName:"商家名称不能包含\'\"[].?{}<>等字符"
        },
        Address:{
            required: "请输入商家地址",
            minlength:"商家地址长度应在（1-40）个字符之间",
            maxlength:"商家地址长度应在（1-40）个字符之间",
            isCheckName:"商家地址不能包含\'\"[].?{}<>等字符"
        },
        SmsPhone:{
            //required: "请输入接受短信手机",
            isMobile:"接受短信手机不是正确的手机号",
            number:"接受短信手机不是正确的手机号"
        },
        Contact:{
            required: "请输入联系人",
            minlength:"联系人长度应在（1-20）个字符之间",
            maxlength:"联系人长度应在（1-20）个字符之间",
            isCheckName:"联系人不能包含\'\"[].?{}<>等字符"
        },
        Phone:{
            required: "请输入联系电话",
            isPhone:"联系电话不是正确的电话号码",
            number:"联系电话不是正确的电话号码"
        },
        Email:{
            email:"请输入正确的Email"
        },
        Homepage:{
            isURL:"请输入正确的URL"
        },
        FinanceRate:{
            required: "请输入网站提成",
            number:"网站提成只能是0到100之间的数值",
            min:"网站提成只能是0到100之间的数值",
            max:"网站提成只能是0到100之间的数值"
        },
        PaymenteRate:{
            required: "请输入结算比例",
            number:"结算比例只能是0到100之间的数值",
            min:"结算比例只能是0到100之间的数值",
            max:"结算比例只能是0到100之间的数值"
        }
      },
      errorPlacement:function(error, element){
          error.appendTo(element.parent().find(".fc_red"));
      }
		});
});