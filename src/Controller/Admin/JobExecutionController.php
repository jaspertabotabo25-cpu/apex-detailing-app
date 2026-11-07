<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Inspection;
use App\Entity\Portfolio;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class JobExecutionController extends AbstractController
{
    #[Route('/appointments/{id}/job-execution', name: 'admin_job_execution')]
    public function index(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $appointment = $em->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            throw $this->createNotFoundException('Appointment not found.');
        }

        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $notes = trim($request->request->get('notes', ''));
            $pushPortfolio = (bool) $request->request->get('push_portfolio', false);
            /** @var UploadedFile|null $file */
            $file = $request->files->get('image');

            if ($file && $file->isValid()) {
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
                if (in_array($file->getMimeType(), $allowedMimes)) {
                    $ext = $file->guessExtension() ?: 'jpg';
                    $type = ($action === 'upload_post') ? Inspection::TYPE_POST : Inspection::TYPE_PRE;
                    $filename = 'inspection_' . $type . '_' . uniqid('', true) . '.' . $ext;
                    $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/inspections';

                    $file->move($uploadDir, $filename);
                    $webPath = '/uploads/inspections/' . $filename;

                    $inspection = new Inspection();
                    $inspection->setAppointment($appointment);
                    $inspection->setType($type);
                    $inspection->setImagePath($webPath);
                    $inspection->setNotes($notes ?: null);

                    $em->persist($inspection);

                    // Push to portfolio if requested on post-inspection
                    if ($pushPortfolio && $type === Inspection::TYPE_POST) {
                        $portfolio = new Portfolio();
                        $portfolio->setImagePath($webPath);
                        $desc = "Completed {$appointment->getServiceType()} for " . ($appointment->getVehicle() ? $appointment->getVehicle()->getDisplayName() : 'client');
                        if ($notes) {
                            $desc .= " - {$notes}";
                        }
                        $portfolio->setDescription($desc);
                        $em->persist($portfolio);
                    }

                    $em->flush();
                    $this->addFlash('success', ucfirst($type) . '-inspection record saved successfully!');
                } else {
                    $this->addFlash('error', 'Invalid image format. Allowed formats: JPG, PNG, WEBP.');
                }
            } else {
                $this->addFlash('error', 'Please select an image file to upload.');
            }

            return $this->redirectToRoute('admin_job_execution', ['id' => $id]);
        }

        $preInspections = $em->getRepository(Inspection::class)->findBy(
            ['appointment' => $appointment, 'type' => Inspection::TYPE_PRE],
            ['createdAt' => 'ASC']
        );
        $postInspections = $em->getRepository(Inspection::class)->findBy(
            ['appointment' => $appointment, 'type' => Inspection::TYPE_POST],
            ['createdAt' => 'ASC']
        );

        return $this->render('admin/job_execution.html.twig', [
            'appointment' => $appointment,
            'pre_inspections' => $preInspections,
            'post_inspections' => $postInspections,
        ]);
    }
}

