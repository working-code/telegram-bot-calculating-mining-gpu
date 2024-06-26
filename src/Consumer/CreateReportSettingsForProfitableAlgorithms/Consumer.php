<?php
declare(strict_types=1);

namespace App\Consumer\CreateReportSettingsForProfitableAlgorithms;

use App\Consumer\CreateReportSettingsForProfitableAlgorithms\Input\Message;
use App\Exception\NotFoundException;
use App\Helper\TelegramHelper;
use App\Report\SettingsForProfitableAlgorithmsReport;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;

readonly class Consumer implements ConsumerInterface
{
    public function __construct(
        private EntityManagerInterface                $em,
        private SerializerInterface                   $serializer,
        private ValidatorInterface                    $validator,
        private SettingsForProfitableAlgorithmsReport $settingsForProfitableAlgorithmsReport,
        private TelegramHelper                        $telegramHelper,
    ) {
    }

    /**
     * @throws TelegramSDKException
     */
    public function execute(AMQPMessage $msg): int
    {
        try {
            $message = $this->serializer->deserialize($msg->getBody(), Message::class, 'json');
            $errors = $this->validator->validate($message);

            if ($errors->count() > 0) {
                return $this->reject((string)$errors);
            }

            $report = $this->settingsForProfitableAlgorithmsReport->getReportByRigId($message->getRigId());
            $this->telegramHelper->sendMessages($report, $message->getTelegramId());
        } catch (NotFoundException $exception) {
            $this->telegramHelper->sendMessage($exception->getMessage(), $message->getTelegramId());
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
