<?php

namespace app\shop\controller\pay;

use app\common\controller\Fun;
use fast\Http;
use think\Db;
use app\shop\controller\Base;
use app\common\controller\Hm;

/**
 * USDT支付类
 */
class Usdtpay extends Base {
    
    public function pay($order,$goods){
        
        $this->redirect("/shop/pay.usdtpay/usdtpay/order_no/" . $order['order_no']);

    }
    
    public function usdtPay($order_no){
        
        $usdtpay_info = db::name('pay')->where(['type' => 'usdtpay'])->find();
        $usdtpay_info = json_decode($usdtpay_info['value'], true);
        
        $order_info = db::name('order')->where(['order_no' => $order_no])->find();

        if(empty($order_info) || $order_info['status'] == 'yiguoqi'){
            return "订单不存在或者订单已过期！";
        } 
        
        $this->assign([
            'trc_au_address' => $usdtpay_info['trc_au_address'],
            'trc_pay_address' => $usdtpay_info['trc_pay_address'],
            'order_no' => $order_info['order_no'],
            'amount' => $order_info['money'],
            'goods_num' => $order_info['goods_num'],
            'email' => $order_info['email'],
            'goods_name' => $order_info['goods_name'],
        ]);
        
        return view(ROOT_PATH . "public/content/template/" . $this->template_name .  "/usdtpay.html");
    }
    
    
}    

?>