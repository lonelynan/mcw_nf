
function imgdel(obj)
{
    $(obj).parent().remove();
}

function insertRow(){
	var newrow=document.getElementById('wholesale_price_list').insertRow(-1);
	newcell=newrow.insertCell(-1);
	newcell.innerHTML='数量： <input type="text" name="JSON[Wholesale]['+(document.getElementById('wholesale_price_list').rows.length-2)+'][Qty]" value="" class="form_input" size="5" maxlength="3" /> 价格：￥ <input type="text" name="JSON[Wholesale]['+(document.getElementById('wholesale_price_list').rows.length-2)+'][Price]" value="" class="form_input" size="5" maxlength="10" /><a href="javascript:;" onclick="document.getElementById(\'wholesale_price_list\').deleteRow(this.parentNode.parentNode.rowIndex);"> <img src="/static/member/images/ico/del.gif" hspace="5" /></a>';
}
KindEditor.ready(function(K) {
	K.create('textarea[name="Description"]', {
		themeType : 'simple',
		filterMode : false,
		uploadJson : '/biz/upload_json.php?TableField=web_column&Users_ID='+UsersID,
		fileManagerJson : '/biz/file_manager_json.php',
		allowFileManager : true
		
	});
	var editor = K.editor({
		uploadJson : '/biz/upload_json.php?TableField=web_article&Users_ID='+UsersID,
		fileManagerJson : '/biz/file_manager_json.php',
		showRemote : true,
		allowFileManager : true
	});
	K('#ImgUpload').click(function(){
		if(K('#PicDetail').children().length>=5){
			alert('您上传的图片数量已经超过5张，不能再上传！');
			return;
		}
		editor.loadPlugin('image', function() {
			editor.plugin.imageDialog({
				clickFn : function(url, title, width, height, border, align) {
					K('#PicDetail').append('<div><a href="'+url+'" target="_blank"><img src="'+url+'" /></a><span onclick="imgdel(this);">删除</span><input type="hidden" name="JSON[ImgPath][]" value="'+url+'" /></div>');
					editor.hideDialog();
				}
			});
		});
	});
	
});
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
		$("#product_add_form").validate({
      rules: {
        Name: {
          required: true,
          minlength:1,
          maxlength:50,
          isCheckName:true
        },
        Order: {
            required: true,
            number:true,
            min:0,
            digits:true
        },
        Category: {
          required: true
        },
        PriceY:{
            required: true,
            number:true,
            min:0,
            max:100000
        },
        PriceX:{
            required: true,
            number:true,
            min:0,
            max:100000,
            isCompire:true
        },
        xiangoutimes:{
            required: true,
            number:true,
            min:0
        },
        Products_Weight:{
            required: true,
            number:true,
            min:0,
            max:10000
        }
      },
      messages: {
        Name: {
          required: "请输入产品名称",
          minlength:"产品名称至少1个字符",
          maxlength:"产品名称最多不能超过50个字符",
          isCheckName:"产品名称必需由英文字母或者汉字组成"
        },
        Order: {
            required: "产品排序不能为空",
            number:"产品排序必须为数字",
            min:"产品排序必须大于等于0",
            digits:"产品排序必须大于等于0"
        },
        Category: {
          required: "请选择产品类型"
        },
        PriceY:{
            required: "请输入商品总价格",
            number:"商品总价格必须为数字",
            min:"商品总价格不能为负数",
            max:"商品总价格最大不能超过100万"
        },
        PriceX:{
            required: "请输入云购单次价格",
            number:"云购单次价格必须为数字",
            min:"云购单次价格不能为负数",
            max:"云购单次价格最大不能超过100万",
            isCompire:"云购单次价格不能大于商品总价格"
        },
        xiangoutimes:{
            required: "请输入限购次数",
            number:"限购次数必须为数字",
            min:"限购次数不能为负数"
        },
        Products_Weight:{
            required: "请输入产品重量",
            number:"产品重量必须为数字",
            min:"产品重量不能为负数",
            max:"产品重量最大不能超过10万"
        }
      },
      errorPlacement:function(error, element){
          error.appendTo(element.parent().find(".fc_red"));
      }
		});
});