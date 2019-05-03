<?php
namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use app\admin\model\Order;

class Settlement extends Command
{
    protected function configure()
    {
        $this->setName('settlement')
            ->setDescription('每日12点结算收货订单');
    }

    protected function execute(Input $input, Output $output)
    {
        $commissions = model('app\admin\model\Order');

        $messge = $commissions->settlementCommission();

        $output->writeln(print_r($messge));
    }
}
