<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class ClientController extends AbstractController
{
    #[Route('/clients', name: 'admin_clients')]
    public function index(EntityManagerInterface $em): Response
    {
        $clients = $em->getRepository(User::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/clients.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/clients/{id}', name: 'admin_client_profile')]
    public function profile(int $id, EntityManagerInterface $em): Response
    {
        $client = $em->getRepository(User::class)->find($id);
        if (!$client) {
            throw $this->createNotFoundException('Client not found.');
        }

        return $this->render('admin/client_profile.html.twig', [
            'client' => $client,
            'vehicles' => $client->getVehicles(),
            'appointments' => $client->getAppointments(),
        ]);
    }
}

