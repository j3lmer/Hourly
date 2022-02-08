<?php


namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use App\Form\HourModType;
use App\Form\ProjectModType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModificationController extends AbstractController
{

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

        $this->formatTimeStartAndEnd($timeStart, $timeEnd, $hour_entry);

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


    //--------------------------------------------------------------------------------------------------------------
    /*
     * gets this project
     * creates a form if the project exists, otherwise it returns an error page
     * submits and changes database
     */

    /**
     * @Route("projects/{projectId}/modify", name="app_projectNameMod")
     */

    public function ModifyName($projectId, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $pj = $em->getRepository(Project::class)->findOneBy(['id' => $projectId, 'user' => $this->getUser()]);

        if ($pj) {
            $form = $this->createForm(ProjectModType::class, $pj);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                //store to database
                $em = $this->getDoctrine()->getManager();
                $em->persist($pj);
                $em->flush();
                return $this->redirectToRoute('app_project', ['projectId' => $pj->getId()]);
            }

            return $this->render('modification/modName.html.twig', [
                'name_form' => $form->createView(),
                'projectId' => $pj->getId()
            ]);
        } else {
            return $this->render('error/project_not_found.html.twig');
        }
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     * simple function to remove the seconds from the timestamp start and end in the modify page
     */

    function formatTimeStartAndEnd($timeStart, $timeEnd, $hour_entry)
    {
        $timeStartFormatted = $timeStart->format('Y-m-d H:i');
        $timeEndFormatted = $timeEnd->format('Y-m-d H:i');

        $dateTimeStartFormatted = new DateTime($timeStartFormatted);
        $dateTimeEndFormatted = new DateTime($timeEndFormatted);

        $hour_entry->setTimestampStart($dateTimeStartFormatted);
        $hour_entry->setTimestampEnd($dateTimeEndFormatted);
    }
}
