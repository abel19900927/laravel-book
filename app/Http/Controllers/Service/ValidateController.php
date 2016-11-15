<?php

namespace App\Http\Controllers\Service;

use App\Entity\Member;
use App\Entity\TempEmail;
use Illuminate\Http\Request;
use App\Tool\Validate\ValidateCode;
use App\Http\Controllers\Controller;
use App\Tool\SMS\Send;
use App\Entity\TempPhone;

class ValidateController extends Controller
{
    public function create(Request $request)
    {
        $validateCode = new ValidateCode;
        $request->session()->put('validate_code', $validateCode->getCode());

        return $validateCode->doimg();
    }

    public function sendSMS(Request $request)
    {
        $phone = $request->input('phone', '');
        if ($phone == '') {
            return ['status' => 1, 'msg' => '手机号不能为空'];
        }

        $charset = '1234567890';
        $_len = strlen($charset) - 1;
        $code = '';
        for ($i = 0; $i < 6; ++$i) {
            $code .= $charset[mt_rand(0, $_len)];
        }

        // $aa = $sendTemplateSMS->sendTemplateSMS('13530520809', [$code, 60], 1);
        $msg = "欢迎您注册成为Abel博客会员，您的注册验证码为:".$code."，请在5分钟内输入。打死也不要告诉其他人哦！【Abel博客】";

        if (Send::SendSMS($phone, $msg)) {
            $tempPhone = TempPhone::where('phone', $phone)->first() ? TempPhone::where('phone', $phone)->first() : new TempPhone;
            $tempPhone->phone = $phone;
            $tempPhone->code = $code;
            $tempPhone->deadline = date('Y-m-d H:i:s', time() + 60*60);
            if ($tempPhone->save()) {
                return ['status' => 0, 'msg' => '短信发送成功'];
            }
        }
        return ['status' => 1, 'msg' => '短信发送失败'];
    }

    public function validateEmail(Request $request)
    {
        $member_id = $request->input('member_id', '');
        $code = $request->input('code', '');
        if($member_id == '' || $code == '') {
            return '验证异常';
        }

        $tempEmail = TempEmail::where('member_id', $member_id)->first();
        if($tempEmail == null) {
            return '验证异常';
        }

        if($tempEmail->code == $code) {
            if(time() > strtotime($tempEmail->deadline)) {
                return '该链接已失效';
            }

            $member = Member::find($member_id);
            $member->active = 1;
            $member->save();

            return redirect('/login');
        } else {
            return '该链接已失效';
        }
    }
}
