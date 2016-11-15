<?php

/**
 * 中国网建
 */

namespace App\Tool\SMS;

class Send
{
    protected static $smsUser = 'w19910111';
    protected static $smsKey = 'db341c76d7ac53ec426c';

    /**
     * 短信发送函数
     * @param unknown_type $phoneNumer
     * @param unknown_type $content
     */
    static public function SendSMS($phoneNumer, $content)
    {
        $url = 'http://utf8.sms.webchinese.cn/?Uid=' . self::$smsUser . '&Key=' . self::$smsKey . '&smsMob=' . $phoneNumer . '&smsText=' . $content;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置否输出到页面
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); //设置连接等待时间
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
