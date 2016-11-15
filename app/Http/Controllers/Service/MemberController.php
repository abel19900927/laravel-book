<?php

namespace App\Http\Controllers\Service;

use App\Entity\Member;
use App\Entity\TempPhone;
use App\Entity\TempEmail;
use App\Models\M3Email;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tool\UUID;
use Mail;

class MemberController extends Controller
{
    public function register(Request $request)
    {
        $email = $request->input('email', '');
        $phone = $request->input('phone', '');
        $password = $request->input('password', '');
        $confirm = $request->input('confirm', '');
        $phone_code = $request->input('phone_code', '');
        $validate_code = $request->input('validate_code', '');

        if ($email == '' && $phone == '') {
            return ['status' => 1, 'msg' => '手机号或邮箱不能为空'];
        }
        if (Member::where('phone', $phone)->whereOr('email', $email)->first()) {
            return ['status' => 1, 'msg' => '手机号或邮箱已被注册'];
        }
        if ($password == '' || strlen($password) < 6) {
            return ['status' => 1, 'msg' => '密码不少于6位'];
        }
        if ($confirm == '' || strlen($confirm) < 6) {
            return ['status' => 1, 'msg' => '确认密码不少于6位'];
        }
        if ($password != $confirm) {
            return ['status' => 1, 'msg' => '两次密码不相同'];
        }

        if ($phone != '') {
            if ($phone_code == '' || strlen($phone_code) != 6) {
                return ['status' => 1, 'msg' => '手机验证码为6位'];
            }
            $tempPhone = TempPhone::where('phone', $phone)->first();
            if ($tempPhone->code == $phone_code) {
                if (time() > strtotime($tempPhone->deadline)) {
                    return ['status' => 1, 'msg' => '手机验证码不正确'];
                }
                $member = new Member;
                $member->phone = $phone;
                $member->email = $email;
                $member->password = md5('bk' . $password);
                if ($member->save()) {
                    return ['status' => 0, 'msg' => '注册成功'];
                } else {
                    return ['status' => 1, 'msg' => '注册失败'];
                }
            } else {
                return ['status' => 1, 'msg' => '手机验证码不正确'];
            }
        } else {
            if ($validate_code == '' || strlen($validate_code) != 4) {
                return ['status' => 1, 'msg' => '验证码为4位'];
            }
            $validate_code_session = $request->session()->get('validate_code', '');
            if ($validate_code_session != $validate_code) {
                return ['status' => 1, 'msg' => '验证码不正确'];
            }

            $member = new Member;
            $member->phone = $phone;
            $member->email = $email;
            $member->password = md5('bk' . $password);
            $member->save();

            $uuid = UUID::create();

            $m3_email = new M3Email;
            $m3_email->to = $email;
            $m3_email->cc = 'wuyafeng0809@163.com';
            $m3_email->subject = 'abel书店验证';
            $m3_email->content = '请于24小时点击该链接完成验证. http://www.book.me/service/validate_email' . '?member_id=' . $member->id . '&code=' . $uuid;

            $tempEmail = new TempEmail;
            $memberInfo = Member::where('email', $email)->first();
            if ($memberInfo) {
                if (TempEmail::where('member_id', $memberInfo->id)->first()) {
                    $tempEmail = TempEmail::where('member_id', $memberInfo->id)->first();
                }
            }
            $tempEmail->member_id = $member->id;
            $tempEmail->code = $uuid;
            $tempEmail->deadline = date('Y-m-d H-i-s', time() + 24 * 60 * 60);
            $tempEmail->save();

            Mail::send('email_register', ['m3_email' => $m3_email], function ($m) use ($m3_email) {
                // $m->from('hello@app.com', 'Your Application');
                $m->to($m3_email->to, '尊敬的用户')->cc($m3_email->cc)->subject($m3_email->subject);
            });

            return ['status' => 0, 'msg' => '注册成功'];
        }
    }

    public function login(Request $request)
    {
        $username = $request->get('username', '');
        $password = $request->get('password', '');
        $validate_code = $request->get('validate_code', '');

        $validate_code_session = $request->session()->get('validate_code');
        if ($validate_code != $validate_code_session) {
            return ['status' => 1, 'msg' => '验证码不正确'];
        }

        $member = null;
        if (strpos($username, '@') == true) {
            $member = Member::where('email', $username)->first();
        } else {
            $member = Member::where('phone', $username)->first();
        }

        if ($member == null) {
            return ['status' => 1, 'msg' => '该用户不存在'];
        } else {
            if (md5('bk' . $password) != $member->password) {
                return ['status' => 1, 'msg' => '密码不正确'];
            }
        }
        $request->session()->put('member', $member);

        return ['status' => 1, 'msg' => '登录成功'];
    }
}