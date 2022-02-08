<?php
declare(strict_types=1);

namespace App\Business;

use App\Entity\Project;

class ProjectManager
{

    //--------------------------------------------------------------------------------------------------------------
    /*
     * adds an extra variable to each entry with its duration in a nice format to display
     */

    public function configureEntryDisplayDuration($entry)
    {
        foreach ($entry as $e => $data) {
            $start = $data->getTimestampStart();
            $end = $data->getTimestampEnd();
            $interval = $start->diff($end);

            $data->entryDuration = ($interval->days * 24) + $interval->h . "h, " . $interval->i . "m";
        }
    }


    //--------------------------------------------------------------------------------------------------------------
    /*
     * simple function to check if a project with this name exists under this user
     */

    public function checkIfUserExists($pjn, $em, $user): bool
    {
        if ($em->getRepository(Project::class)->findOneBy(['user' => $user, 'name' => $pjn])) {
            return true;
        } else {
            return false;
        }
    }


    //--------------------------------------------------------------------------------------------------------------


    /**
     * simple function which expects a project variable
     * and outputs the calculation of its total amount of hours combined
     *
     * @param Project $project
     * @return string
     */
    public function getTotalHours(Project $project)
    {
        $total_hour = [0, 0];

        $hours = $project->getProjectHours()->toArray();

        foreach ($hours as $hour => $data) {
            $start = $data->getTimestampStart();
            $end = $data->getTimestampEnd();
            $interval = $start->diff($end);

            $total_hour[0] += ($interval->days * 24) + $interval->h;
            $total_hour[1] += $interval->i;
        }

        return $total_hour[0] . "h, " . $total_hour[1] . "m";
    }

}
