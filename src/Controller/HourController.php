<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use App\Form\AddHoursClassType;
use App\Business\HourManager;
use App\Form\HourModType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManager;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
class HourController extends AbstractController
{
    private HourManager       $hourManager;
    private ProjectRepository $projectRepository;

    public function __construct(HourManager $hourManager, ProjectRepository $projectRepository)
    {
        $this->hourManager       = $hourManager;
        $this->projectRepository = $projectRepository;
    }

    #[Route('projects/{project}/new', name: 'app_newHours')]
    public function createNewHours(Request $request, $project): Response
    {
        $pjn       = html_entity_decode($project);
        $hourEntry = new ProjectHours();

        $pj = $this->projectRepository->findByUserAndName($this->getUser(), $pjn);
        $hourEntry->setProject($pj);

        $form = $this->createForm(AddHoursClassType::class, $hourEntry);
        $form->handleRequest($request);

        $handled = $this->hourManager->handleAddHoursForm($form, $hourEntry, $pj);

        switch ($handled) {
            case 'notHandled':
                $this->addFlash('error', 'End time must be later than start time!');
                break;
            case 'handled':
                echo $pj->getId();
                return $this->redirectToRoute('app_project', ['projectId' => $pj->getId()]);
        }

        return $this->render('add_hours/index.html.twig', [
            'hours_form' => $form->createView(),
            'projectId'  => $pj->getId()
        ]);
    }

    /**
     * renders are you sure to delete this hour entry page
     *
     * @param int $projectId
     * @param int $hoursId
     * @return Response
     * @Route("projects/{projectId}/{hoursId}/delete", name="app_hour_delete")
     */
    public function DeleteHours(int $projectId, int $hoursId): Response
    {
        $hour = $this->getDoctrine()->getManager()->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        return $this->render('deletion/deleteHour.html.twig', [
            'projectId' => $projectId,
            'hour'      => $hour
        ]);
    }

    /**
     * Retrieves the specific hour to be deleted and deletes it
     * @Route("projects/{projectId}/{hoursId}/deletedHour", name="app_hour_deleted", defaults={"pj" = "DELETED_HOUR"})
     */
    public function DeletedHours(int $projectId, int $hoursId): RedirectResponse
    {
        $em   = $this->getDoctrine()->getManager();
        $hour = $em->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        $em->remove($hour);
        $em->flush();
        return $this->redirectToRoute('app_project', ['projectId' => $projectId]);
    }


    /*
     * retrieves this project and hour entry
     * creates form based on ProjectHours
     * when form is submitted and valid
     * checks if end time is later than start time, if not; flashes an error
     * otherwise, calculates time difference and sets it as duration on the hour entry
     * adds the hour entry to the project
     * saves all to the database
     */

    /**
     * @Route("projects/{pjn}/{hkey}/modify", name="app_projectHourMod")
     */

    public function ModifyHours($pjn, $hkey, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $hour_entry = $em->getRepository(ProjectHours::class)->findOneBy(['id' => $hkey]);
        $pj = $em->getRepository(Project::class)->findOneBy(['name' => $pjn, 'user' => $this->getUser()]);

        $timeStart = $hour_entry->getTimestampStart();
        $timeEnd = $hour_entry->getTimestampEnd();

        $this->hourManager->formatTimeStartAndEnd($timeStart, $timeEnd, $hour_entry);

        $form = $this->createForm(HourModType::class, $hour_entry);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $start = $hour_entry->getTimestampStart();
            $end = $hour_entry->getTimestampEnd();

            if ($end > $start) {
                $interval = $start->diff($end);

                $hour_entry->setDuration($interval);
                $pj->addProjectHours($hour_entry);


                //store to database
                $em = $this->getDoctrine()->getManager();
                $em->persist($hour_entry);
                $em->persist($pj);
                $em->flush();
                return $this->redirectToRoute('app_project', ['projectId' => $pj->getId()]);
            } else {
                $this->addFlash('error', 'End time must be later than start time!');
            }


        }

        return $this->render('modification/modHours.html.twig', [
            'hours_form' => $form->createView(),
            'projectId' => $pj->getId()
        ]);
    }
}
