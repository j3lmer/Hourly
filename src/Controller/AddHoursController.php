<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use App\Form\AddHoursClassType;
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
    #[Route('projects/{project}/new', name: 'app_newHours')]
    public function index(Request $request, $project): Response
    {
        $em = $this->getDoctrine()->getManager();
        $pjn = html_entity_decode($project);

        $hour_entry = new ProjectHours();

        $pj = $em->getRepository(Project::class)->findOneBy(['name' => $pjn, 'user' => $this->getUser()]);
        $hour_entry->setProject($pj);

        $form = $this->createForm(AddHoursClassType::class, $hour_entry);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $start = $hour_entry->getTimestampStart();
            $end = $hour_entry->getTimestampEnd();

            if ($end > $start) {
                $interval = $start->diff($end);

                $hour_entry->setDuration($interval);
                $hour_entry->setProject($pj);
                $pj->addProjectHours($hour_entry);

                //store to database
                $em->persist($hour_entry);
                $em->persist($pj);
                $em->flush();
                return $this->redirectToRoute('app_project', ['projectname' => $pj->getName()]);
            } else {
                $this->addFlash('error', 'End time must be later than start time!');
            }
        }

        return $this->render('add_hours/index.html.twig', [
            'hours_form' => $form->createView(),
            'projectname' => $pjn
        ]);
    }
}
