<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Project;
use Symfony\Component\Security\Core\User\UserInterface;

class ProjectRepository extends AbstractRepository
{
    public function findByUserAndName(UserInterface $user ,string $projectName): Project
    {
        return parent::findOneBy(['name' => $projectName, 'user' => $user]);
    }

    protected function getEntityClassName(): string
    {
        return Project::class;
    }
}
