<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\GpuDTO;
use App\DTO\RigDTO;
use App\DTO\UserDTO;
use App\Entity\Rig;
use App\Exception\NotFoundException;
use App\Exception\ValidationErrorException;
use App\Manager\RigItemManager;
use App\Manager\RigManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class RigService
{
    public function __construct(
        private RigManager             $rigManager,
        private UserService            $userService,
        private ValidatorInterface     $validator,
        private EntityManagerInterface $em,
        private GpuService             $gpuService,
        private RigItemManager         $rigItemManager,
    ) {
    }

    /**
     * @throws NotFoundException
     */
    public function getInfoByAllRigsByTelegramId(int $telegramId): string
    {
        $rigs = $this->getRigsByTelegramId($telegramId);

        if (!$rigs) {
            throw new NotFoundException('Rigs not found');
        }

        $report = sprintf("%s\n\n", '*Отчет по ригам.*');

        foreach ($rigs as $rig) {
            $report .= sprintf(
                "Название: *%s*\nСтоимость электроэнергии: *%.2f руб*\nКПД БП: *%d%%*\nПотребление тушки: *%d ватт*\n",
                $rig->getName(),
                $rig->getElectricityCost(),
                $rig->getPowerSupplyEfficiency(),
                $rig->getMotherboardConsumption(),
            );

            if ($rig->getItems()->count() === 0) {
                $report .= "Видеокарт *нет*\n";
            } else {
                $report .= "*Видеокарты:*\n";

                foreach ($rig->getItems() as $item) {
                    $report .= sprintf(
                        "%s x %d\n",
                        $item->getGpu()->getName(),
                        $item->getCount()
                    );
                }
            }

            $report .= "\n";
        }

        return $report;
    }

    /**
     * @return Rig[]
     */
    public function getRigsByTelegramId(int $telegramId): array
    {
        $rigRepository = $this->em->getRepository(Rig::class);

        return $rigRepository->findByTelegramId($telegramId);
    }

    /**
     * @throws ValidationErrorException
     */
    public function createRigBy(RigDTO $rigDTO, UserDTO $userDTO): void
    {
        $user = $this->userService->findUserByTelegramId($userDTO->getId());

        if (!$user) {
            $user = $this->userService->createUserByUserDTO($userDTO);
        }

        $rig = $this->rigManager->create(
            $rigDTO->getName(),
            $rigDTO->getElectricityCost(),
            $rigDTO->getPowerSupplyEfficiency(),
            $rigDTO->getMotherboardConsumption()
        );
        $rig->setUser($user);
        $user->addRig($rig);

        $errors = $this->validator->validate($rig);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        $this->rigManager->emFlush();
    }

    /**
     * @param GpuDTO[] $gpuList
     *
     * @throws NotFoundException
     */
    public function addListGpuInRig(array $gpuList, int $rigId): void
    {
        $rig = $this->getRigById($rigId);

        if (!$rig) {
            throw new NotFoundException(sprintf('Rig not found with id "%s"', $rigId));
        }

        $gpuIds = array_map(static fn (GpuDTO $gpuDTO) => $gpuDTO->getId(), $gpuList);
        $gpuListCount = array_map(static fn (GpuDTO $gpuDTO) => $gpuDTO->getCount(), $gpuList);
        $gpuListCount = array_combine($gpuIds, $gpuListCount);
        $gpuEntityGpuList = $this->gpuService->getListGpuByIds($gpuIds);

        foreach ($gpuEntityGpuList as $gpu) {
            $this->rigItemManager->create($rig, $gpu, $gpuListCount[$gpu->getId()]);
        }

        $this->rigItemManager->emFlush();
    }

    private function getRigById(int $rigId): ?Rig
    {
        $rigRepository = $this->em->getRepository(Rig::class);

        return $rigRepository->find($rigId);
    }
}
