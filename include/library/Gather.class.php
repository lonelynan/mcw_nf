<?php
class Gather {
	private $url;
	private $path;
	private function ksort($arr) {
		foreach ( $arr as $value ) {
			$temp [] = $value;
		}
		return $temp;
	}

	/**
	 * 递归创建文件夹
	 * @param $dir
	 * @param int $mode
	 * @param bool|TRUE $makeindex
	 * @return bool
	 */
	private function dmkdir($dir, $mode = 0777, $makeindex = TRUE){
		if(!is_dir($dir)) {
			$this->dmkdir(dirname($dir), $mode, $makeindex);
			@mkdir($dir, $mode);
			if(!empty($makeindex)) {
				@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
			}
		}
		return true;
	}
	public function __construct($url, $path) {
		$this->url = $url;
		$this->path = $path;
		// 设置脚本执行不超时
		set_time_limit ( 0 );
	}
	public function fetch() {
		return $this->transform ( $this->url, $this->path );
	}
	private function createPic($url, $path, $name) {
		$info = getimagesize ( $url );
		$type = str_replace ( 'image/', '', $info ['mime'] );
		$fileName = $path . DIRECTORY_SEPARATOR . $name . ".$type";
		if (!file_exists($fileName)) {
			$img = file_get_contents ( $url );
			file_put_contents ( $fileName, $img );
		}
		return $fileName;
	}
	private function transform($url, $path) {
		$this->dmkdir($path);
		$data ['url'] = $url; // 文章URL
		$content = file_get_contents ( $url );
		preg_match ( '/<title>([.\s\S]*)<\/title>/i', $content, $result );
		$data ['title'] = $result [1]; // 文章标题
		preg_match ( '/var\s+msg_cdn_url\s*=\s*"([^\s]*)"/', $content, $result );
		$data ['cover'] = $result [1]; // 封面
		preg_match ( '/var\s+nickname\s*=\s*"([^\s]*)"/', $content, $result );
		$data ['nickname'] = $result [1]; // 公众号昵称
		preg_match ( '/var\s+ct\s*=\s*"([^\s]*)"/', $content, $result );
		$data ['ct'] = $result [1]; // 公众号发布的时间戳
		preg_match ( '/var\s+user_name\s*=\s*"([^\s]*)"/', $content, $result );
		$data ['user_name'] = $result [1]; // 公众号的原始ID
		preg_match ( '/var\s+round_head_img\s*=\s*"([^\s]*)"/', $content, $result );
		$data ['round_head_img'] = $this->createPic ( preg_replace('/http:\/\//', 'https://', $result [1]), $path, "round_head_img_" . $data ['user_name'] ); // // 公众号头像

		// 获取微信主体内容
		preg_match ( '/<div\s+class="rich_media_content\s*"[\s\S]*id="js_content">(.*?)<\/div>/is', $content, $result ); //注意非贪婪的?
		// 精细化筛选
		// preg_match_all ( '/data-src="([a-zA-z]+:\/\/[^\s]*mmbiz\/([^\s]*)\/\d+\?)[^\s]*=([^\s]*)"|data-src="([a-zA-z]+:\/\/[^\s]*mmbiz\/([^\s]*)\/\d+)"|background-image\s*:\s*url\s*\(\s*([a-zA-z]+:\/\/[^\s]*mmbiz\/([^\s]*)\/\d+)\s*\)|background-image\s*:\s*url\s*\(\s*([a-zA-z]+:\/\/[^\s]*mmbiz\/([^\s]*)\/\d+\?)[^\s]*=([^\s]*)\s*\)/is', $result [1], $result2 );
		// 获取微信主体中的防盗链图片(含css背景图片)内容
		preg_match_all ( '/data-src="[a-zA-z]+:\/\/[^\s]*[mmbiz|mmbiz_jpg]\/[^\s]*\/\d+\?[^\s]*=[^\s]*"|data-src="[a-zA-z]+:\/\/[^\s]*[mmbiz|mmbiz_jpg]\/[^\s]*\/\d+"|background-image\s*:\s*url\s*\(\s*[a-zA-z]+:\/\/[^\s]*mmbiz\/[^\s]*\/\d+|background-image\s*:\s*url\s*\(\s*[a-zA-z]+:\/\/[^\s]*mmbiz\/[^\s]*\/\d+\?[^\s]*=[^\s]*/is', $result [1], $result2 );
		// 判断微信主体中是否包含防盗链图片
		if (! empty ( $result2 [0] )) {
			foreach ( $result2 [0] as $value ) {
				// 取出防盗链地址中的data-src值后的http://url主体部分
				preg_match ( '/[a-zA-z]+:\/\/[^\s]*\/[mmbiz|mmbiz_jpg]\/([^\s\/]*)\/\d+\?[^\s"]*|[a-zA-z]+:\/\/[^\s]*[mmbiz|mmbiz_jpg]\/([^\s\/]*)\/\d+/', $value, $temp );
				$temp = array_filter ( $temp );
				$temp = $this->ksort ( $temp );
				$urlList [] = $temp [0];
				$nameList [] = $temp [1];
			}
			foreach ( $urlList as $value ) {
				$name = array_shift ( $nameList );
				$fileName = $this->createPic ( $value, $path, $name ); // 保存为本地图片
				$content = str_replace ( $value, $fileName, $content );
			}
		}
		// 更新所有data-src的地址
		$result [1] = str_replace ( "data-src", "src", str_replace('<!--headTrap<body></body><head></head><html></html>-->', '', $content) );
		// 返回处理后的微信主体内容。
		$data ['content'] = trim($result [1]);
		return $data;
	}
}
