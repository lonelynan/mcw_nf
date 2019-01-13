var zhongchou_obj={
	config_form_init:function(){
		$('#config_form').submit(function(){return false;});
		$('#config_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			$.post('?', $('#config_form').serialize(), function(data){
				if(data.status==1){
					if(confirm(data.msg)){
						$('#config_form input:submit').attr('disabled', false);
					}else{
						$('#config_form input:submit').attr('disabled', false);
						window.location=data.url;
					}
				}else{
					alert(data.msg);
					$('#config_form input:submit').attr('disabled', false);
				}
			}, 'json');
		});
	},
	
	form_submit:function(){
		$('#form_submit').submit(function(){			
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#form_submit input:submit').attr('disabled', true);
			return true;
		});
	},
	
	project_edit:function(){
		var date_str=new Date();
		$('#form_submit input[name=Time]').daterangepicker({
			timePicker:true,
			minDate:new Date(date_str.getFullYear(), date_str.getMonth(), date_str.getDate()),
			format:'YYYY/MM/DD HH:mm:00'}
		);
		$('#form_submit').submit(function(){			
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#form_submit input:submit').attr('disabled', true);
			return true;
		});
	},
	
	message_init:function(){
		$('#user_message_form').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#user_message_form input:submit').attr('disabled', true);
			return true;
		});
	}
}
$(function(){
    jQuery.validator.addMethod( "isCheckName",function(value,element){       
        var pattern =/^([\'\"\[\]\.\?\{\}])+$/;   
            if(value !=''){
                if(pattern.exec(value)){
                    return false;
                }
            }
            return true;     
    });  

	jQuery.validator.addMethod( "isCompire",function(value,element){
    var other = parseFloat($.trim(value));
		var MaxGoodsCount = parseFloat($.trim($("input[name='MaxGoodsCount']").val()));
		if(MaxGoodsCount<=other){
			return false;
		}
        return true;     
    });
    
	$("#form_submit").validate({
          rules: {
        	  Title: {
              required: true,
              minlength:1,
              maxlength:16,
              isCheckName:true
            },
            Amount: {
              required: true,
              min:0.01,
              number:true,
              max:100000
            }
          },
          messages: {
        	 Title: {
              required: "请输入项目名称",
              minlength:"项目名称至少在1个字符以上",
              maxlength:"项目名称最多不能超过16个字符",
              isCheckName:"项目名称必需由英文字母或者汉字组成"
            },
            Amount: {
              required: "请输入筹款金额",
              min:"筹款金额必须大于0.01的数字",
              number:"筹款金额必须大于0.01的数字",
              max:"筹款金额最大不能超过10万"
            }
          },
      errorPlacement:function(error, element){
          error.appendTo(element.parent().find(".fc_red"));
      }
	});
	
		$("#prize_form_submit").validate({
          rules: {
        	  title: {
              required: true,
              minlength:1,
              maxlength:16,
              isCheckName:true
            },
            money: {
              required: true,
              min:0.01,
              number:true,
              max:100000
            },
            maxtimes: {
              required: true,
              min:0,
              number:true,
              max:100000
            }
          },
          messages: {
        	 title: {
              required: "请输入赠品名称",
              minlength:"赠品名称至少在1个字符以上",
              maxlength:"赠品名称最多不能超过16个字符",
              isCheckName:"赠品名称必需由英文字母或者汉字组成"
            },
            money: {
              required: "请输入支持金额",
              min:"支持金额必须大于0.01的数字",
              number:"支持金额必须大于0.01的数字",
              max:"支持金额最大不能超过10万"
            },
            maxtimes: {
              required: "请输入每人最多支持次数",
              min:"每人最多支持次数必须大于0的数字",
              number:"每人最多支持次数必须大于1的数字",
              max:"每人最多支持次数最大不能超过10万"
            }
          },
      errorPlacement:function(error, element){
          error.appendTo(element.parent().find(".fc_red"));
      }
	});

});