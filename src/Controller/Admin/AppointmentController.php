<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AppointmentController extends AbstractController
{
    #[Route('/appointments', name: 'admin_appointments')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $statusFilter = $request->query->get('status');
        $criteria = [];
        if ($statusFilter && in_array($statusFilter, [Appointment::STATUS_PENDING, Appointment::STATUS_APPROVED, Appointment::STATUS_CANCELLED])) {
            $criteria['status'] = $statusFilter;
        }

        $appointments = $em->getRepository(Appointment::class)->findBy($criteria, ['appointmentDate' => 'DESC']);

        return $this->render('admin/appointments.html.twig', [
            'appointments' => $appointments,
            'current_status' => $statusFilter,
        ]);
    }

    #[Route('/api/update-status', name: 'admin_api_update_status', methods: ['POST'])]
    public function updateStatus(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $id = $request->request->get('appointment_id');
        $status = $request->request->get('status');

        if (!$id || !$status) {
            return new JsonResponse(['success' => false, 'error' => 'Missing ID or status parameters.'], Response::HTTP_BAD_REQUEST);
        }

        $appointment = $em->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['success' => false, 'error' => 'Appointment not found.'], Response::HTTP_NOT_FOUND);
        }

        if (!in_array($status, [Appointment::STATUS_PENDING, Appointment::STATUS_APPROVED, Appointment::STATUS_CANCELLED])) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid status.'], Response::HTTP_BAD_REQUEST);
        }

        $appointment->setStatus($status);
        $em->flush();

        return new JsonResponse(['success' => true, 'status' => $status]);
    }
}
