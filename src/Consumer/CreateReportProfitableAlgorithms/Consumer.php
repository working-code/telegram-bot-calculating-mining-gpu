<?php
declare(strict_types=1);

namespace App\Consumer\CreateReportProfitableAlgorithms;

use App\Consumer\CreateReportProfitableAlgorithms\Input\Message;
use App\Helper\TelegramHelper;
use App\Report\ProfitableAlgorithmsReport;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

readonly class Consumer implements ConsumerInterface
{
    public function __construct(
        private EntityManagerInterface     $em,
        private SerializerInterface        $serializer,
        private ValidatorInterface         $validator,
        private ProfitableAlgorithmsReport $profitableAlgorithmsReport,
        private TelegramHelper             $telegramHelper,
    ) {
    }

    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = $this->serializer->deserialize($msg->getBody(), Message::class, 'json');
            $errors = $this->validator->validate($message);

            if ($errors->count() > 0) {
                return $this->reject((string)$errors);
            }

            $report = $this->profitableAlgorithmsReport->getReportByTelegramId($message->getTelegramId());
            $this->telegramHelper->sendMessages($report, $message->getTelegramId());
        } catch (Throwable $exception) {
            $this->reject($exception->getMessage());
        } finally {
            $this->em->clear();
            $this->em->getConnection()->close();
        }

        return static::MSG_ACK;
    }

    private function reject(string $error): int
    {
        echo "Incorrect message: $error";

        return self::MSG_REJECT;
    }
}
