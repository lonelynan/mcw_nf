<?php 
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');
if (IS_AJAX && isset($_POST['action']) && $_POST['action'] == 'getActive') {
    $active_id = intval($_POST['aid']);
    $UsersID = trim($_POST['UsersID']);
    if ($active_id) {
        $rsActive = $DB->GetRs('active', '*', 
                "WHERE Users_ID='" . $UsersID . "' AND Active_ID=" . $active_id);
        if (! empty($rsActive)) {
            $data = [
                    'status' => 1,
                    'data' => $rsActive
            ];
            die(json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }
    $data = [
            'status' => 0
    ];
    die(json_encode($data));
}
// Ajax 判断是否添加过产品
if (IS_AJAX && isset($_POST['action']) && $_POST['action'] == 'getProduct') {
    $typeid = intval($_POST['Type_ID']);
    $typename = $typelist[$typeid]['module'];
    $UsersID = trim($_POST['UsersID']);
    $BizID = intval($_POST['BizID']);
    $flag = "";
    $url = "";
    $msg = "";
    /*if ($typename == 'pintuan') {
        $time = time();
        $flag = $DB->Get("pintuan_products", "*",
                "WHERE Users_ID='" . $UsersID .
                         "' AND Biz_ID=" . $BizID . " AND starttime<=" . $time . " AND stoptime>=" . $time . "");
        $url = "../pintuan/product_add.php";
        $msg = "拼团还没有产品请添加产品";
    } else
        if ($typename == 'cloud') {
            $flag = $DB->Get("cloud_products", "*",
                    "WHERE Users_ID='" . $UsersID .
                             "' AND Biz_ID=" . $BizID . " AND Products_SoldOut=0");
            $url = "../cloud/products_add.php";
            $msg = "云购还没有产品请添加产品";
        } else {
            $time = time();
            $flag = $DB->Get("zhongchou_project", "*",
                    "WHERE usersid='" . $UsersID .
                             "' AND Biz_ID=" . $BizID . " AND fromtime<=" . $time . " AND totime>=" . $time . "");
            $url = "../zhongchou/project_add.php";
            $msg = "众筹还没有项目请添加项目";
        }*/
    if (empty($flag)) {
        $data = [
                'status' => 0,
                'msg' => $msg,
                'url' => $url
        ];
    } else {
        $data = [
                'status' => 1,
                'msg' => $msg,
                'url' => $url
        ];
    }
    die(json_encode($data, JSON_UNESCAPED_UNICODE));
}

if (IS_POST) {
    $post = $_POST;
    $active_id = intval($post['Active_ID']);
    $TypeID = $post['TypeID'];
    $return_uri = "active.php?TypeID=" . $TypeID;
    $usersid = $post['UsersID'];
    $rsActive = $DB->GetRs("active", "*", 
            "WHERE Users_ID='" . $usersid . "' AND Active_ID='" . $active_id . "'");
    
    $rsActiveBiz = $DB->GetRs("biz_active", "count(*) AS total", 
            "WHERE Users_ID='" . $usersid . "'  AND Active_ID='" . $active_id . "'");
    
    /*if ($rsActiveBiz['total'] >= $rsActive['MaxBizCount']) {
        showMsg("只允许{$rsActive['MaxBizCount']}个商家参加活动", $return_uri, 1);
    }*/
    $data = [];
    $data['Users_ID'] = $post['UsersID'];
    $data['Active_ID'] = $post['Active_ID'];
    $data['Biz_ID'] = $post['BizID'];
    $data['ListConfig'] = $post['toplist'];
    $data['Status'] = 2;
    $data['addtime'] = time();
    if (! $post['toplist']) {
        showMsg("您还没有选择要推荐的产品", "javascript:history.go(-1)");
    }
    
    $flag = $DB->Add("biz_active", $data);
    if (true == $flag) {
        showMsg("添加成功", $return_uri, 1);
    } else {
        showMsg("添加失败", $return_uri, 1);
    }
} else {
    // 判断是否有产品
    
    $Active_ID = isset($_GET['activeid']) && $_GET['activeid'] ? $_GET['activeid'] : 0;
    $TypeID = isset($_GET['TypeID']) && $_GET['TypeID'] ? $_GET['TypeID'] : 1;
    $rsActive = [];
    if ($Active_ID) {
        $rsActive = $DB->GetRs("active", "*", 
                "WHERE Users_ID='" . $UsersID . "' AND Active_ID='" . $Active_ID . "'");
        if (empty($rsActive)) {
            showMsg("没有可以参与的活动", "/biz/", 1);
            exit();
        }
        $TypeID = $rsActive['Type_ID'];
        $rsActive['module'] = $typelist[$rsActive['Type_ID']]['module'];
        $result = "";
        $url = "";
        $msg = "";
        $backurl = "active.php?TypeID={$TypeID}";
        /*if ($rsActive['module'] == 'pintuan') {
            $time = time();
            $result = $DB->Get("pintuan_products", "*",
                    "WHERE Users_ID='" . $UsersID .
                             "' AND Biz_ID=" . $BizID . " AND starttime<=" . $time . " AND stoptime>=" . $time . "");
            $url = "../pintuan/product_add.php";
            $msg = "拼团还没有产品请添加产品";
        } else
            if ($rsActive['module'] == 'cloud') {
                $result = $DB->Get("cloud_products", "*",
                        "WHERE Users_ID='" . $UsersID .
                                 "' AND Biz_ID=" . $BizID . " AND Products_SoldOut=0");
                $url = "../cloud/products_add.php";
                $msg = "云购还没有产品请添加产品";
            } else {
                $time = time();
                $result = $DB->Get("zhongchou_project", "*",
                        "WHERE usersid='" . $UsersID .
                                 "' AND Biz_ID=" . $BizID . " AND fromtime<=" . $time . " AND totime>=" . $time . "");
                $url = "../zhongchou/project_add.php";
                $msg = "众筹还没有项目请添加项目";
            }*/
        $result = $DB->Get("shop_products", "*",
            "WHERE Users_ID='" . $UsersID .
            "' AND Biz_ID=" . $BizID );
        $flag = $DB->toArray($result);
        if (empty($flag)) {
            showMsg($msg, $url, 0, $backurl);
        }
    } else {
        $module = !empty($typelist[$TypeID]['module']) ? $typelist[$TypeID]['module'] : '';
        $result = "";
        $url = "";
        $msg = "";
        $backurl = "active.php?TypeID={$TypeID}";
        /*if ($module == 'pintuan') {
            $time = time();
            $result = $DB->Get("pintuan_products", "*", 
                    "WHERE Users_ID='" . $UsersID .
                             "' AND Biz_ID=" . $BizID . " AND starttime<=" . $time . " AND stoptime>=" . $time . "");
            $url = "../pintuan/product_add.php";
            $msg = "拼团还没有产品请添加产品";
        } else 
            if ($module == 'cloud') {
                $result = $DB->Get("cloud_products", "*", 
                        "WHERE Users_ID='" . $UsersID .
                                 "' AND Biz_ID=" . $BizID . " AND Products_SoldOut=0");
                $url = "../cloud/products_add.php";
                $msg = "云购还没有产品请添加产品";
            } else {
                $time = time();
                $result = $DB->Get("zhongchou_project", "*", 
                        "WHERE usersid='" . $UsersID .
                                 "' AND Biz_ID=" . $BizID . " AND fromtime<=" . $time . " AND totime>=" . $time . "");
                $url = "../zhongchou/project_add.php";
                $msg = "众筹还没有项目请添加项目";
            }*/
        $result = $DB->Get("shop_products", "*",
            "WHERE Users_ID='" . $UsersID .
            "' AND Biz_ID=" . $BizID );
        $flag = $DB->toArray($result);
        if (empty($flag)) {
            showMsg($msg, $url, 0, $backurl);
        }
        $time = time();
        $rsActive = $DB->GetRs("active", "*", 
                "WHERE Users_ID='" . $UsersID .
                         "' AND Type_ID={$TypeID} AND starttime<" . $time . " AND stoptime>" . $time . " AND Status=1");
        if (empty($rsActive)) {
            showMsg("没有可以参与的活动", "/biz/", 1);
        }
        $Active_ID = $rsActive['Active_ID'];
    }
    $time = time();
    $sql = "SELECT * FROM biz_active AS b LEFT JOIN active AS a ON b.Active_ID=a.Active_ID WHERE b.Users_ID='" .
             $UsersID . "' AND b.Biz_ID='" . $BizID . "' " .
             ($Active_ID ? "AND b.Active_ID='" . $Active_ID . "'" : "") .
             " AND a.Type_ID={$TypeID} AND a.starttime<" . $time . " AND a.stoptime>" . $time ;
    $result = $DB->query($sql);
    $flag = $DB->fetch_assoc($result);
    if ($flag) {
        showMsg("您已经参与过此次活动", "javascript:history.go(-1);", 1);
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title>添加活动</title>
        <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
        <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
        <script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
        <style>
        label { font-weight:900px;font-family:"宋体"}
        </style>
        <script>
        <?php if($Active_ID){ ?>
        $(document).ready(function(){
            $('#select').click(function(){
                  layer.open({
                      type: 2,
                      area: ['800px', '500px'],
                      fix: false,
                      maxmin: true,
                      content: '/biz/active/product_select.php?activeid='+"<?=$Active_ID ?>"
                  });              
            });
        });
        <?php }else{ ?>
        $(document).ready(function(){
            $('#select').click(function(){
                  var Active_ID = $("input[name='Active_ID']").val();
                  layer.open({
                      type: 2,
                      area: ['800px', '500px'],
                      fix: false,
                      maxmin: true,
                      content: '/biz/active/product_select.php?activeid='+Active_ID
                  });              
            });
        });
        
        
        
        $(function(){
            $("select[name='active']").change(function(){
                var aid = $(this).val();
                $.post("/biz/active/active_add.php",{ aid:aid,action:'getActive',UsersID:"<?=$UsersID ?>"},function(data){
                    if(data.status==1){
                        var active = data.data;
                        $("input[name='Active_ID']").val(active.Active_ID);
                        $("#active_name").text(active.Active_Name);
                    }
                },"json");
            });
        });
        <?php } ?>
        </script>
    </head>
	<body>
        <div id="iframe_page">
			<div class="iframe_content">
            	<div id="products" class="r_con_wrap" style="padding-bottom: 60px;">
              	<form id="product_add_form" class="r_con_form skipForm" method="post" action="active_add.php">
                    <input type="hidden" name="UsersID" value="<?=$UsersID ?>" />
                    <input type="hidden" name="Active_ID" value="<?=$Active_ID?:$rsActive['Active_ID'] ?>" />
                    <input type="hidden" name="BizID" value="<?=$BizID ?>" />
                    <input type="hidden" name="toplist" value=""/>
                    <input type="hidden" name="TypeID" value="<?=$TypeID?>"/>
                    
                    <div class="rows">
                    	<label>活动名称</label>
                    	<span class="input" style="width:300px;"><label id="active_name"><?=$rsActive['Active_Name'] ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
                    	</span>
                    	<div class="clear"></div>
                    </div>
                    <div class="rows">
                      <label>显示在列表页的产品</label>
                      <span class="input">
                          <div class="box1 col-md-6" style="width:500px;">
                          	  <a href="#" class="btn_green" id="select" style="float: right;">选择产品</a>
                              <select multiple="multiple" id="bootstrap-duallistbox-nonselected-list_commit" class="form-control" name="commit" style="height: 100px;width:300px;">
                              </select>
                           </div>
                      </span>
                      <div class="clear"></div>
                    </div>
                    <div class="rows">
                      <label></label>
                      <span class="input">
                      	  <input type="submit" class="btn_green" name="submit" value="立即参与" />
                      <div class="clear"></div>
                    </div>      
            	</form>
            </div>
          </div>
        </div>
        <script>
            $(function(){
                $("#product_add_form").submit(function(){
                    var toplist = $.trim($("input[name='toplist']").val());
                    if(toplist == ''){
                        alert("您还没有选择要推荐的产品");
                        return false;
                    }
                });

            });
        </script>
	</body>
</html>