<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\UserImportCsvType;
use App\Service\CsvUserImporter;
use Symfony\Component\HttpFoundation\Request;



final class UserImportController extends AbstractController
{
    #[Route('/admin/utilisateur/import', name: 'admin_utilisateur_import')]
public function import(
    Request $request,
    CsvUserImporter $importer
): Response {

    $form = $this->createForm(UserImportCsvType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $file = $form->get('csvFile')->getData();

        if ($file) {

            $result = $importer->import($file->getPathname());

            $this->addFlash('success', $result['created'].' users created');

            foreach ($result['errors'] as $error) {
                $this->addFlash('danger', $error);
            }
        }
    }

 return $this->render('admin/user_import/index.html.twig', [
  'form' => $form->createView(),
]);
}

}
