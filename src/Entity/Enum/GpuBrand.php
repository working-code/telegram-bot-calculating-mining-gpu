<?php

namespace App\Entity\Enum;

enum GpuBrand: int
{
    case Nvidia = 1;
    case Amd = 2;
    case Intel = 3;
}
