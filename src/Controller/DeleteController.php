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
        $em = $this->getDoctrine()->getManager();
        $hour = $em->getRepository(ProjectHours::class)->findOneBy(['id' => $hoursId]);

        $em->remove($hour);
        $em->flush();
        return $this->redirectToRoute('app_project', ['projectId' => $projectId]);
    }
}
