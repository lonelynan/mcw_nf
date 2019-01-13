var record_obj={
	record_init:function(){
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
	}
}