<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DeleteController
 * @package App\Controller
 */
class DeleteController extends AbstractController
{
    /**
     * Renders are you sure to delete this project page
     *
     * @param string $projectName
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("projects/{projectName}/delete", name="app_delete")
     */
    public function DeleteProject($projectName)
    {
        if ($this->getDoctrine()->getRepository(Project::class)->findOneBy(['name' => $projectName, 'user' => $this->getUser()])) {
            return $this->render('deletion/delete.html.twig', [
                'projectname' => $projectName
            ]);
        }
        return $this->render('error/project_not_found.html.twig');

    }

    /**
     * Retrieves the specific project to be deleted and removes it
     *
     * @param string $projectName
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("projects/{projectName}/deleted", name="app_deleted", defaults={"pj" = "DELETED_PROJECT"})
     */
    public function DeletedProject($projectName)
    {
        $em = $this->getDoctrine()->getManager();
        $pj = $em->getRepository(Project::class)->findOneBy(['user' => $this->getUser(), 'name' => $projectName]);

        $em->remove($pj);
        $em->flush();

        return $this->redirectToRoute('app_mainscreen');
    }

    /**
     * renders are you sure to delete this hour entry page
     *
     * @param string $projectName
     * @param int    $hoursId
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("projects/{projectName}/{hoursId}/delete", name="app_hour_delete")
     */
    public function DeleteHours($projectName, $hoursId)
    {
        $hour = $this->getDoctrine()->getManager()->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        return $this->render('deletion/deleteHour.html.twig', [
            'projectname' => $projectName,
            'hour' => $hour
        ]);
    }

    /**
     * Retrieves the specific hour to be deleted and deletes it
     * @Route("projects/{projectName}/{hoursId}/deletedHour", name="app_hour_deleted", defaults={"pj" = "DELETED_HOUR"})
     */
    public function DeletedHours($projectName, $hoursId)
    {
        $em = $this->getDoctrine()->getManager();
        $hour = $em->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        $em->remove($hour);
        $em->flush();
        return $this->redirectToRoute('app_project', ['projectname' => $hour->getProject()->getName()]);
    }
}
