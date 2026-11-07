<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class BookingController extends AbstractController
{
    #[Route('/book', name: 'app_book', methods: ['POST'])]
    public function book(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isAjax = $request->isXmlHttpRequest() || str_contains($request->headers->get('Accept', ''), 'application/json');

        $phone = trim($request->request->get('phone', $request->request->get('b_phone', '')));
        $service = trim($request->request->get('service', $request->request->get('b_service', '')));
        $dateStr = trim($request->request->get('date', $request->request->get('b_date', '')));
        $location = trim($request->request->get('location', $request->request->get('b_location', '')));
        $vehicleId = $request->request->get('vehicle_id');

        if (empty($phone) || empty($service) || empty($dateStr) || empty($location)) {
            $errorMsg = 'All required booking fields must be completed.';
            if ($isAjax) {
                return new JsonResponse(['success' => false, 'error' => $errorMsg], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', $errorMsg);
            return $this->redirectToRoute('app_home');
        }

        try {
            $appointmentDate = new \DateTime($dateStr);
            if ($appointmentDate < new \DateTime()) {
                throw new \Exception('Appointment date must be in the future.');
            }
        } catch (\Exception $e) {
            $errorMsg = 'Invalid appointment date: ' . $e->getMessage();
            if ($isAjax) {
                return new JsonResponse(['success' => false, 'error' => $errorMsg], Response::HTTP_BAD_REQUEST);
            }
            $this->addFlash('error', $errorMsg);
            return $this->redirectToRoute('app_home');
        }

        // Vehicle resolution or creation
        $vehicle = null;
        if ($vehicleId) {
            $vehicle = $em->getRepository(Vehicle::class)->findOneBy(['id' => $vehicleId, 'user' => $user]);
        } elseif ($request->request->get('vehicle_make')) {
            $vehicle = new Vehicle();
            $vehicle->setUser($user);
            $vehicle->setMake(trim($request->request->get('vehicle_make')));
            $vehicle->setModel(trim($request->request->get('vehicle_model', 'Standard')));
            $vehicle->setColor(trim($request->request->get('vehicle_color', 'Black')));
            $vehicle->setLicensePlate(trim($request->request->get('vehicle_plate', '')));
            $em->persist($vehicle);
        }

        // Create appointment
        $appointment = new Appointment();
        $appointment->setUser($user);
        $appointment->setVehicle($vehicle);
        $appointment->setServiceType($service);
        $appointment->setAppointmentDate($appointmentDate);
        $appointment->setPhone($phone);
        $appointment->setLocation($location);
        $appointment->setStatus(Appointment::STATUS_PENDING);
        $appointment->setDurationMinutes(60);
        $appointment->setBufferMinutes(30);

        // Update user phone/address if empty
        if (!$user->getPhone() && $phone) {
            $user->setPhone($phone);
        }
        if (!$user->getAddress() && $location) {
            $user->setAddress($location);
        }

        $em->persist($appointment);
        $em->flush();

        if ($isAjax) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Appointment booked successfully! Our team will review and confirm your slot.',
                'appointment_id' => $appointment->getId(),
            ]);
        }

        $this->addFlash('success', 'Your detailing appointment has been scheduled and is pending confirmation.');
        return $this->redirectToRoute('app_my_appointments');
    }

    #[Route('/my-appointments', name: 'app_my_appointments', methods: ['GET'])]
    public function myAppointments(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $appointments = $em->getRepository(Appointment::class)->findBy(
            ['user' => $user],
            ['appointmentDate' => 'DESC']
        );

        return $this->render('booking/my_appointments.html.twig', [
            'appointments' => $appointments,
            'vehicles' => $user->getVehicles(),
        ]);
    }

    #[Route('/appointments/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    public function cancel(int $id, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $appointment = $em->getRepository(Appointment::class)->findOneBy(['id' => $id, 'user' => $user]);

        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        if ($appointment->getStatus() === Appointment::STATUS_PENDING) {
            $appointment->setStatus(Appointment::STATUS_CANCELLED);
            $em->flush();
            $this->addFlash('success', 'Appointment has been cancelled.');
        } else {
            $this->addFlash('error', 'Only pending appointments can be cancelled online.');
        }

        return $this->redirectToRoute('app_my_appointments');
    }
}

