var games_obj={	
	games_edit_init:function(){

		$('#games_form select[Name="Pattern"]').val()==0 && $('#games_form .integral').hide();
		$('#games_form select[Name="Pattern"]').change(function(){
			if($(this).val()==0){
				$('#games_form .integral').hide();
			}else{
				$('#games_form .integral').show();
			}
		});
		
		$('#games_form').submit(function(){return false;});
		$('#games_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$(this).attr('disabled', true);
			$.post('?', $('#games_form').serialize(), function(data){
				if(data.status==1){
					if(confirm(data.msg)){
						$('#games_form input:submit').attr('disabled', false);
						window.location=data.url;
					}else{
						$('#games_form input:submit').attr('disabled', false);
						window.location=data.url;
					}
				}else{
					alert(data.msg);
					$('#games_form input:submit').attr('disabled', false);
				}
			}, 'json');
		});
	}
}