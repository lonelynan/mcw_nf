<?php
require_once('../global.php');
//print_r($rsBiz);
?>
<!DOCTYPE html>
<html lang="cn" class="app fadeInUp animated">
	<head>
		<meta charset="utf-8" />
		<title>
			供货商-管理后台
		</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"
		/>
		<link rel="stylesheet" href="/static/biz/css/font-awesome.min.css" type="text/css">
		<link rel="stylesheet" href="/static/biz/css/font-awesome.css" type="text/css">
		<link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
	</head>
	<body>
		<link rel="stylesheet" href="/static/biz/css/admin.min.css" type="text/css">
		<link rel="stylesheet" href="/static/biz/css/home.css" type="text/css">
		<section class="vbox">
			<header class="header bg-white b-b b-light">
				<p>
					尊敬的供货商您好，仅需完成以下流程，就可以发布产品，同时进行店铺推广！
				</p>
			</header>
			<section class="scrollable  wrapper">
				<section class="panel panel-default">
					<form class="form-horizontal form-validate" method="post" action="/api/form/ajaxformurl">
						<div class="panel-body">
							<div class="step-list-wrp">
								<ul class="step-list list-unstyled">
									<li class="step-item step-box no-extra-up">
										<div class="step-inner" id="id-audit-fr">
											<div class="step-list-opr">
												<span>
													<i class="fa fa-check text-success">
													</i>
													已完成
												</span>
											</div>
											<div class="step-content">
												<h4>
													注册供货商
													<i class="fa fa-check text-primary m-l-sm">
													</i>
												</h4>
												<div class="step-desc">
													快速一键注册
												</div>
											</div>
										</div>
										<span class="icon-step-line icon-step-line-up">
										</span>
										<span class="icon-step-line icon-step-line-down">
										</span>
										<span class="icon-step-main-box">
											<span class="icon-step-list step">
												1
											</span>
										</span>
										<span class="arrow-main-box">
											<i class="arrow arrow-out">
											</i>
											<i class="arrow arrow-in">
											</i>
										</span>
									</li>
									<li class="step-item step-box ">
										<div class="step-inner" id="id-audit-fr">
                                                <?php
                                                if ($rsBiz['is_agree'] == 0) { ?>
                                                <div class="step-list-opr">
													<span>
														<i class="fa fa-close text-success">
														</i>
														未完成
													</span>
                                                    <a href="agreement.php" class="text-blue" style="display:inline-block;">
													立即完成
													</a>
												
												</div>
                                                <?php } else { ?>
                                                <div class="step-list-opr">
												<span>
													<i class="fa fa-check text-success">
													</i>
													已完成
												</span>
                                                    <a href="agreement.php" class="text-blue" style="display:inline-block;">
													查看资料
												</a>
												 
                                                
											</div>
                                                                                    <?php }
                                                                                    ?>
											
											<div class="step-content">
												<h4>
													签署入驻协议
													<i class="fa fa-check text-primary m-l-sm">
													</i>
												</h4>
												<div class="step-desc">
													在线签署商家入驻协议
												</div>
											</div>
										</div>
										<span class="icon-step-line icon-step-line-up">
										</span>
										<span class="icon-step-line icon-step-line-down">
										</span>
										<span class="icon-step-main-box">
											<span class="icon-step-list step">
												2
											</span>
										</span>
										<span class="arrow-main-box">
											<i class="arrow arrow-out">
											</i>
											<i class="arrow arrow-in">
											</i>
										</span>
									</li>
									<li class="step-item step-box">
										<div class="step-inner" id="id-verify-fr">
											<div class="step-list-opr">
                                                                                            <?php  if ($rsBiz['is_auth'] == 0) { ?>
                                                                                                <span>
													<i class="fa fa-bolt text-warning">
													</i>
													未提交
												</span>
												<a class="text-blue" href="anthing.php">
													立即提交
												</a>
                                                                                            
                                                                                            <?php }elseif ($rsBiz['is_auth'] == 1){?>
                                                                                                <span>
													<i class="fa fa-bolt text-warning">
													</i>
													审核中
												</span>
												<a class="text-blue" href="anthing.php">
													修改资料
												</a>
                                                                                                
                                                                                            <?php }elseif ($rsBiz['is_auth'] == 2){ ?>
                                                                                                <span>
													<i class="fa fa-bolt text-warning">
													</i>
													已认证
												</span>
                                                                                                <a class="text-blue" href="anth_detail.php">
													查看资料
												</a>
                                                                                            <?php }else{?>
                                                                                                 <span>
													<i class="fa fa-bolt text-warning">
													</i>
													审核未通过
												</span>
												<a class="text-blue" href="anthing.php">
													重新提交
												</a>
                                                                                                
                                                                                            <?php }?>
												
											</div>
											<div class="step-content">
												<h4>
													提交资质
												</h4>
												<div class="step-desc">
													提交企业资料和银行账户信息
												</div>
											</div>
										</div>
										<span class="icon-step-line icon-step-line-up">
										</span>
										<span class="icon-step-line icon-step-line-down">
										</span>
										<span class="icon-step-main-box">
											<span class="icon-step-list step">
												3
											</span>
										</span>
										<span class="arrow-main-box">
											<i class="arrow arrow-out">
											</i>
											<i class="arrow arrow-in">
											</i>
										</span>
									</li>
									<li class="step-item step-box">
										<div class="step-inner" id="id-audit-fr">
                                                                                    <?php  if ($rsBiz['is_pay'] == 0) { ?>
                                                                                                <div class="step-list-opr">
                                                                                                        <a class="btn btn-primary" href="payment.php" data-toggle="popover" data-html="true"
                                                                                                        data-placement="bottom" data-trigger="click" data-content="抱歉，请完成资质提交并通过审核后，方可查看到准确的付款信息！">
                                                                                                                立即付款
                                                                                                        </a>
                                                                                                </div>
                                                                                            
                                                                                    <?php }elseif ($rsBiz['is_pay'] == 1){?>
                                                                                             <div class="step-list-opr">
                                                                                                      <span>
													<i class="fa fa-check text-success">
													</i>
													已支付
                                                                                                    </span>
                                                                                                  <a class="text-blue" href="payment_detail.php">
													查看支付信息
												</a>
                                                                                                </div>
                                                                                    <?php }else{ ?>
                                                                                    <?php } ?>
											<div class="step-content">
												<h4>
													付款
												</h4>
												<div class="step-desc">
													根据不同的入驻类目及入驻年限打款
												</div>
											</div>
										</div>
										<span class="icon-step-line icon-step-line-up">
										</span>
										<span class="icon-step-line icon-step-line-down">
										</span>
										<span class="icon-step-main-box">
											<span class="icon-step-list step">
												4
											</span>
										</span>
										<span class="arrow-main-box">
											<i class="arrow arrow-out">
											</i>
											<i class="arrow arrow-in">
											</i>
										</span>
									</li>
									<li class="step-item step-box no-extra-down">
										<div class="step-inner">
											<div class="step-content">
												<h4>
													入驻成功！
													<i class="fa fa-check text-primary m-l-sm hide">
													</i>
												</h4>
												<div class="step-desc">
													恭喜您成功入驻<?=$rssetconfig['sys_name']?>！
												</div>
											</div>
										</div>
										<span class="icon-step-line icon-step-line-up">
										</span>
										<span class="icon-step-line icon-step-line-down">
										</span>
										<span class="icon-step-main-box">
											<span class="icon-step-list step">
												5
											</span>
										</span>
										<span class="arrow-main-box">
											<i class="arrow arrow-out">
											</i>
											<i class="arrow arrow-in">
											</i>
										</span>
									</li>
								</ul>
							</div>
						</div>
					</form>
				</section>
			</section>
		</section>
	</body>

</html>