<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/inventory')]
#[IsGranted('ROLE_ADMIN')]
class InventoryController extends AbstractController
{
    #[Route('', name: 'admin_inventory', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $items = $em->getRepository(Inventory::class)->findBy([], ['itemName' => 'ASC']);

        return $this->render('admin/inventory.html.twig', [
            'inventory' => $items,
        ]);
    }

    #[Route('/add', name: 'admin_inventory_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $name = trim($request->request->get('item_name', ''));
        $qty = max(0, (int) $request->request->get('quantity', 0));
        $threshold = max(0, (int) $request->request->get('threshold', 5));

        if (!empty($name)) {
            $item = new Inventory();
            $item->setItemName($name);
            $item->setQuantity($qty);
            $item->setLowStockThreshold($threshold);

            $em->persist($item);
            $em->flush();

            $this->addFlash('success', "Item '{$name}' added to inventory.");
        } else {
            $this->addFlash('error', 'Item name cannot be empty.');
        }

        return $this->redirectToRoute('admin_inventory');
    }

    #[Route('/{id}/update', name: 'admin_inventory_update', methods: ['POST'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Inventory::class)->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found.');
        }

        $qty = max(0, (int) $request->request->get('quantity', 0));
        $threshold = max(0, (int) $request->request->get('threshold', 5));

        $item->setQuantity($qty);
        $item->setLowStockThreshold($threshold);
        $em->flush();

        $this->addFlash('success', "Item '{$item->getItemName()}' updated.");
        return $this->redirectToRoute('admin_inventory');
    }

    #[Route('/{id}/delete', name: 'admin_inventory_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $item = $em->getRepository(Inventory::class)->find($id);
        if ($item) {
            $em->remove($item);
            $em->flush();
            $this->addFlash('success', 'Item removed from inventory.');
        }

        return $this->redirectToRoute('admin_inventory');
    }
}

