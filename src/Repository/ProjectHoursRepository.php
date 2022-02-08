<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\ProjectHours;

class ProjectHoursRepository extends AbstractRepository
{

    protected function getEntityClassName(): string
    {
        return ProjectHours::class;
    }
}
