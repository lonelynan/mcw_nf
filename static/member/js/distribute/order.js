var order_obj={
	orders_send:function(){
		$("input[name=refuse]").click(function(){
			var type = $(this).attr('value');
			if(type == 1){
			$("#refuseshow").hide();
			$('#order_send_form input:submit').val('通过审核');
			}else{
			$("#refuseshow").show();
			$('#order_send_form input:submit').val('提交');
			}
		});
		
		$('#order_send_form').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#order_send_form input:submit').attr('disabled', true);
			return true;
		});
	},
	orders_init:function(){		
		var logic = function( currentDateTime ){
	if( currentDateTime.getDay()==6 ){
		this.setOptions({
			minTime:'11:00'
		});
	}else
		this.setOptions({
			minTime:'8:00'
		});
};
$('#search_form input[name=AccTime_S], #search_form input[name=AccTime_E]').datetimepicker({
	lang:'ch',
	onChangeDateTime:logic,
	onShow:logic
});
	},
}