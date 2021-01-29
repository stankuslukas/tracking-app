<?php

namespace App\Services;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvService{
    private $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    public function exportCsv(string $dateFrom, string $dateTo, int $userId){

            $fetch_tasks = $this->em->getRepository(Task::class)
                ->createQueryBuilder('u')
                ->where('u.UserTask = :userId')
                ->andWhere('u.date BETWEEN :date_from AND :date_to')
                ->orderBy('u.date', 'DESC')
                ->setParameter('userId', $userId)
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo)
                ->getQuery()
                ->iterate();

            $total_time = $this->em->getRepository(Task::class)
                ->createQueryBuilder('u')
                ->select('SUM(u.time_spent) as total')
                ->where('u.UserTask = :userId')
                ->andWhere('u.date BETWEEN :date_from AND :date_to')
                ->orderBy('u.date', 'DESC')
                ->setParameter('userId', $userId)
                ->setParameter('date_from', $dateFrom)
                ->setParameter('date_to', $dateTo)
                ->getQuery()
                ->getSingleResult();

        $response = new StreamedResponse();
        $response->setCallback(
            function () use ($fetch_tasks, $total_time) {
                $headers = ["Title", "Comment", "Date", "Time spent"];
                $footer = ["Total time", "", "", $total_time['total']];

                $handle = fopen('php://output', 'r+');

                fputcsv($handle, $headers);

                foreach ($fetch_tasks as $row) {
                    fputcsv($handle, $row[0]->toArray());
                }

                fputcsv($handle, $footer);

                fclose($handle);
            }
        );

        $filename = "Export_{$dateFrom}_{$dateTo}.csv";
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$filename);

        return $response;
    }
}