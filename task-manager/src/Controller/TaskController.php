<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task/create', name: 'task_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['description'], $data['status'])) {
            return new Response('Invalid data', 400);
        }

        if (strlen($data['title']) < 3 || strlen($data['title']) > 255) {
            return new Response('Title must be between 3 and 255 characters', 400);
        }

        if (empty($data['description'])) {
            return new Response('Description can\'t be empty', 400);
        }

        $validStatuses = ['todo', 'in_progress', 'done'];
        if (!in_array($data['status'], $validStatuses)) {
            return new Response('You can only enter: todo, in_progress, done', 400);
        }

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($task);
        $entityManager->flush();

        return new Response('Task created', 201);
    }

    #[Route('/task/update/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return new Response('Task not found', 404);
        }

        $data = json_decode($request->getContent(), true);

        $task->setTitle($data['title'] ?? $task->getTitle());
        $task->setDescription($data['description'] ?? $task->getDescription());
        $task->setStatus($data['status'] ?? $task->getStatus());
        $task->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return new Response('Task updated');
    }

    #[Route('/task/delete/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return new Response('Task not found', 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return new Response('Task deleted');
    }

    #[Route('/task/list', name: 'task_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $tasks = $entityManager->getRepository(Task::class)->findAll();

        if (!$tasks) {
            return new Response('No tasks found', 404);
        }

        return $this->json($tasks);
    }
}
