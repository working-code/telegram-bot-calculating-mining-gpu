<?php
declare(strict_types=1);

namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserManager
{
    use ManagerTrait;

    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(int $telegramId, ?string $userName, ?string $firstName, ?string $type): User
    {
        $user = (new User())
            ->setTelegramId($telegramId)
            ->setUserName($userName)
            ->setFirstName($firstName)
            ->setType($type);
        $this->em->persist($user);

        return $user;
    }
}
