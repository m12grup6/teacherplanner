<?php

declare(strict_types=1);

namespace App\Controller;
use App\Entity\Restriction;
use App\Form\RestrictionType;
use App\Repository\RestrictionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/restrictions") 
 */

class RestrictionController extends AbstractController
{
    public function __construct (RestrictionRepository $restrictionRepository, EntityManagerInterface $entityManager)
    {
        $this->restrictionRepository = $restrictionRepository;
        $this->entityManager = $entityManager;
    }

    /**
    * @Route("/add", name="app_addRestriction")
    * Mètode per afegir una restriccio i grabar-la a la BBDD.
    */    
    public function addRestriction(Request $request){
        $restriction = new Restriction();
        $form = $this->createForm(RestrictionType::class, $restriction);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            // ¿Pot ser que falti obtenir el data del form i fer set dels atributs de la restricció?
            // exemple del controller de course:
            // $course->setName($form['name']->getData());



            $em = $this->getDoctrine()->getManager();
            $em->persist($restriction);
            $em->flush();
            $this->addFlash('success', 'La restricció ha sigut afegida');

            return $this->redirectToRoute('app_getRestrictions');
        }

        return $this->render('restrictions/add.html.twig', [
            'controller_name' => 'RestrictionController',
            'form'=>$form->createView()
        ]);
    }

    /**
    * @Route("/", name="app_getRestrictions")
    * Mètode per llistar totes les restriccions.
    */
    public function showRestrictions(){
        $entityManager = $this->getDoctrine()->getManager();
        $allRestrictions = $entityManager->getRepository(Restriction::class)->findAll();
        return $this->render('restrictions/allRestrictions.html.twig', [
            'allRestrictions' => $allRestrictions,
        ]);
    }

    /**
    * @Route("/delete/{id}", name="app_deleteRestriction")
    * Mètode per esborrar la restriccio passada per paràmetre
    * @param Integer $id id de la restriccio a esborrar.
    */
    public function deleteRestriction($id){
        $entityManager = $this->getDoctrine()->getManager();
     
        $restriction = $entityManager->getRepository(Restriction::class)->find($id);

        if (!$restriction) {
            throw $this->createNotFoundException(
                'No existeix cap restricció amb id '.$id
            );
        }
        $entityManager->remove($restriction);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_getRestrictions');
    }

    /**
    * @Route("/edit/{id}", name="app_updateRestriction")
    * Mètode per editar la restriccio passada per paràmetre
    * @param Integer $id id de la restriccio a editar.
    */
    public function updateRestriction(Restriction $restriction, Request $request): Response{
        $form = $this->createForm(RestrictionType::class, $restriction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_getRestrictions');
        }

        return $this->render('restrictions/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}", name="app_detailRestriction")
     * Mètode per mostrar el detall d'una restriccio. Mostra el valor dels seus atributs.
     * @param Restriction $restriction objecte subject amb les dades de la restriccio.
     */
    public function detailRestriction(Restriction $restriction): Response
    {
        return $this->render('restrictions/detail.html.twig',
            ['restriction' => $restriction]);
    }

}
