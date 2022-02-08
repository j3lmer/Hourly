<?php

namespace App\Controller;

use App\Business\ProjectManager;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    private ProjectManager    $projectManager;


    public function __construct(ProjectManager $projectManager)
    {
        $this->projectManager = $projectManager;
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

            $total_hour = $this->projectManager->getTotalHours($project);
            $hourList[] = $total_hour;
        }

        return $this->render('home.html.twig', [
            'projectlist' => $projects,
            'hourlist' => $hourList
        ]);
    }






}
