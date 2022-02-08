<?php
declare(strict_types=1);

namespace App\Controller;

use App\Business\ProjectManager;
use App\Entity\Project;
use App\Form\ProjectModType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{

    private ProjectManager    $projectManager;
    private ProjectRepository $projectRepository;

    public function __construct(ProjectManager $projectManager, ProjectRepository $projectRepository)
    {
        $this->projectManager    = $projectManager;
        $this->projectRepository = $projectRepository;
    }

    //--------------------------------------------------------------------------------------------------------------
    /*
     * if user exists, loads all hour entries for this project.
     * displays total amount of hours, else displays an error
     */

    /**
     * @Route("projects/{projectId}", name="app_project")
     */
    public function ProjectPage($projectId): Response
    {
        $em   = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $project = $this->projectRepository->find($projectId);
        $projectName = $project->getName();

        $exists = $this->projectManager->checkIfUserExists($projectName, $em, $user);

        $project = $em->getRepository(Project::class)->findOneBy(['name' => $projectName, 'user' => $user]);
        if ($exists) {
            $totalHours = $this->projectManager->getTotalHours($project);

            $hourEntry = $project->getProjectHours()->toArray();

            $this->projectManager->configureEntryDisplayDuration($hourEntry);

            return $this->render('project.html.twig', [
                'projectName' => $projectName,
                'projectId'   => $projectId,
                'hours'       => $hourEntry,
                'totalHours'  => $totalHours
            ]);
        } else {
            return $this->render('error/project_not_found.html.twig');
        }
    }


    /**
     * Renders are you sure to delete this project page
     *
     * @param int $projectId
     * @return Response
     *
     * @Route("projects/{projectId}/delete", name="app_delete")
     */
    public function DeleteProject(int $projectId): Response
    {
        if ($project = $this->getDoctrine()->getRepository(Project::class)->findOneBy([
            'id'   => $projectId,
            'user' => $this->getUser()
        ])) {
            return $this->render('deletion/delete.html.twig', [
                'projectName' => $project->getName(),
                'projectId'   => $projectId
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
    public function DeletedProject(int $projectId): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $pj = $em->getRepository(Project::class)->findOneBy(['user' => $this->getUser(), 'id' => $projectId]);

        $em->remove($pj);
        $em->flush();

        return $this->redirectToRoute('app_mainscreen');
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

}
