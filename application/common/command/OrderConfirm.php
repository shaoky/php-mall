<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\admin\model\Order;
use think\Db;


class OrderConfirm extends Command
{
    protected function configure()
    {
        $this->setName('orderconfirm')
            ->setDescription('自动收货');
    }

    protected function execute(Input $input, Output $output)
    {


        $order = model('app\h5\model\Common');
        $webInfo = Db::table('tp_web_config')->find();
        $time = time() - $webInfo['autoConfirmDelivery'] * 24 * 60 * 60;
        $orders = Db::table('tp_order')->where([
            ['orderStatus','=',3],
            ['deliveryTime','<',$time],
        ])->column('orderId');
        for ($i = 0;$i<count($orders);$i++){
            $order->setOrderConfirm($orders[$i]);
        }
        $output->writeln(print_r($orders));

    }
}
