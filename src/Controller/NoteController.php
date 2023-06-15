<?php

namespace App\Controller;

use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoteController extends AbstractFOSRestController
{
    /**
     * @var NoteRepository
     */
    private $noteRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(NoteRepository $noteRepository, EntityManagerInterface $entityManager){

        $this->noteRepository = $noteRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/notes/{id}", name="get_note")
     * @param int $id
     * @return View
     */
    public function getNote(int $id):View
    {
        $note = $this->noteRepository->findOneBy(["id" => $id]);

        if($note){
            return $this->view($note, Response::HTTP_NOT_FOUND);
        }

        return $this->view(["msg" => "No se encontro la nota con ese id"],  Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Delete("/notes/{id}", name="delete_note")
     * @param int $id
     * @return void
     */
    public function deleteNote(int $id): View
    {
        $note = $this->noteRepository->findOneBy(["id" => $id]);

        if($note){
            $this->entityManager->remove($note);
            $this->entityManager->flush();

            return $this->view(["msg" => "Nota eliminada correctamente"], Response::HTTP_OK);
        }
        return $this->view(["msg" => "No se encontro la nota"],  Response::HTTP_NOT_FOUND);
    }
}
