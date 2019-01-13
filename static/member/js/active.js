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
    
	$("#product_add_form").validate({
          rules: {
        	  Active_Name: {
              required: true,
              minlength:1,
              maxlength:16,
              isCheckName:true
            },
            ActiveType: {
              required: true
            },

            PriceDiscount: {
              required: true,
              number:true,
              max:99
            },
            MaxBizCount:{
                required: true,
                number:true,
                min:1,
                max:100000
            },
            MaxGoodsCount:{
                required: true,
                number:true,
                min:1,
                max:100000
            },
            BizGoodsCount:{
                required: true,
                number:true,
                min:1,
                max:100000,
                isCompire:true
            },
            ListShowGoodsCount:{
                required: true,
                number:true,
                min:1,
                digits:true,
                isCompire:true
            },
            BizShowGoodsCount:{
            	required: true,
                number:true,
                min:1,
                digits:true,
                isCompire:true
            }
          },
          messages: {
        	 Active_Name: {
              required: "请输入活动名称",
              minlength:"活动名称至少在1个字符以上",
              maxlength:"活动名称最多不能超过16个字符",
              isCheckName:"活动名称必需由英文字母或者汉字组成"
            },
            ActiveType: {
              required: "请选择活动类型"
            },
            PriceDiscount: {
              required: "请填写价格折扣",
              number:"商家推荐产品数必须为数字",
              max:"价格折扣不能大于99"
           },


            MaxBizCount:{
                required: "请输入商家数",
                number:"商家数必须为数字",
                min:"商家数不能小于1",
                max:"商家数最大不能超过10万"
            },
            MaxGoodsCount:{
                required: "活动最多产品数不能为空",
                number:"活动最多产品数必须为数字",
                min:"活动最多产品数至少为1个",
                max:"活动最多产品数最大不能超过10万"
            },
            BizGoodsCount:{
                required: "请输入商家推荐产品数",
                number:"商家推荐产品数必须为数字",
                min:"商家推荐产品数至少为1个",
                max:"商家推荐产品数最大不能超过10万",
                isCompire:"商家推荐产品数要小于活动最多产品数"
            },
            ListShowGoodsCount:{
                required: "列表页显示产品数不能为空",
                number:"列表页显示产品数必须为数字",
                min:"列表页显示产品数至少为1个",
                digits:"列表页显示产品数必须为整数",
                isCompire:"列表页显示产品数要小于活动最多产品数"
            },
            BizShowGoodsCount:{
            	required: "商家店铺页显示产品数不能为空",
                number:"商家店铺页显示产品数必须为数字",
                min:"商家店铺页显示产品数至少为1个",
                digits:"商家店铺页显示产品数必须为整数",
                isCompire:"商家店铺页显示产品数要小于活动最多产品数"
            }
          },
          errorPlacement:function(error, element){
              error.appendTo(element.parent().find(".fc_red"));
          }
	});

});