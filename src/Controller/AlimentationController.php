<?php

namespace App\Controller;

use App\Entity\Alimentation;
use App\Form\AlimentationForm;
use App\Form\AlimentationType;
use App\Form\AlimentationSearchType;
use App\Repository\AlimentationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/alimentation')]
final class AlimentationController extends AbstractController
{
    #[Route(name: 'app_alimentation_index', methods: ['GET'])]
    public function index(Request $request, AlimentationRepository $alimentationRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(AlimentationSearchType::class);
        $form->handleRequest($request);

        $query = $alimentationRepository->createQueryBuilderForSearch(
            $form->get('montantMin')->getData(),
            $form->get('montantMax')->getData(),
            $form->get('description')->getData(),
            $form->get('dateMin')->getData(),
            $form->get('dateMax')->getData()
        );

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Nombre d'éléments par page
        );

        return $this->render('alimentation/index.html.twig', [
            'alimentations' => $pagination,
            'searchForm' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_alimentation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $alimentation = new Alimentation();
    $form = $this->createForm(AlimentationType::class, $alimentation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $alimentation->setCreatedAt(new \DateTimeImmutable());
        $alimentation->setUser($this->getUser());

        $entityManager->persist($alimentation);
        $entityManager->flush();

        return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('alimentation/new.html.twig', [
        'alimentation' => $alimentation,
        'form' => $form,
    ]);
}


    #[Route('/{id}', name: 'app_alimentation_show', methods: ['GET'])]
    public function show(Alimentation $alimentation): Response
    {
        return $this->render('alimentation/show.html.twig', [
            'alimentation' => $alimentation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alimentation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alimentation $alimentation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AlimentationType::class, $alimentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alimentation/edit.html.twig', [
            'alimentation' => $alimentation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alimentation_delete', methods: ['POST'])]
    public function delete(Request $request, Alimentation $alimentation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alimentation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alimentation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_alimentation_index', [], Response::HTTP_SEE_OTHER);
    }
}
