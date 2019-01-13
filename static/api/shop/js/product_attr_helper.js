// JavaScript Document
/*edit in 20160318*/
/**
 * 点选可选属性或改变数量时修改商品价格的函数
 */
/*function changePrice()
{
  var attr = getSelectedAttributes(document.forms['addtocart_form']);
  var qty = document.forms['addtocart_form'].elements['Qty'].value;
  var no_attr_price  =  document.forms['addtocart_form'].elements['no_attr_price'].value;
  var param = {action:'price',no_attr_price:no_attr_price,'ProductsID':Products_ID,'attr':attr,'qty':qty,'UsersID':UsersID};
  
  $("#spec_list").attr('value',attr);
  $.get(base_url+'api/shop/cart/ajax.php',param,function(data){
  		 if (data.status = 0)
 		 {
    		alert(data.mgs);
  		 }
  		 else
  		 {
			$("#cur_price").attr('value',data.result);
			$("#cur-price-txt").html(data.result*qty);
		 }
		 
  },'json');
}


function changeAtt(t) {
t.lastChild.checked='checked';
for (var i = 0; i<t.parentNode.childNodes.length;i++) {
        if (t.parentNode.childNodes[i].className == 'cattsel') {
            t.parentNode.childNodes[i].className = '';
        }
    }
t.className = "cattsel";
changePrice();
}*/
function changePrice(t)
{       
    var aa = $(t).attr('title');      
    var attr = getSelectedAttributes(document.forms['addtocart_form']);
 
    var attrnew=new Array();
    var qty = document.forms['addtocart_form'].elements['Qty'].value;
    var no_attr_price  =  document.forms['addtocart_form'].elements['no_attr_price'].value;
    $(t).children(".childrenatt").attr('name',"cattsel");
    var formSeriall = $("#addtocart_form").serialize();
    var param = {'aa':aa,'formSeriall':formSeriall,action:'price',no_attr_price:no_attr_price,'ProductsID':Products_ID,'attr':attr,'qty':qty,'UsersID':UsersID};
    $.get(base_url+'api/shop/cart/ajax.php',param,function(data){
        if (data.status = 0){
            alert(data.mgs);
        }else  {
            $("#cur_price").attr('value',data.result);
            
            $("#cur-price-txt").html(data.result*qty); //返回的价格变化
            $("#stock_val").html(data.property_count);	//返回的库存变化
            $("#spec_list").val(data.product_property_id);	//返回的关联属性的id
            $("#number_id").html(data.number_id);	//返回的关联属性的id
            //返回的图像变化
            var attr_image = data.attr_image || '';
            if(attr_image){
              $("#product-thumb img").attr('src', attr_image);
            }
            var resultarrays = data.result_arrays;
            for(var i=0; i<resultarrays.length; i++){
                
                var classname = resultarrays[i]; 
                $("."+classname).css("color",'#000');
                $("."+classname).attr('aria-disabled','false');
                $("."+classname).attr("href","javascript:");
                $("."+classname).css("cursor",'pointer');
                $("."+classname).attr("onclick","changeAtt(this)");				
            }
			
            if(aab =='class0'){

            }else{
                    $("."+aab).addClass("porpers");
            }

         }
  },'json');
  var aab = $(t).attr('value'); 
    var url = $(t).attr('url'); 
    var title = $(t).attr('title'); 
    if(url.length>1){
        $(t).addClass(aab+" porpers "+title);
    }
}
function changeAtt(t) {
    t.lastChild.checked='checked';
    var aab = $(t).attr('value');
    var url = $(t).attr('url');
    if(url.length>1){
        for (var i = 0; i<t.parentNode.childNodes.length;i++) {
            var classnames = t.parentNode.childNodes[i].className; 
            if(classnames !=undefined){
                var classnamess = classnames.substr(0,7);
                if (classnamess == 'cattsel') {
                    $(t).parent(".catt").children("."+aab).removeClass("cattsel");
                  
                }               
            }
        }
    }
	if(url.length<2){
        $(".class1").addClass("porpers");
    }
    $(t).removeClass("porpers");
	
    var aab = $(t).attr('value');  
    if(aab =='class0'){

    }else{
        $("."+aab).removeClass("porpers");
    }
    var classnum = aab.substr(5,2);
    if(classnum>1){
        //alert(classnum);
        var classnums = classnum-1;//alert(classnums);
        var classnumss = "class"+classnums;//alert(classnumss);
        $("."+classnumss).removeClass("porpers");
    }
     if(classnum>0){
        
        var classnum1s = parseInt(classnum)+parseInt(1);
        var classnum1ss = "class"+classnum1s;//alert(classnum1ss);//alert(classnum1ss);
        $("."+classnum1ss).removeClass("cattsel");
        $("."+classnum1ss).children(".childrenatt").attr("name","");
        
    }
    $(t).parent(".catt").children("a").children('.childrenatt').attr("name","");
    if(url.length<2){
        $(".porpers").removeClass("cattsel");
        $(t).parent(".catt").parent(".list-group-item").parent(".list-group").children("li").children(".catt").children("a").children('.childrenatt').attr("name","");
    }    
    $(".porpers").css("color",'#CDCDCD');
    $(".porpers").attr('aria-disabled','true');
    $(".porpers").css("cursor",'not-allowed');
    $(".porpers").removeAttr("href"); 
    $(".porpers").removeAttr("onclick");
    for (var i = 0; i<t.parentNode.childNodes.length;i++) {
		
        if (t.parentNode.childNodes[i].className == 'cattsel') {
            t.parentNode.childNodes[i].className = '';
        }
    }
    t.className = "cattsel";
changePrice(t);
}
/**

 * 获得选定的商品属性

 */

function getSelectedAttributes(formBuy)

{

  var spec_arr = new Array();

  var j = 0;

  for (i = 0; i < formBuy.elements.length; i ++ )

  {

    var prefix = formBuy.elements[i].name.substr(0, 5);



    if (prefix == 'spec_' && (

       ((formBuy.elements[i].type == 'radio' && formBuy.elements[i].parentNode.className == 'cattsel') || (formBuy.elements[i].type == 'checkbox' && formBuy.elements[i].checked)) ||

      formBuy.elements[i].tagName == 'SELECT'))

    {

      spec_arr[j] = formBuy.elements[i].value;

      j++ ;

    }

  }



  return spec_arr;

}

function mauual_check(){
 var attr_id_str =  $("#spec_list").attr('value');
 var attr_array = attr_id_str.split(',');
 

 for(var product_attr_id in attr_array){
	 $('#spec_value_'+attr_array[product_attr_id]).prop('checked',true);
 }
}
