<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\ReportFormType;
use App\Form\TaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks/{page}", defaults={"page": "1"}, name="tasks")
     */
    public function tasks($page, Request $request): Response
    {
        $task = new Task();

        $formTask = $this->createForm(TaskFormType::class, $task);
        $formTask->handleRequest($request);

        $formReport = $this->createForm(ReportFormType::class, null, array(
            'action' => $this->generateUrl('generate_report'),
            'method' => 'POST',
        ));

        if($formTask->isSubmitted() && $formTask->isValid()){
            $task->setTitle($formTask->get('title')->getData());
            $task->setComment($formTask->get('comment')->getData());
            $task->setDate($formTask->get('date')->getData());
            $task->setTimeSpent($formTask->get('time_spent')->getData());
            $task->setUserTask($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($task);
            $entityManager->flush();
        }

        $tasks = $this->getDoctrine()->getRepository(Task::class)->findAllPaginated($page, $this->getUser()->getId());

        return $this->render('tasks.html.twig', [
            'tasks' => $tasks,
            'formTask' => $formTask->createView(),
            'formReport' => $formReport->createView()
        ]);
    }
}
