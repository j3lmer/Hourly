<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectHours;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param int $projectId
     * @return Response
     *
     * @Route("projects/{projectId}/delete", name="app_delete")
     */
    public function DeleteProject($projectId): Response
    {
        if ($project = $this->getDoctrine()->getRepository(Project::class)->findOneBy([
            'id' => $projectId,
            'user' => $this->getUser()
        ])) {
            return $this->render('deletion/delete.html.twig', [
                'projectName' => $project->getName(),
                'projectId' => $projectId
            ]);
        }
        return $this->render('error/project_not_found.html.twig');

    }

    /**
     * Retrieves the specific project to be deleted and removes it
     *
     * @param int $projectId
     * @return RedirectResponse
     *
     * @Route("projects/{projectId}/deleted", name="app_deleted", defaults={"pj" = "DELETED_PROJECT"})
     */
    public function DeletedProject($projectId): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $pj = $em->getRepository(Project::class)->findOneBy(['user' => $this->getUser(), 'id' => $projectId]);

        $em->remove($pj);
        $em->flush();

        return $this->redirectToRoute('app_mainscreen');
    }

    /**
     * renders are you sure to delete this hour entry page
     *
     * @param int $projectId
     * @param int $hoursId
     * @return Response
     * @Route("projects/{projectId}/{hoursId}/delete", name="app_hour_delete")
     */
    public function DeleteHours($projectId, $hoursId): Response
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
    public function DeletedHours($projectId, $hoursId): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $hour = $em->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        $em->remove($hour);
        $em->flush();
        return $this->redirectToRoute('app_project', ['projectId' => $projectId]);
    }
}
