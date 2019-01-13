?> <?php
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/flow.php');
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/windowSchedule.php');
    if(isset($_GET['action']) && $_GET['action'] == 'taskRemove'){
        $Users_Id = isset($_SESSION["Users_ID"]) ? $_SESSION["Users_ID"] : '';
        $taskName = $_SESSION["Users_ID"]."_Task";
        $task = new Task();
        $task->remove($taskName);
        $DB->Del("users_schedule","Users_ID='{$Users_Id}'");
        echo "<script> alert(\"删除计划任务成功\");history.go(-1); </script>";
        exit;
    }
    if ($_POST) {
        $RunType = $_POST['RunType'];
        $day = intval($_POST['day']);
        $Time = $_POST['Time'];
        $Users_Id = isset($_SESSION["Users_ID"]) ? $_SESSION["Users_ID"] : '';
        $StartRunTime = "";
        if(!$Users_Id){
            echo "<script> alert(\"Session过期，请重新登录\");top.location.href = '/member/login.php'; </script>";
            exit;
        }
        if(!$day){
            $day =1;
        }
        if(empty($Time) || !$Time){
            $Time = date("H:i");
        }

        $data = array(
            'Users_ID' => $Users_Id,
            'StartRunTime' => $Time,
            'RunType' => $RunType,
            'Status' => 1,
            'LastRunTime' => strtotime(date("Y-m-d",time())),
            'day' =>$day
        );
        //添加计划任务 

        $sch = $DB->GetRs("users_schedule", "*", "WHERE Users_ID='{$Users_Id}'");
		if ($sch) {
            $DB->Set("users_schedule", $data, "WHERE Users_ID='{$Users_Id}'");
        } else {
            $DB->Add("users_schedule", $data);
        }
		if(PHP_OS == 'WINNT'){
			$taskName = $_SESSION["Users_ID"]."_Task";
			$task = new Task();
			if ($sch) {
				$task->remove($taskName);
			}
			$type = "";
			if($RunType == 1){  //按周
				$task->add("mo",1);
				$type = "WEEKLY";
			}else if($RunType ==2 ){  //按天
				$task->add("mo",$day);
				$type = "DAILY";
			}else{  //按月
				$task->add("mo",1);
				$type = "MONTHLY";
			}
			$task->add("st",$Time);
			$task->add("ru",'"System"');
			$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat  http://".$_SERVER['HTTP_HOST']."/cron/Run.php");
			$task->getXML($taskName);
		}else{
			// 非windows 执行 
			$arrTime = explode(':', $Time);
			$cron  = $arrTime[1] . " " . $arrTime[0] . ' ';
			if($RunType == 1){  //按周
				$cron .= "* * */" . $day . " ";
			}else if($RunType ==2 ){  //按天
				$cron .= "*/" . $day . " * * ";
			}else{  //按月
				$cron .= "* */" . $day . " * ";
			}
			$cron .= " curl -s http://".$_SERVER['HTTP_HOST']."/cron/Run.php";
			try{
				Crontab::removeJob($cron);
				Crontab::addJob($cron);
			}catch(Exception $e){
				echo "请授予当前用户运行crontab的权限";exit;
			}
		}
		
        echo json_encode(['status'=>1]);
        exit;
    }
    ?>
    <!DOCTYPE HTML>
    <html>
    <head>
        <meta charset="utf-8">
        <title></title>
        <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
        <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
        <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
        <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
        <script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
        <script type='text/javascript' src="/static/js/select2.js"></script>
        <script type="text/javascript" src="/static/js/location.js"></script>
        <link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
        <script type="text/javascript" src="/static/js/area.js"></script>
        <link href="/static/css/select2.css" rel="stylesheet"/>

    <body>
    <!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
    <![endif]-->
    <div id="iframe_page">
        <div class="iframe_content">
            <link href='/static/member/css/shop.css' rel='stylesheet'
                  type='text/css' />

            <div class="r_nav">
                <ul>
                    <li><a href="/member/finance/sales_record.php">销售记录</a></li>
                    <li><a href="/member/finance/payment.php">付款单</a></li>
                    <li class="cur"><a href="/member/shop/setting/task_config.php">自动结算配置</a></li>
                </ul>
            </div>
            <div id="payment" class="r_con_wrap">
                <link href='/static/js/plugin/operamasks/operamasks-ui.css'
                      rel='stylesheet' type='text/css' />
                <script type='text/javascript'
                        src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
                <script type='text/javascript'
                        src='/static/js/plugin/daterangepicker/moment_min.js'></script>
                <link href='/static/js/plugin/daterangepicker/daterangepicker.css'
                      rel='stylesheet' type='text/css' />
                <script type='text/javascript'
                        src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
                <script language="javascript">$(document).ready(payment.payment_edit_init);</script>
                <form id="payment_form" class="r_con_form" method="post" action="/member/shop/setting/task_config.php">
                    <?php $sch = $DB->GetRs("users_schedule", "*", "WHERE Users_ID='{$_SESSION['Users_ID']}'");
                    $type = 2;
                    if($sch){
                        $type = $sch['RunType'];
                        $time = $sch['StartRunTime'];
                        $day = $sch['day'];
                        $lastRunTime = $sch['LastRunTime'];

                    }
                    ?>
                    <div class="rows">
                        <label>结算类型</label> <span class="input time"> <select
                                name='RunType'>
								<option value="1" <?=$type==1?"selected":"" ?>>按周结算</option>
								<option value="2" <?=$type==2?"selected":"" ?>>按天结算</option>
								<option value="3" <?=$type==3?"selected":"" ?>>按月结算</option>
						</select>&nbsp; (若按天结算，请手动填写天数)<font class="fc_red">*</font></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label>选择结算时间</label>
                        <span class="input time">
                            <input name="Time" type="text" value="<?=isset($time)?$time:date('H:i') ?>" class="form_input" size="40" notnull />
                            <font class="fc_red">*</font>
                            <span class="tips">需要结算的销售记录的时间段</span>
                        </span>
                        <div class="clear"></div>
                        <label>结算天数</label>
                        <span class="input time">
                            <input name="day" type="text" value="<?php echo isset($day)?$day:2; ?>" class="form_input" size="40" notnull />
                            <font class="fc_red">*</font>
                            <span class="tips">每隔N天进行结算</span>
                        </span>
                    </div>
                    <div class="rows">
                        <label></label>
                        <span class="input">
                            <input type="button" class="btn_green" value="确定" name="submit_btn">
                            <input type="button" class="btn_green" value="删除计划任务" name="removeTask">
                        </span>
                        <div class="clear"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(function(){
            $("input[name='removeTask']").click(function(){
                location.href = "/member/shop/setting/task_config.php?action=taskRemove";
            });

            $("select[name='RunType']").change(function(){

                var RunType = $("select[name='RunType']").val();
                if(RunType==1){
                    $("input[name='day']").val("7");
                }else if(RunType==3){
                    $("input[name='day']").val("<?php echo date("t",time());?>");
                }
            });

            $("input[name='submit_btn']").click(function(){
                $.post("/member/shop/setting/task_config.php",{
                    'RunType':$("select[name='RunType']").val(),
                    'Time':$("input[name='Time']").val(),
                    'day':$("input[name='day']").val()
                },function(data){},'json');
                alert("添加计划任务成功");
            });
        });

        $('#payment_form input[name=Time]').datetimepicker({
            datepicker:false,
            format:'H:i',
            step:5
        });
    </script>
    </body>
    </html>