<?php

namespace App\Controller;

use App\Form\CaisseSeuilType;
use App\Service\CaisseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/caisse')]
#[IsGranted('ROLE_ADMIN')]
class CaisseController extends AbstractController
{
    #[Route('/configure', name: 'app_caisse_configure', methods: ['GET', 'POST'])]
    public function configureSeuil(Request $request, CaisseService $caisseService, EntityManagerInterface $em): Response
    {
        $caisse = $caisseService->getCaisse();
        $form = $this->createForm(CaisseSeuilType::class, $caisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le seuil d\'alerte a été mis à jour avec succès.');

            return $this->redirectToRoute('app_caisse_configure');
        }

        return $this->render('caisse/configure.html.twig', [
            'form' => $form->createView(),
            'caisse' => $caisse
        ]);
    }
}
