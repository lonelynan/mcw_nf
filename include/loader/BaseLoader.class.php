<?php
class BaseLoader
{
    /**
     * 请求
     * @param string $url
     * @param string $method
     * @param array $params
     * @param return json
     */
    public function http_request($url, $method = 'post', $params = array()){
        $options = array(
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER =>FALSE,
            CURLOPT_SSL_VERIFYHOST =>FALSE
        );
        $param_string = http_build_query($params);
        if($method == 'post'){
            $options += array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $param_string);
        }else{
            if($param_string)
                $options[CURLOPT_URL] .= '?'.$param_string;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        if( ! $result = curl_exec($ch))
        {
            $result = curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}