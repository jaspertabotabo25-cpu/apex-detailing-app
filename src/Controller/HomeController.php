<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PortfolioRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ServiceRepository $serviceRepository,
        PortfolioRepository $portfolioRepository
    ): Response {
        $services = $serviceRepository->findActiveServices();
        $portfolio = $portfolioRepository->findLatest(6);

        /** @var User|null $user */
        $user = $this->getUser();
        $vehicles = $user ? $user->getVehicles() : [];

        return $this->render('home/index.html.twig', [
            'services' => $services,
            'portfolio' => $portfolio,
            'vehicles' => $vehicles,
        ]);
    }
}
