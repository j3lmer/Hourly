<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Project
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", nullable=false, referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectHours", mappedBy="project", cascade={"persist", "remove"})
     */
    private $project_hours;

    /**
     * @ORM\Column(type="string")
     */
    private $comment;

    public function addProjectHours(ProjectHours $ph)
    {
        $this->project_hours[] = $ph;
    }

    /**
     * @return ArrayCollection|ProjectHours[]
     */
    public function getProjectHours()
    {
        return $this->project_hours;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user): void
    {
        $user->addProject($this);

        $this->user = $user;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function __construct()
    {
        $this->project_hours = new ArrayCollection();
    }
}
