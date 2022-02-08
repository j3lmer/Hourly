<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     * redirects the user from '/' to login page
     */

    /**
     * @Route("/", name="welcome")
     */
    public function Welcome()
    {
        return $this->redirectToRoute("app_login");
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     *gets each project and displays them + the linked hours
     */

    /**
     * @Route("/projects", name="app_mainscreen")
     */
    public function Main()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $projects = $em->getRepository(Project::class)->findBy(['user' => $user]);

        $hourList = [];

        foreach ($projects as $project) {
            $total_hour = $this->getTotalHours($project);
            array_push($hourList, $total_hour);
        }

        return $this->render('home.html.twig', [
            'projectlist' => $projects,
            'hourlist' => $hourList
        ]);
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     * if user exists, loads all hour entries for this project.
     * displays total amount of hours, else displays an error
     */

    /**
     * @Route("projects/{projectId}", name="app_project")
     */
    public function ProjectPage($projectId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $project = $this->projectRepository->find($projectId);
        dump($project);
        $projectName = $project->getName();

        $exists = $this->checkIfUserExists($projectName, $em, $user);

        $project = $em->getRepository(Project::class)->findOneBy(['name' => $projectName, 'user' => $user]);
        if ($exists) {
            $totalHours = $this->getTotalHours($project);

            $hourEntry = $project->getProjectHours()->toArray();

            $this->configureEntryDisplayDuration($hourEntry);

            return $this->render('project.html.twig', [
                'projectName' => $projectName,
                'projectId' => $projectId,
                'hours' => $hourEntry,
                'totalHours' => $totalHours
            ]);
        } else {
            return $this->render('error/project_not_found.html.twig');
        }
    }

    /**
     * simple function which expects a project variable
     * and outputs the calculation of its total amount of hours combined
     *
     * @param Project $project
     * @return string
     */
    private function getTotalHours(Project $project)
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

        $total_hour_string = $total_hour[0] . "h, " . $total_hour[1] . "m";

        return $total_hour_string;
    }


    //--------------------------------------------------------------------------------------------------------------
    /*
     * adds an extra variable to each entry with its duration in a nice format to display
     */

    function configureEntryDisplayDuration($entry)
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

    function checkIfUserExists($pjn, $em, $user)
    {
        if ($em->getRepository(Project::class)->findOneBy(['user' => $user, 'name' => $pjn])) {
            return true;
        } else {
            return false;
        }
    }


    //--------------------------------------------------------------------------------------------------------------

}
