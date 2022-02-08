<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\MakeProjectFormClassType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormController extends AbstractController
{

    /*
     * creates new project and retrieves this user
     * creates form based on Project
     * when form is submitted and valid, checks if there are other projects on this account with the same name, if so; flashes an error
     * otherwise adds project to the database
     */
    #[Route('/NewProject', name: 'app_newproject')]
    public function index(Request $request): Response
    {
        $pj = new Project();
        $pj->setUser($this->getUser());

        $form = $this->createForm(MakeProjectFormClassType::class, $pj);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $potentialSameNameProjects = $this->getDoctrine()->getRepository(Project::class)->findBy([
                'user' => $this->getUser(),
                'name' => $pj->getName()
            ]);

            if (!empty($potentialSameNameProjects)) {
                //display message that projects cant have the same name
                $this->addFlash('error', 'Project cant have same name as other projects on your account');
            } else {
                //store to database
                $em = $this->getDoctrine()->getManager();
                $em->persist($pj);
                $em->flush();
                return $this->redirectToRoute('app_mainscreen');

            }
        }

        return $this->render('make_project_form/index.html.twig', [
            'post_form' => $form->createView()
        ]);
    }
}
