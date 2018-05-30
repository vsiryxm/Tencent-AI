<?php

/**
 * 腾讯AI SDK
 * Author：Simon<vsiryxm@qq.com>
 * Date：2017/11/30
 */
class TencentAI
{
    const API_URL_PREFIX = 'https://api.ai.qq.com/fcgi-bin';

    /* 自然语言处理：基本文本分析 */
    const TEXTTRANS_URL = '/nlp/nlp_texttrans'; //文字自动翻译接口

    /* 计算机视觉：图片特效 */
    const FACECOSMETIC_URL = '/ptu/ptu_facecosmetic'; //人脸美妆接口
    const FACEDECORATION_URL = '/ptu/ptu_facedecoration'; //人脸变妆接口
    const IMGFILTER_URL = '/ptu/ptu_imgfilter'; //滤镜接口
    const FACEMERGE_URL = '/ptu/ptu_facemerge'; //人脸融合接口
    const FACESTICKER_URL = '/ptu/ptu_facesticker'; //大头贴接口
    const FACEAGE_URL = '/ptu/ptu_faceage'; //颜龄检测接口

    private $app_id;
    private $app_key;
    private $nonce_str; //随机字符串，1~32字节即可
    private $time_stamp;
    public $error_code;
    public $error_msg;
    private $parameters; //参数配置
    public $debug; //默认为false，不开启调试模式；true开启调试模式，写入日志

    public function __construct($options)
    {
        $this->app_id = isset($options['app_id']) ? $options['app_id'] : '';
        $this->app_key = isset($options['app_key']) ? $options['app_key'] : '';
        $this->nonce_str = self::createNonceStr(32);
        $this->time_stamp = time();
        $this->error_code = 0;
        $this->error_msg = '';
        $this->debug = isset($options['debug']) ? $options['debug'] : false;
        $this->logcallback = isset($options['logcallback']) ? $options['logcallback'] : '';
        $this->parameters = [];
    }

    /**
     * 人脸融合
     * @param string $model 人脸图片模板，更多参见官网文档，可自定义上传
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facemerge.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function faceMerge($model = 1, $image)
    {
        if (empty($model)) {
            $this->error_msg = '未选择素材模板~';
            return false;
        }
        if (empty($image)) {
            $this->error_msg = '未上传人脸图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'model' => $model,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::FACEMERGE_URL, $this->parameters);
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 人脸美妆
     * @param string $cosmetic 美妆模板，更多参见官网文档，可自定义上传
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facecosmetic.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function faceCosmetic($cosmetic = 1, $image)
    {
        if (empty($cosmetic)) {
            $this->error_msg = '未选择美妆模板~';
            return false;
        }
        if (empty($image)) {
            $this->error_msg = '未上传人脸图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'cosmetic' => $cosmetic,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::FACECOSMETIC_URL, $this->parameters);	
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 人脸变妆
     * @param string $decoration 变妆模板，更多参见官网文档，可自定义上传
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facedecoration.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function faceDecoration($decoration = 1, $image)
    {
        if (empty($decoration)) {
            $this->error_msg = '未选择变妆模板~';
            return false;
        }
        if (empty($image)) {
            $this->error_msg = '未上传人脸图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'decoration' => $decoration,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::FACEDECORATION_URL, $this->parameters);
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 图片滤镜
     * @param string $filter 滤镜特效模板，更多参见官网文档，可自定义上传
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facedecoration.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function imgFilter($filter = 1, $image)
    {
        if (empty($filter)) {
            $this->error_msg = '未选择滤镜特效~';
            return false;
        }
        if (empty($image)) {
            $this->error_msg = '未上传图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'filter' => $filter,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::IMGFILTER_URL, $this->parameters);
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 大头贴
     * @param string $sticker 大头贴模板，更多参见官网文档，可自定义上传
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facesticker.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function faceSticker($sticker = 2, $image)
    {
        if (empty($sticker)) {
            $this->error_msg = '未选择大头贴模板~';
            return false;
        }
        if (empty($image)) {
            $this->error_msg = '未上传图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'sticker' => $sticker,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::FACESTICKER_URL, $this->parameters);
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 颜龄检测
     * @param string $image 用户图片base64码，<500KB，需要去除前缀`data:image/jpeg;base64,`
     * @link https://ai.qq.com/doc/facesticker.shtml
     * @return string 加工后的图片base64码，需要增加前缀`data:image/jpeg;base64,`才能显示
     */
    public function faceAge($image)
    {
        if (empty($image)) {
            $this->error_msg = '未上传图片~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'image' => $image,
        ];
        $this->getSignature(); //添加签名		
        $result = $this->http_post(self::API_URL_PREFIX . self::FACEAGE_URL, $this->parameters);
        if (false === $result) {
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['image'];
    }

    /**
     * 自动识别文字翻译
     * @param string $text 文本句子
     * @param integer $type 翻译类型，0为自动识别（中英文互转），更多参见官网文档
     * @link https://ai.qq.com/doc/nlptrans.shtml
     * @return string
     */
    public function textTrans($text, $type = 0)
    {
        if (empty($text)) {
            $this->error_msg = '未提供翻译文本~';
            return false;
        }
        $this->parameters = [
            'app_id' => $this->app_id,
            'nonce_str' => $this->nonce_str,
            'time_stamp' => $this->time_stamp,
            'text' => $text,
            'type' => $type,
        ];
        $this->getSignature(); //添加签名
        $result = $this->http_post(self::API_URL_PREFIX . self::TEXTTRANS_URL, $this->parameters);
        if (false === $result) {
            $this->error_msg = '请求接口失败~';
            return false;
        }
        $json = json_decode($result, true);
        if (empty($json) || intval($json['ret']) > 0) {
            $this->error_code = $json['ret'];
            $this->error_msg = $json['msg'];
            return false;
        }
        return $json['data']['trans_text'];
    }

    /**
     * 生成签名
     * @link https://ai.qq.com/doc/auth.shtml
     * @return string
     */
    protected function getSignature()
    {
        /* 获得参数对列表N（字典升级排序） */
        ksort($this->parameters, SORT_STRING);
        /* 按URL键值拼接字符串T */
        $string = http_build_query($this->parameters);
        /* 拼接应用密钥，得到字符串S */
        $string .= '&app_key=' . $this->app_key;
        /* 计算MD5摘要，得到签名字符串，转化md5签名值大写 */
        $signature = strtoupper(md5($string));
        $this->parameters['sign'] = $signature;
        return $signature;
    }

    /**
     * 生成随机字符串
     * @param integer $length 长度
     * @return string
     */
    protected static function createNonceStr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * POST 请求
     * @param string $url 请求地址
     * @param array $param 待提交数据
     * @param boolean $post_file 是否文件上传
     * @return string
     */
    protected function http_post($url, $param, $post_file = false)
    {
        $curl = curl_init();
        if (false !== stripos($url, 'https://')) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curl_file = true;
        } else {
            $is_curl_file = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        $post_data = http_build_query($param);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $http_result = curl_exec($curl);
        $http_info = curl_getinfo($curl);
        curl_close($curl);
        if (200 !== intval($http_info['http_code'])) {
            $this->error_msg = '请求提交失败~';
            return false;
        }
        return $http_result;
    }

    /**
     * 日志记录，可被重载
     * @param mixed $log 输入日志
     * @return mixed
     */
    protected function log($log)
    {
        if ($this->debug && function_exists($this->logcallback)) {
            if (is_array($log)) {
                $log = print_r($log, true);
            }
            return call_user_func($this->logcallback, $log);
        }
    }

}
