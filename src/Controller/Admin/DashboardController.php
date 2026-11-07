<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        $appointmentRepo = $em->getRepository(Appointment::class);
        $inventoryRepo = $em->getRepository(Inventory::class);
        $userRepo = $em->getRepository(User::class);

        $totalAppointments = $appointmentRepo->count([]);
        $pendingAppointments = $appointmentRepo->countByStatus(Appointment::STATUS_PENDING);
        $approvedAppointments = $appointmentRepo->countByStatus(Appointment::STATUS_APPROVED);
        $lowStockItems = $inventoryRepo->findLowStockItems();
        $recentBookings = $appointmentRepo->findBy([], ['appointmentDate' => 'DESC'], 5);
        $totalClients = $userRepo->count([]);

        return $this->render('admin/dashboard.html.twig', [
            'total_appointments' => $totalAppointments,
            'pending_appointments' => $pendingAppointments,
            'approved_appointments' => $approvedAppointments,
            'low_stock_items' => $lowStockItems,
            'recent_bookings' => $recentBookings,
            'total_clients' => $totalClients,
        ]);
    }
}
