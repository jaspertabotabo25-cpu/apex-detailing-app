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
class ScheduleController extends AbstractController
{
    #[Route('/schedule', name: 'admin_schedule')]
    public function index(EntityManagerInterface $em): Response
    {
        $appointments = $em->getRepository(Appointment::class)->findBy(
            ['status' => Appointment::STATUS_APPROVED],
            ['appointmentDate' => 'ASC']
        );

        return $this->render('admin/schedule.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/api/update-schedule', name: 'admin_api_update_schedule', methods: ['POST'])]
    public function updateSchedule(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $id = $request->request->get('id');
        $duration = (int) $request->request->get('duration', 60);
        $buffer = (int) $request->request->get('buffer', 30);

        $appointment = $em->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['success' => false, 'error' => 'Appointment not found.'], Response::HTTP_NOT_FOUND);
        }

        $appointment->setDurationMinutes(max(15, $duration));
        $appointment->setBufferMinutes(max(0, $buffer));
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
