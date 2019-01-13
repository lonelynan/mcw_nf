<?php 
class Crontab {
	
	//添加计划任务
	static public function addJob($job = '') {
		if (self::doesJobExist($job)) {
			return false;
		} else {
			$jobs = self::getJobs();
			$jobs[] = $job;
			return self::saveJobs($jobs);
		}
	}
	
	//删除计划任务
	static public function removeJob($job = '') {
		if (self::doesJobExist($job)) {
			$jobs = self::getJobs();
			unset($jobs[array_search($job, $jobs)]);
			return self::saveJobs($jobs);
		} else {
			return false;
		}
	}
	
	//检测计划任务是否存在
	static private function doesJobExist($job = '') {
		$jobs = self::getJobs();
		if (in_array($job, $jobs)) {
			return true;
		} else {
			return false;
		}
	}
	
	//获取所有的计划任务
	static private function getJobs() {
		$output = shell_exec('crontab -l');
		return self::stringToArray($output);
	}
	
	//字符串转数组
	static private function stringToArray($jobs = '') {
		$array = explode("\r\n", trim($jobs)); // trim() gets rid of the last \r\n
		foreach ($array as $key => $item) {
			if ($item == '') {
				unset($array[$key]);
			}
		}
		return $array;
	}
	
	//数组转字符串
	static private function arrayToString($jobs = array()) {
		$string = implode("\r\n", $jobs);
		return $string;
	}
	
	//保存计划任务
	static private function saveJobs($jobs = array()) {
		$output = shell_exec('echo "'.self::arrayToString($jobs).'" | crontab -');
		return $output; 
	}
}