<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/services')]
#[IsGranted('ROLE_ADMIN')]
class ServiceController extends AbstractController
{
    #[Route('', name: 'admin_services', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $services = $em->getRepository(Service::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/services.html.twig', [
            'services' => $services,
        ]);
    }

    #[Route('/add', name: 'admin_service_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $name = trim($request->request->get('name', ''));
        if (!empty($name)) {
            $service = new Service();
            $service->setName($name);
            $service->setIsActive(true);

            $em->persist($service);
            $em->flush();

            $this->addFlash('success', "Service '{$name}' created.");
        }

        return $this->redirectToRoute('admin_services');
    }

    #[Route('/{id}/toggle', name: 'admin_service_toggle', methods: ['POST'])]
    public function toggle(int $id, EntityManagerInterface $em): Response
    {
        $service = $em->getRepository(Service::class)->find($id);
        if ($service) {
            $service->setIsActive(!$service->isActive());
            $em->flush();
            $statusStr = $service->isActive() ? 'activated' : 'deactivated';
            $this->addFlash('success', "Service '{$service->getName()}' has been {$statusStr}.");
        }

        return $this->redirectToRoute('admin_services');
    }
}

