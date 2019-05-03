<?php

namespace Common\ORG\Library;

use AliyunMNS\Client;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Requests\SendMessageRequest;

/**
 * 阿里云消息服务
 * Class AliyunMNSApi
 * @package extend\library
 */
class AliyunMNSApi
{

    private $accessId;
    private $accessKey;
    private $endPoint;
    private $client;
    private $queueName;

    public function __construct($queueName)
    {
        $config = D('Config')->queryConfigByGroup('ALIYUN', 'MNS');
        $this->accessId = $config['AccessKeyID'];
        $this->accessKey = $config['AccessKeySecret'];
        $this->endPoint = $config['Endpoint'];
        $this->queueName = $queueName;
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
    }

    /**
     * 创建队列
     * @return \AliyunMNS\Responses\CreateQueueResponse
     * @throws \Exception
     */
    public function createQueue()
    {
        $request = new CreateQueueRequest($this->queueName);
        try {
            $res = $this->client->createQueue($request);
            return $res;
        } catch (MnsException $e) {
            echo "CreateQueueFailed: " . $e;
            throw new \Exception($e);
        }
    }

    /**
     * 发送消息
     * @param $messageBody  发送文本
     * @return \AliyunMNS\Responses\SendMessageResponse
     * @throws \Exception
     */
    public function sendMessage($messageBody)
    {
        $queue = $this->client->getQueueRef($this->queueName);
        $request = new SendMessageRequest($messageBody);
        try {
            $res = $queue->sendMessage($request);
            return $res;
        } catch (MnsException $e) {
            echo "SendMessage Failed: " . $e . "\n";
            echo "MNSErrorCode: " . $e->getMnsErrorCode() . "\n";
            throw new \Exception($e);
        }
    }


    /**
     * 创建队列
     */
    public function run()
    {
        $queueName = "CreateQueueAndSendMessageExample";

        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);

        // 1. create queue
        $request = new CreateQueueRequest($queueName);
        try {
            $res = $this->client->createQueue($request);
            echo "QueueCreated! \n";
        } catch (MnsException $e) {
            echo "CreateQueueFailed: " . $e;
            return;
        }
        $queue = $this->client->getQueueRef($queueName);

        // 2. send message
        $messageBody = "test";
        // as the messageBody will be automatically encoded
        // the MD5 is calculated for the encoded body
        $bodyMD5 = md5(base64_encode($messageBody));
        $request = new SendMessageRequest($messageBody);
        try {
            $res = $queue->sendMessage($request);
            echo "MessageSent! \n";
        } catch (MnsException $e) {
            echo "SendMessage Failed: " . $e;
            return;
        }

        // 3. receive message
        $receiptHandle = NULL;
        try {
            // when receiving messages, it's always a good practice to set the waitSeconds to be 30.
            // it means to send one http-long-polling request which lasts 30 seconds at most.
            $res = $queue->receiveMessage(30);
            echo "ReceiveMessage Succeed! \n";
            if (strtoupper($bodyMD5) == $res->getMessageBodyMD5()) {
                echo "You got the message sent by yourself! \n";
            }
            $receiptHandle = $res->getReceiptHandle();
        } catch (MnsException $e) {
            echo "ReceiveMessage Failed: " . $e;
            return;
        }

        // 4. delete message
        try {
            $res = $queue->deleteMessage($receiptHandle);
            echo "DeleteMessage Succeed! \n";
        } catch (MnsException $e) {
            echo "DeleteMessage Failed: " . $e;
            return;
        }
    }

    /**
     * 删除队列
     * @param $queueName
     */
    public function deleteQueue($queueName)
    {
        try {
            $this->client->deleteQueue($queueName);
            echo "DeleteQueue Succeed! \n";
        } catch (MnsException $e) {
            echo "DeleteQueue Failed: " . $e;
            return;
        }
    }

}