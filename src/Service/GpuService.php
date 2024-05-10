<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\GpuDTO;
use App\Entity\Gpu;
use App\Exception\ValidationErrorException;
use App\Manager\GpuManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class GpuService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GpuManager             $gpuManager,
        private ValidatorInterface     $validator,
    ) {
    }

    public function getGpuManager(): GpuManager
    {
        return $this->gpuManager;
    }

    /**
     * @return Gpu[]
     */
    public function getListGpuByBrand($brand): array
    {
        $gpuRepository = $this->em->getRepository(Gpu::class);

        return $gpuRepository->findBy(['brand' => $brand], ['name' => 'ASC']);
    }

    /**
     * @return Gpu[]
     */
    public function getListGpuByIds(array $ids): array
    {
        $gpuRepository = $this->em->getRepository(Gpu::class);

        return $gpuRepository->findBy(['id' => $ids]);
    }

    /**
     * @throws ValidationErrorException
     */
    public function createGpuByGpuDTO(GpuDTO $gpuDTO, $flush = true): Gpu
    {
        $gpu = $this->gpuManager->create($gpuDTO->getName(), $gpuDTO->getBrand(), $gpuDTO->getAlias());
        $errors = $this->validator->validate($gpu);

        if ($errors->count()) {
            throw new ValidationErrorException($errors);
        }

        if ($flush) {
            $this->gpuManager->emFlush();
        }

        return $gpu;
    }

    /**
     * @return Gpu[]
     */
    public function getAllGpuWithAlias(): array
    {
        $gpuRepository = $this->em->getRepository(Gpu::class);

        return $gpuRepository->getAllWithAlias();
    }

    /**
     * @return Gpu[]
     */
    public function getAllGpuWithWorks(): array
    {
        $gpuRepository = $this->em->getRepository(Gpu::class);

        return $gpuRepository->getAllGpuWithWorks();
    }

    /**
     * @return string[]
     */
    public function getAllGpuAlias(): array
    {
        $gpuRepository = $this->em->getRepository(Gpu::class);

        return $gpuRepository->getAllGpuAlias();
    }
}
