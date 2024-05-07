<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\ValidationErrorException;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserService
{
    public function __construct(
        private UserManager            $userManager,
        private ValidatorInterface     $validator,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @throws ValidationErrorException
     */
    public function createUserByUserDTO(UserDTO $userDTO): User
    {
        $user = $this->userManager->create(
            $userDTO->getId(),
            $userDTO->getUserName(),
            $userDTO->getFirstName(),
            $userDTO->getType()
        );
        $errors = $this->validator->validate($user);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        return $user;
    }

    public function findUserByTelegramId(int $telegramId): ?User
    {
        $userRepository = $this->em->getRepository(User::class);

        return $userRepository->findOneBy(['telegramId' => $telegramId]);
    }
}
