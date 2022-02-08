<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProjectHours
 * @ORM\Entity(repositoryClass="App\Repository\ProjectHoursRepository")
 */
class ProjectHours
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp_start;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp_end;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="project_hours")
     * @ORM\JoinColumn(name="project_id", nullable=false, referencedColumnName="id", onDelete="CASCADE")
     */
    private $project;

    /**
     * @ORM\Column(type="dateinterval")
     */
    private $duration;

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration): void
    {
        $this->duration = $duration;
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
    public function getTimestampStart()
    {
        return $this->timestamp_start;
    }

    /**
     * @param mixed $timestamp_start
     */
    public function setTimestampStart($timestamp_start): void
    {
        $this->timestamp_start = $timestamp_start;
    }

    /**
     * @return mixed
     */
    public function getTimestampEnd()
    {
        return $this->timestamp_end;
    }

    /**
     * @param mixed $timestamp_end
     */
    public function setTimestampEnd($timestamp_end): void
    {
        $this->timestamp_end = $timestamp_end;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project): void
    {
        $project->addProjectHours($this);

        $this->project = $project;
    }
}
