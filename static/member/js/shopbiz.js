var shopbiz_obj={
	biz_edit_init:function(){
		var usersid = $("#usersid").val();
		var gog = $("input[name='FinanceType2']:checked").val();
		if (gog == 1) {
			$("input[name=BizProfit]").attr('disabled', true);
			$("input[name=BizProfit]").removeClass('form_input');
			$("input[name=BizProfit]").addClass('form_input_disable');
		} else {
			$("input[name=BizProfit]").attr('disabled', false);
			$("input[name=BizProfit]").removeClass('form_input_disable');
			$("input[name=BizProfit]").addClass('form_input');
		}
		$('#loc_city').change(function(){
			var areaid;
			areaid = $(this).val();
			var data = {action:'get_region',areaid:areaid,usersid:usersid};
			$.get('/member/shop/ajax.php', data, function(data){
				if(data.status == 1){
					$('#regionid_1').empty();
					$('#regionid_1').append($("<option>").text("请选择").val(0));
					$('#regionid_0').empty();
					$('#regionid_0').append($("<option>").text("请选择").val(0));
					$.each(data.html,function(index,html){
						var option = $("<option>").text(html.name).val(html.id);
						$('#regionid_0').append(option);
                    });
				}else{
					$('#regionid_0').empty();
					$('#regionid_0').append($("<option>").text("请选择").val(0));
					$('#regionid_1').empty();
					$('#regionid_1').append($("<option>").text("请选择").val(0));
				}
			},"json");
		});
		
		$('#regionid_0').change(function(){
				
			var parentid = $(this).val();
			
			if(parentid>0){
				var data = {action:'get_region',parentid:parentid,usersid:usersid};
				$.get('/member/shop/ajax.php', data, function(data){
					if(data.status == 1){
						$('#regionid_1').empty();
						$('#regionid_1').append($("<option>").text("请选择").val(0));
						$.each(data.html,function(index,html){
							var option = $("<option>").text(html.name).val(html.id);
							$('#regionid_1').append(option);
						});
					}else{
						$('#regionid_1').empty();
						$('#regionid_1').append($("<option>").text("请选择").val(0));
					}
				},"json");
			}
		});
		
		$('#biz_edit').submit(function(){
			
			if(global_obj.check_form($('*[notnull]'))){return false};
			var un_status = $('#submit_button_green').attr("data-status");
			if(un_status == 1){
				return false;
		    }
			//$('#biz_edit input:submit').attr('disabled', true);
			return true;
		});

		$("input[name=FinanceType2]").click(function(){
			var selectidgo = $("input[name='FinanceType2']:checked").val();
			if (selectidgo == 1) {
				$("input[name=BizProfit]").attr('disabled', true);
				$("input[name=BizProfit]").removeClass('form_input');
				$("input[name=BizProfit]").addClass('form_input_disable');
			} else {
				$("input[name=BizProfit]").attr('disabled', false);
				$("input[name=BizProfit]").removeClass('form_input_disable');
				$("input[name=BizProfit]").addClass('form_input');
			}
		});
	},
	biz_shipping_default_edit:function(){
		
		$("input.Default_Shipping").click(function(){
			var template_exist = $(this).parent().prev().find('select').length;
		
			if(template_exist == 0){
				alert('请为此物流公司添加模板');
				return false;
			}
		});
	
	    $('#shipping_default_config input:submit').click(function(){
			
			if(global_obj.check_form($('#shipping_default_config *[notnull]'))){
				return false;
			};
			
		});
	
	}
}