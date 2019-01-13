var pintuan_obj={
	
	 
	home_init:function(){
		//加载上传按钮
		
		$('.m_lefter a').attr('href', '#').css({'cursor':'default', 'text-decoration':'none'}).click(function(){
			$(this).blur();
			return false;
		});
		
		
		
		//ajax提交更新，返回
		$('#home_form').submit(function(){return false;});
		$('#home_form input:submit').click(function(){
			$(this).attr('disabled', true);
			$.post('?', $('#home_form').serialize()+'&do_action=web.set_home_mod&ajax=1', function(data){
				$('#home_form input:submit').attr('disabled', false);
				if(data.status==1){
					alert("首页设置成功！");
					
					
				}else{
					alert("首页设置失败，请重试！");
					

				};
			}, 'json');
		});
		
		$('#home_form .item .rows .b_l a[href=#web_home_img_del]').click(function(){
			var _no=$(this).attr('value');
			$('#home_form .b_r').eq(_no).html('');
			$('#home_form input[name=ImgPathList\\[\\]]').eq(_no).val('');
			this.blur();
			return false;
		});
	} 
	 
	 
}