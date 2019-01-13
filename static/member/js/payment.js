var payment={
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
		
		$("#search_form .output_btn").click(function(){
			window.location='./output.php?'+$('#search_form').serialize()+'&type=sales_record_list';
		});
	},
	payment_edit_init:function(){
		var date_str=new Date();
		$('#payment_form input[name=Time]').daterangepicker({
			timePicker:true,
			format:'YYYY/MM/DD HH:mm:00'}
		)
		$('#payment_form').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#payment_form input:submit').attr('disabled', true);
			return true;
		});
	}
}