<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use App\Form\AddHoursClassType;
use App\Business\HourManager;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManager;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddHoursController
 *
 * gets the project from the url and replaces %20 with spaces
 * creates new hour entry
 * sets default timestamps to now
 * retrieves the project this hour entry would be linked to and links it
 * creates form based on ProjectHours
 *
 * when form is submitted and is valid, checks if end time is later than start time, if not; flashes an error
 * calculates time difference between start and end time and puts it as duration in this hour entry
 * adds this hour entry to the project class
 * adds to the database
 *
 * @package App\Controller
 */
class AddHoursController extends AbstractController
{
    private HourManager $hourManager;
    private ProjectRepository $projectRepository;

    public function __construct(HourManager $hourManager, ProjectRepository $projectRepository)
    {
        $this->hourManager = $hourManager;
        $this->projectRepository = $projectRepository;
    }

    #[Route('projects/{project}/new', name: 'app_newHours')]
    public function createNewHours(Request $request, $project): Response
    {
        $pjn = html_entity_decode($project);
        $hourEntry = new ProjectHours();

        $pj = $this->projectRepository->findByUserAndName($this->getUser(), $pjn);
        $hourEntry->setProject($pj);

        $form = $this->createForm(AddHoursClassType::class, $hourEntry);
        $form->handleRequest($request);

        $handled = $this->hourManager->handleAddHoursForm($form, $hourEntry, $pj);

        switch($handled){
            case 'notHandled':
                $this->addFlash('error', 'End time must be later than start time!');
                break;
            case 'handled':
                echo $pj->getId();
                return $this->redirectToRoute('app_project', ['projectId' => $pj->getId()]);
        }

        return $this->render('add_hours/index.html.twig', [
            'hours_form' => $form->createView(),
            'projectId' => $pj->getId()
        ]);
    }
}
