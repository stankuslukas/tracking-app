<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\RegisterUserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterUserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user->setPassword(
                $passwordEncoder->encodePassword($user, $form->get('password')->getData())
            );
            $user->setEmail($form->get('email')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $formAuthenticator,
                'main'
            );
        }

        return $this->render('index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{page}", defaults={"page": "1"}, name="profile")
     */
    public function profile($page): Response
    {
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findAllPaginated($page, $this->getUser()->getId());

        return $this->render('profile.html.twig', [
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route("/create-task", name="create_task")
     */
    public function create_task(Request $request): Response
    {
        $title = trim($request->get('title'));
        $comment = $request->get('comment');
        $date = $request->get('date');
        $duration = $request->get('duration');

        if(!empty($title) && !empty($date) && !empty($duration)){
            $entityManager = $this->getDoctrine()->getManager();

            $task = new Task();

            $task->setTitle($title);
            $task->setComment($comment);
            $date = new \DateTime($date);
            $task->setDate($date);
            $task->setTimeSpent($duration);
            $task->setUserTask($this->getUser());

            $entityManager->persist($task);
            $entityManager->flush();

            $this->redirectToRoute('profile');
        }else{
            $this->addFlash(
                'error', 'Please fill in all the fields.'
            );
        }

        return $this->redirectToRoute('profile');
    }

    /**
     * @Route("/generate-report", name="generate_report")
     */
    public function generate_report(Request $request): Response
    {
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        if(!empty($date_from) && !empty($date_to)) {
            $container = $this->container;
            $response = new StreamedResponse(function () use ($container) {

                $em = $container->get('doctrine')->getManager();

                $fetch_tasks = $this->getDoctrine()->getRepository(Task::class)
                    ->createQueryBuilder('u')
                    ->where('u.UserTask = :userId')
                    ->andWhere('u.date BETWEEN :date_from AND :date_to')
                    ->orderBy('u.date', 'DESC')
                    ->setParameter('userId', $this->getUser()->getId())
                    ->setParameter('date_from', "2021-01-01")
                    ->setParameter('date_to', "2021-01-09")
                    ->getQuery()
                    ->iterate();

                $total_time = $this->getDoctrine()->getRepository(Task::class)
                    ->createQueryBuilder('u')
                    ->select('SUM(u.time_spent) as total')
                    ->where('u.UserTask = :userId')
                    ->andWhere('u.date BETWEEN :date_from AND :date_to')
                    ->orderBy('u.date', 'DESC')
                    ->setParameter('userId', $this->getUser()->getId())
                    ->setParameter('date_from', "2021-01-01")
                    ->setParameter('date_to', "2021-01-09")
                    ->getQuery()
                    ->getSingleResult();

                $handle = fopen('php://output', 'r+');
                $headers = ["Title", "Comment", "Date", "Time spent"];
                $footer = ["Total time", "", "", $total_time['total']];
                fputcsv($handle, $headers);

                while (false !== ($row = $fetch_tasks->next())) {
                    fputcsv($handle, $row[0]->toArray());
                    $em->detach($row[0]);
                }

                fputcsv($handle, $footer);

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

            return $response;
        }else{
            $this->addFlash(
                'report', 'Please fill in all the fields.'
            );

            return $this->redirectToRoute('profile');
        }
    }

}
