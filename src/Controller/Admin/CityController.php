<?php

// ce fichier et tous les twigs et les form ont été généré avec la commande make:crud !
// je n'ai fait que quelques adaptations mineures

namespace App\Controller\Admin;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/villes")
 */
class CityController extends AbstractController
{
    /**
     * @Route("/liste/{page}", name="admin_city_index", methods={"GET"}, requirements={"page": "\d+"})
     */
    public function index(CityRepository $cityRepository, int $page = 1): Response
    {
        $paginatedCities = $cityRepository->findPaginatedCities($page);

        return $this->render('admin/city/index.html.twig', [
            'cities' => $paginatedCities
        ]);
    }

    /**
     * @Route("/ajouter", name="admin_city_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($city);
            $entityManager->flush();

            $this->addFlash('success', $city->getName() . " a bien été créée !");

            return $this->redirectToRoute('admin_city_index');
        }

        return $this->render('admin/city/new.html.twig', [
            'city' => $city,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_city_show", methods={"GET"})
     */
    public function show(City $city): Response
    {
        return $this->render('admin/city/show.html.twig', [
            'city' => $city,
        ]);
    }

    /**
     * @Route("/{id}/modifier", name="admin_city_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, City $city): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', $city->getName() . " a bien été modifiée !");

            return $this->redirectToRoute('admin_city_index');
        }

        return $this->render('admin/city/edit.html.twig', [
            'city' => $city,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_city_delete", methods={"POST"})
     */
    public function delete(Request $request, City $city): Response
    {
        //si la ville est associée des lieux, on ne peut pas la supprimer
        if (!empty($city->getLocations())){
            $this->addFlash('warning', $city->getName() . " est associée à des lieux, et ne peut être supprimée !");
            return $this->redirectToRoute('admin_city_index');
        }

        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($city);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_city_index');
    }
}
