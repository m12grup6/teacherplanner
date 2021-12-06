<?php

namespace App\Controller;

use App\Entity\Constraint;
use App\Repository\ConstraintRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/constraints")
 */
class ConstraintController extends AbstractController
{

    public function __construct(ConstraintRepository $constraintRepository, EntityManagerInterface $entityManager)
    {
        $this->constraintRepository = $constraintRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/{id}/remove", name="app_removeConstraint")
     * Mètode per esborrar franja de restricció al professor
     * @param Integer $id id del constraint a esborrar.
     */
    public function removeConstraint($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $target = $entityManager->find(Constraint::class, $id);
        $status = 404;
        $msg = 'Not found';
        if(!is_null($target)) {
            $entityManager->remove($target);
            $entityManager->flush();
            $status = 200;
            $msg = 'Constraint Deleted';
        }
        return new Response($msg, $status, array('Content-Type' => 'text/html'));
    }

}
