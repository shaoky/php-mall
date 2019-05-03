<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\admin\model\Order;
use think\Db;


class OrderClose extends Command
{
    protected function configure()
    {
        $this->setName('orderClose')
            ->setDescription('关闭订单');
    }

    protected function execute(Input $input, Output $output)
    {


        $order = model('app\admin\model\Order');
        $data = $order->orderClose();

        $output->writeln($data);

    }
}
