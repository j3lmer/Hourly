<?php
declare(strict_types=1);

namespace App\Business;

use App\Entity\Project;
use App\Entity\ProjectHours;
use App\Repository\ProjectHoursRepository;
use App\Repository\ProjectRepository;
use DateTime;
use Symfony\Component\Form\FormInterface;

class HourManager
{
    private ProjectHoursRepository $hoursRepository;
    private ProjectRepository      $projectRepository;

    public function __construct(ProjectHoursRepository $hoursRepository, ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->hoursRepository   = $hoursRepository;
    }

    public function handleAddHoursForm(FormInterface $form, ProjectHours $hourEntry, Project $project): ?string
    {

        if ($form->isSubmitted() && $form->isValid()) {
            $start = $hourEntry->getTimestampStart();
            $end   = $hourEntry->getTimestampEnd();

            if ($end > $start) {
                $interval = $start->diff($end);

                $hourEntry->setDuration($interval);
                $hourEntry->setProject($project);
                $project->addProjectHours($hourEntry);

                //store to database
                $this->hoursRepository->persist($hourEntry);
                $this->projectRepository->persist($project);
                $this->hoursRepository->flush();
                $this->projectRepository->flush();
                return 'handled';
            } else {
                return 'notHandled';
            }
        }
        return null;
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     * simple function to remove the seconds from the timestamp start and end in the modify page
     */

    public function formatTimeStartAndEnd($timeStart, $timeEnd, $hour_entry): void
    {
        $timeStartFormatted = $timeStart->format('Y-m-d H:i');
        $timeEndFormatted   = $timeEnd->format('Y-m-d H:i');

        $dateTimeStartFormatted = new DateTime($timeStartFormatted);
        $dateTimeEndFormatted   = new DateTime($timeEndFormatted);

        $hour_entry->setTimestampStart($dateTimeStartFormatted);
        $hour_entry->setTimestampEnd($dateTimeEndFormatted);
    }

}
