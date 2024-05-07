<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\WorkDTO;
use App\Entity\Gpu;
use App\Entity\Work;
use App\Exception\ValidationErrorException;
use App\Manager\WorkItemManager;
use App\Manager\WorkManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class WorkService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface     $validator,
        private WorkManager            $workManager,
        private WorkItemManager        $workItemManager,
    ) {
    }

    /**
     * @throws ValidationErrorException
     */
    public function createWorkByWorkDTO(WorkDTO $workDTO, $flush = true): Work
    {
        $work = $this->workManager->create($workDTO->getGpu(), $workDTO->getAlias());

        foreach ($workDTO->getItems() as $workItemDTO) {
            $workItem = $this->workItemManager->create(
                $work,
                $workItemDTO->getCoin(),
                $workItemDTO->getAlias(),
                $workItemDTO->getHashRate(),
                $workItemDTO->getCount(),
                $workItemDTO->getPowerConsumption()
            );
            $work->addItem($workItem);
        }

        $errors = $this->validator->validate($work);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->workManager->emFlush();
        }

        return $work;
    }

    /**
     * @throws ValidationErrorException
     */
    public function updateWorkByWorkDTO(Work $work, WorkDTO $workDTO, $flush = true): Work
    {
        foreach ($workDTO->getItems() as $newAlias => $workItemDTO) {
            if ($work->getItems()->containsKey($newAlias)) {
                $workItem = $work->getItems()->get($newAlias);
                $workItem
                    ->setHashRate($workItemDTO->getHashRate())
                    ->setCount($workItemDTO->getCount())
                    ->setPowerConsumption($workItemDTO->getPowerConsumption());
            } else {
                $workItem = $this->workItemManager->create(
                    $workItemDTO->getWork(),
                    $workItemDTO->getCoin(),
                    $workItemDTO->getAlias(),
                    $workItemDTO->getHashRate(),
                    $workItemDTO->getCount(),
                    $workItemDTO->getPowerConsumption()
                );
                $errors = $this->validator->validate($workItem);

                if ($errors->count()) {
                    throw new ValidationErrorException($errors);
                }
            }
        }

        if ($flush) {
            $this->workManager->emFlush();
        }

        return $work;
    }

    /**
     * @return Work[]
     */
    public function getWorksWithItemByGpu(Gpu $gpu): array
    {
        $workRepository = $this->em->getRepository(Work::class);

        return $workRepository->getWorksWithItemByGpu($gpu);
    }
}
