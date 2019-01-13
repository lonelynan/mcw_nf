
function timing () {
	var time = 120;
	var sendyzm = $('#sendyzm');
	sendyzm.prop('disabled', true).html(time + 's');
	var t = setInterval(function () {
		if (time <= 0) {
			$('#sendyzm').prop('disabled', false).html('获取验证码');
			clearInterval(t);
		} else {
			time--;
			sendyzm.html(time + 's');
		}
	}, 1000);
}


$(document).ready(function(){
	var lottery = document.getElementById("lotteryCanvas");
	var ctx = lottery.getContext('2d');
	var rotateDeg = 360 / prize_count / 2 + 90;
	for (var i = 0; i < prize_count; i++) {
		// 保存当前状态
		ctx.save();
		// 开始一条新路径
		ctx.beginPath();
		// 位移到圆心，下面需要围绕圆心旋转
		ctx.translate(150, 150);
		// 从(0, 0)坐标开始定义一条新的子路径
		ctx.moveTo(0, 0);
		// 旋转弧度,需将角度转换为弧度,使用 degrees * Math.PI/180 公式进行计算
		ctx.rotate((360 / prize_count * i - rotateDeg) * Math.PI / 180);
		// 绘制圆弧
		ctx.arc(0, 0, 150, 0,  2 * Math.PI / prize_count);

		// 颜色间隔
		if (i % 2 == 0) {
			ctx.fillStyle = '#ffb820';
		}else{
			ctx.fillStyle = '#ffcb3f';
		}

		// 填充扇形
		ctx.fill();
		// 绘制边框
		ctx.lineWidth = 0.5;
		ctx.strokeStyle = '#e4370e';
		ctx.stroke();

		// 恢复前一个状态
		ctx.restore();
	}

	$(".canvas-btn").on('click', function() {
		if(start==false){
			return;
		}
		var that = $(this);
		that.addClass('disabled');
		$.post('?','action=move', function(data){
			start = false;
			if(data.errorCode == 0){
				$('#canvas-content').rotate({
					duration:6000,
					angle: (360 / parseInt(data.data.total)) * parseInt(data.data.prize),
					animateTo:5400 - (360 / parseInt(data.data.total)) * parseInt(data.data.prize),
					easing:$.easing.easeOutSine,
					callback:function(){
						that.removeClass('disabled');
						start = true;
						global_obj.win_alert(data.msg, function(){
							if (data.msg='距离中奖就差0.01毫米,再来一次!') {
								window.location.reload();
							}
						});
					}
				});
			} else {
				start = true;
				global_obj.win_alert(data.msg,function(){

				});
			}
		}, 'json');
	})
	
	$('.prize_list li span a[href=#usesn]').click(function(){
		if (!bind_mobile) {
			$('#phoneN').slideDown(200);
			$('#phoneN .close').click(function(){
				$('#phoneN').slideUp(200);
			});
		} else {
			var sn=$(this).parent().attr('sn');
			var snid=$(this).parent().attr('snid');
			$('#UsedSn').slideDown(200);
			$('#UsedSn #UsedSnNumber').text(sn);
			$('#UsedSn form input[name=SNID]').val(snid);
			$('#UsedPrize input[name=bp]').focus();
			return false;
		}
	});

	//发送验证码
	$("#sendyzm").on('click', function() {
		var mobile = $("input[name=Mobile]").val();
		if (mobile.length < 11) {
			global_obj.win_alert('手机号输入错误！', function(){$("input[name=Mobile]").focus()});
			return false;
		}
		$.ajax({
			type:'post',
			url:'?',
			data:{action:'send',Mobile:mobile},
			dataType:'json',
			success:function(res) {
				if (res.status) {
					//此处开始读秒,并且把发送验证码按钮置为不可用,读秒完成后要置为可用.
					timing();
				} else {
					global_obj.win_alert(res.msg, function(){$("input[name=Mobile]").focus()});
					return false;
				}
			}
		})
	});


	//绑定手机号操作
	$('#phoneN .submit').click(function(){
		var mobile = $("input[name=Mobile]").val();
		var yzm = $("input[name=yzm]").val();
		if (mobile.length < 11) {
			global_obj.win_alert('手机号输入错误！', function(){$("input[name=Mobile]").focus()});
			return false;
		}
		if (yzm.length != 4) {
			global_obj.win_alert('验证码输入错误！', function(){$("input[name=yzm]").focus()});
			return false;
		}
		$.ajax({
			type:'post',
			url:'?',
			data:{action:'bind',Mobile:mobile, captcha:yzm},
			dataType:'json',
			success:function(res) {
				if (res.status) {
					global_obj.win_alert(res.msg, function(){window.location.reload()});
				} else {
					global_obj.win_alert(res.msg, function(){$("input[name=Mobile]").focus()});
					return false;
				}
			}
		})
	});

	$('#UsedPrize').submit(function(){return false;});
	$('#UsedPrize .submit').click(function(){
		if($('#UsedPrize input[name=bp]').val()==''){
			global_obj.win_alert('请输入商家密码！', function(){$('#UsedPrize input[name=bp]').focus()});
			return false;
		}
		
		$(this).attr('disabled', true).val('提交中...');
		$.post('', $('#UsedPrize').serialize()+'&action=used', function(data){
			if(data.errorCode==0){
				global_obj.win_alert('兑奖成功！', function(){window.top.location.reload();});
			}else{
				global_obj.win_alert(data.msg);
			};
			$('#UsedSn').slideUp(200);
			$('#UsedPrize input[name=bp]').val('');
			$('#UsedPrize input:submit').attr('disabled', false).val('提交');
		}, 'json');
	});
	
	$('#UsedPrize .close').click(function(){
		$('#UsedSn').slideUp(200);
	});
})