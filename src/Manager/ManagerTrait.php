<?php

namespace App\Manager;

trait ManagerTrait
{
    public function emFlush(): void
    {
        $this->em->flush();
    }
}
