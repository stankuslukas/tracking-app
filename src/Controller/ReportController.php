<?php

namespace App\Controller;

use App\Services\CsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    /**
     * @Route("/generate-report", name="generate_report")
     */
    public function generateReport(Request $request, CsvService $csv): Response
    {
        $date_from = $request->request->get('report_form')['date_from'];
        $date_to = $request->request->get('report_form')['date_to'];

        if(!empty($date_from) && !empty($date_to)) {
            return $csv->exportCsv($date_from, $date_to, $this->getUser()->getId());
        }else{
            $this->addFlash(
                'report', 'Please fill in all the fields.'
            );

            return $this->redirectToRoute('tasks');
        }
    }
}
