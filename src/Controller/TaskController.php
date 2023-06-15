<?php

namespace App\Controller;

use App\Entity\Note;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends AbstractFOSRestController
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TaskRepository
     */
    private $taskRepository;

    public function __construct(EntityManagerInterface $entityManager, TaskRepository $taskRepository)
    {
        $this->entityManager = $entityManager;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Rest\Get("/tasks/{id}", name="get_task")
     * @param int $id
     * @return View
     */
    public function getTask(int $id): View
    {
        $task = $this->taskRepository->findOneBy(["id" => $id]);

        if($task){
            return $this->view($task, Response::HTTP_OK);
        }

        return $this->view(["msg" => "No se encontro la tarea con ese id"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Get("/tasks/{id}/notes", name="get_task_notes")
     * @param int $id
     * @return View
     */
    public function getTaskNotes(int $id): View
    {
        $task = $this->taskRepository->findOneBy(["id" => $id]);

        if($task){
            $notes = $task->getNotes();

            return $this->view($notes, Response::HTTP_OK);
        }
        return $this->view(["msg" => "Tarea no encontrada"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Post("/tasks/{id}/notes", name="create_task_note")
     * @Rest\RequestParam(name="content", description="Content for the note" ,nullable=false)
     * @param ParamFetcher $paramFetcher
     * @param int $id
     * @return View
     */
    public function createTaskNotes(ParamFetcher $paramFetcher, int $id): View
    {
        $task = $this->taskRepository->findOneBy(["id" => $id]);

        if($task){
            $note = new Note();
            $content = $paramFetcher->get("content");

            $note->setContent($content);
            $note->setTask($task);

            $task->addNote($note);

            $this->entityManager->persist($note);
            $this->entityManager->flush();

            return $this->view(["msg" => "Nota creada correctamente", $note], Response::HTTP_CREATED);
        }
        return $this->view(["msg" => "No se encontro la tarea con ese id"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Delete("/tasks/{id}", name="delete_list_task")
     * @param int $id
     * @return View
     */
    public function deleteListsTask(int $id): View
    {
        $task = $this->taskRepository->findOneBy(["id" => $id]);

        if($task){

            $this->entityManager->remove($task);
            $this->entityManager->flush();

            return $this->view(["msg" => "Tarea eliminada"], Response::HTTP_NO_CONTENT);
        }

        return $this->view(["msg" => "No se encontró la tarea"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Patch("/tasks/{id}/status", name="change_status_task", methods={"POST", "PATCH"})
     * @param int $id
     * @return View
     */
    public function changeStatusTask(int $id): View
    {
        $task = $this->taskRepository->findOneBy(["id" => $id]);

        if($task){
            $task->setIsCompleted(!$task->getIsCompleted());
            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->view(["estado" => $task->getIsCompleted()], Response::HTTP_OK);
        }

        return $this->view(["msg" => "No se encontró la tarea con ese id"], Response::HTTP_NOT_FOUND);
    }
}
