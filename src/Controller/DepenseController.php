<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Form\DepenseSearchType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/depense')]
final class DepenseController extends AbstractController
{
    #[Route(name: 'app_depense_index', methods: ['GET'])]
    public function index(Request $request, DepenseRepository $depenseRepository): Response
    {
        $form = $this->createForm(DepenseSearchType::class);
        $form->handleRequest($request);

        $depenses = $depenseRepository->findBySearchCriteria(
            $form->get('montantMin')->getData(),
            $form->get('montantMax')->getData(),
            $form->get('description')->getData(),
            $form->get('dateMin')->getData(),
            $form->get('dateMax')->getData()
        );

        return $this->render('depense/index.html.twig', [
            'depenses' => $depenses,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_depense_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $depense = new Depense();
    $form = $this->createForm(DepenseType::class, $depense);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $depense->setCreatedAt(new \DateTimeImmutable());
        $depense->setUser($this->getUser());

        $entityManager->persist($depense);
        $entityManager->flush();

        return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('depense/new.html.twig', [
        'depense' => $depense,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_depense_show', methods: ['GET'])]
    public function show(Depense $depense): Response
    {
        return $this->render('depense/show.html.twig', [
            'depense' => $depense,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_depense_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depense $depense, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DepenseType::class, $depense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('depense/edit.html.twig', [
            'depense' => $depense,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_depense_delete', methods: ['POST'])]
    public function delete(Request $request, Depense $depense, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$depense->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($depense);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_depense_index', [], Response::HTTP_SEE_OTHER);
    }
}
