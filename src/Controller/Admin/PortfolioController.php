<?php

namespace App\Controller\Admin;

use App\Entity\Portfolio;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/portfolio')]
#[IsGranted('ROLE_ADMIN')]
class PortfolioController extends AbstractController
{
    #[Route('', name: 'admin_portfolio', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $items = $em->getRepository(Portfolio::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/portfolio.html.twig', [
            'portfolio' => $items,
        ]);
    }

    #[Route('/upload', name: 'admin_portfolio_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('image');
        $desc = trim($request->request->get('description', ''));

        if ($file && $file->isValid()) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($file->getMimeType(), $allowedMimes)) {
                $ext = $file->guessExtension() ?: 'jpg';
                $newFilename = 'portfolio_' . uniqid('', true) . '.' . $ext;
                $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/portfolio';

                $file->move($uploadDir, $newFilename);

                $portfolio = new Portfolio();
                $portfolio->setImagePath('/uploads/portfolio/' . $newFilename);
                $portfolio->setDescription($desc);

                $em->persist($portfolio);
                $em->flush();

                $this->addFlash('success', 'Showcase image uploaded successfully!');
            } else {
                $this->addFlash('error', 'Invalid image format. Allowed formats: JPG, PNG, WEBP.');
            }
        } else {
            $this->addFlash('error', 'Please select an image file to upload.');
        }

        return $this->redirectToRoute('admin_portfolio');
    }

    #[Route('/{id}/delete', name: 'admin_portfolio_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Portfolio::class)->find($id);
        if ($item) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public' . $item->getImagePath();
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Portfolio item removed.');
        }

        return $this->redirectToRoute('admin_portfolio');
    }
}
