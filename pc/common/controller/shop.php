<?php
namespace common\controller;
class shopController extends commonController {
	protected $UsersID = '';
	public function _initialize() {
		parent::_initialize();
		if(isset($_GET['UsersID'])) {
			$this->UsersID = $_GET['UsersID'];
		}else {
		    if(MAIN_SITE == $_SERVER['HTTP_HOST']){
			   header('location:http://' . MAIN_SITE . '/member/');
			}
			
			$users_dominfo = model('users')->field('Users_ID,domenable')->where(array('domname'=>$_SERVER['HTTP_HOST']))->find();
			$fronturl = model('Setting')->field('sys_dommain')->where(array('id'=>1))->find();
			if (!$fronturl) {
				exit('域名不存在！');
			} else {
				$this->Afterurl = 'http://'.$fronturl['sys_dommain'];
			}
			if(!empty($users_dominfo) && $users_dominfo['domenable'] == 1) {
				$this->UsersID = $users_dominfo['Users_ID'];
			} else {
				exit('网址不存在！');
			}
		}
		$this->assign('UsersID', $this->UsersID);
		
	}
	protected function url_parsing($ownerid){
	    //网址自由跳转
		
			if($this->_controller == 'goods') {
			    $url = $this->Afterurl . '/api/'.$this->UsersID.'/shop/'.$ownerid.'/products/'.$_GET['id'].'/';
			}else if($this->_controller == 'list') {
			    $url = $this->Afterurl . '/api/'.$this->UsersID.'/shop/'.$ownerid.'/category/'.$_GET['id'].'/';
			}else {
			    $url = $this->Afterurl . '/api/'.$this->UsersID.'/shop/'.$ownerid.'/union/';
			}
		
		if(isset($url)){
		    header('location:' . $url);
		}
	}
}