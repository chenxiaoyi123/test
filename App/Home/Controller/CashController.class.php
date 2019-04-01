<?php
/**
 * Created by PhpStorm.
 * User: 97558
 * Date: 2018/12/3
 * Time: 15:08
 */

namespace Home\Controller;

use Think\Controller;
use Think\Log;

Vendor('alipay.aop.AopClient', '', '.php');
Vendor('alipay.aop.request.AlipayFundTransToaccountTransferRequest', '', '.php');
header("Access-Control-Allow-Credentials:true");
header('Access-Control-Allow-Origin:http://localhost:8080');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class CashController extends Controller
{
    //余额提现
    public function Cash()
    {
        $uid = intval(I('uid'));
        $mobile = str_replace(' ', '', trim(I('mobile')));
        $realname = I('realname');
        $money=I('money');
        $ip = $_SERVER["REMOTE_ADDR"];
        if (empty($mobile)) return_err("账户为空");
        if (!$uid > 0) return_err("用户数据参数错误");
        if (!$money > 0) {
            return_err('提现金额参数错误~');
        }
        $member=M('member');
        //开启事务
        $member->startTrans();
        $update=$member->where('id='.$uid)->setDec('balance',$money);
        if ($update==false){return_err('失败请重试~');}
        $data['uid'] = $uid;
        $data['realname'] = $realname;
        $data['mobile'] = $mobile;
        $data['amount'] = $money;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['status'] = 0;
        $data['ip'] = $ip;
        $cash = M('cash');
        $add = $cash->add($data);
        if ($add == false) {
            //任一执行失败，执行回滚操作，相当于均不执行
            $member->rollback();
            return_err('提现失败~');
        }
        //执行成功，提交事务
        $member->commit();
        return_data('success');
    }

    //支付宝提现
    public function ALtransfers()
    {
        $tel = 13525588695;
        $amount = 10;
        $re_user_name = '陈义';

        $cash = M('cash');
        $max = $cash->field('MAX(id) as sum')->find();
        $order_no = time() . $max['sum'];
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018073060792711';
        $aop->rsaPrivateKey = 'MIIEpgIBAAKCAQEAqFLbeWZqkHbpxybJzmPkT7+k0Qgxi1S3Nb+wCaJRDxDNjbopIJe7LpWuTJX3LK/2FDqBscyrcSTCutFe9xi3BnULaVGWVJJlBKZLJexll6TpyOxX4j+u/2sX93Hq90qxQECge+7AhQ9fQFtSp/excgj8mwdJJik9CnV2Z5VWS/rllFAczeckaeF72YlvbQY+MpsywGba3ZsnNm1kDLS32yucCr/eyhptq60dZlXudG0nZx+H/+OUNd9mL3RJzt0KE+/x+5YFtICKujWlFXvP4zXMFSFUN1+QuMGDUAknjWgRachOAdcg7eMLHh9/+M3m279eSBJizyA3OrWjeUe9mQIDAQABAoIBAQCGigvRAjKFG/cJ7o/5PtC7iYPUbIclReZWuMudN7cwoo6aDMVYvs6nko5JushhWJgJXSZTFjOmcOqQ5k7QlFmeeKlRWhwdpxHFYKHKQySEzBTtOzOXrK1UjKYQa2aSmIoKwF1GEfShpkLNLvFnPkz/x/0YcP9f2DBpDrBAZRYgDbNblNvP+dXRlEc/rM5o+QwSTTDTuIb/g/X+nM1jEK41k9GEJIY0uBkSkXKdb19csTiMewKAfviBxfCe4FTxjkIloGDu8iW7GzxtR9VJig145+tWqk9kMPeB7PpeEmLwyGsfjxxqoQjMzdLdzBOeLmF3rgb3itxVu1UYqPI8yKhNAoGBANuOqexhgk0xAFFtIVABWzqZq7sWmXWbex26MQfSjy584CwVp8s5K0FCzXtXIOP77SBxCZz1+2mw4vkkmIaiXy2iqTM3Oi00xlxVcqSjfjIvBanhrDrVOjobWNSXwkMHzmt2COZvs/jofHi0vrvDW/gdAWKaGLQi+pYufT7aEdcHAoGBAMRDMi68+U0t/Zv/3UfDt2IO38O9rSWl2KOhfbIWw4ocI1ugmKEhCcUeMQgIqJeQuPckq+NZThTOs1o1YFSLpGMuDU34unTPndajfbR9CelwbT2PmGS0nRVFLhhAUJ9usBNVvMaDcc6S2WzCLnJv1f4Pl3IAuWczbEwQ+ac4MP5fAoGBAK+9sBH/svbqpCCJQ8Lwcv+jBa0JV+ilfZS79ocWaXmCh0WCR/8JUbA5MpTplvAmNRZkpJc45fchmWxneJc73QeATgMqz6xjs+swkkVqgJbWwKfMdbnZ93OPdDknCF3zH60wm8sn2l0AarGLq6hLpZAiV3t/cQqvfPk9WQ84KlN9AoGBAL1bU8ySWTok2F6t49J8u68pSK2zkJ4VQErH4d10Zx8WfOrHrNsxZBrCQW5N5FOvtzYENK96l4It2A9+Fj4cKPPkF8QV6dgQBGp1fTApv+lxpoRRyifHtxMxlwKg8uiQQ+OzwhoJ8kDroEl1pJiW3HFum6DLoBY5IBDYA/dZmLOjAoGBAKFxyNqP9uVSw5KHlBGxIfkh2utK9IBgBuOHGMNpaii8UQMR99v5BBtC5h0b5/XicM4FA+cJ+RoL2zBgxBGvAeS6rZ3zw0z36AxFshkAA+wbUIOSqr1pP0JEdTEoekI9ZOVO/LwY9FxJjBn2R3PUwmoP2x/mgIlvisuRrGOJyHiv';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqFLbeWZqkHbpxybJzmPkT7+k0Qgxi1S3Nb+wCaJRDxDNjbopIJe7LpWuTJX3LK/2FDqBscyrcSTCutFe9xi3BnULaVGWVJJlBKZLJexll6TpyOxX4j+u/2sX93Hq90qxQECge+7AhQ9fQFtSp/excgj8mwdJJik9CnV2Z5VWS/rllFAczeckaeF72YlvbQY+MpsywGba3ZsnNm1kDLS32yucCr/eyhptq60dZlXudG0nZx+H/+OUNd9mL3RJzt0KE+/x+5YFtICKujWlFXvP4zXMFSFUN1+QuMGDUAknjWgRachOAdcg7eMLHh9/+M3m279eSBJizyA3OrWjeUe9mQIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $money = ($amount / 100);
        $request = new \AlipayFundTransToaccountTransferRequest();
        $request->setBizContent("{" .
            "\"out_biz_no\":\"$order_no\"," .
            "\"payee_type\":\"ALIPAY_LOGONID\"," .
            "\"payee_account\":\"$tel\"," .
            "\"amount\":\"$money\"," .
            "\"payer_show_name\":\"提现成功\"," .
            "\"payee_real_name\":\"$re_user_name\"," .
            "\"remark\":\"任务广场余额提现\"" .
            "}");
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultdata['code'] = $result->$responseNode->code;
        $resultdata['sub_msg'] = $result->$responseNode->sub_msg;
        print_r($resultdata);
        die();
    }
}