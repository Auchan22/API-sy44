<?php

namespace App\Controller;

use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListController extends AbstractFOSRestController
{
    /**
     * @var TaskListRepository
     */
    private $tlr;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(TaskListRepository $tlr, EntityManagerInterface $entityManager)
    {
        $this->tlr = $tlr;
        $this->entityManager = $entityManager;
    }

    /**
     * @Rest\Get("/lists", name="get_lists")
     */
    public function getLists()
    {
        $data = $this->tlr->findAll();
        return $this->view([$data, Response::HTTP_OK]);
    }

    /**
     * @Rest\Get("/lists/{id}", name="get_list")
     */
    public function getList(int $id)
    {
        $data = $this->tlr->findOneBy(["id" => $id]);
        if($data) return $this->view([$data, Response::HTTP_FOUND]);
        return $this->view(["msg" => "No se encontro la lista con ese id", Response::HTTP_NOT_FOUND]);
    }

    /**
     * @Rest\Get("/tasks/{id}/list", name="get_lists_tasks")
     */
    public function getListsTasks(int $id)
    {}

    /**
     * @Rest\Post("/lists", name="create_list")
     * @Rest\RequestParam(name="title", description="Title of the list", nullable=false)
     * @Rest\RequestParam(name="background", description="Background Title", nullable=true)
     * @Rest\RequestParam(name="backgroundPath", description="Background url", nullable=true)
     */
    public function createList(ParamFetcher $paramFetcher)
    {
        $title = $paramFetcher->get("title");
        $bg = $paramFetcher->get("background") ?? "No Image";
        $bgPath = $paramFetcher->get("backgroundPath") ?? "default.png";
        if($title){
            $list = new TaskList();

            $list->setTitle($title);
            $list->setBackground($bg);
            $list->setBackgroundPath($bgPath);
            $this->entityManager->persist($list);
            $this->entityManager->flush();

            return $this->view([$list, Response::HTTP_CREATED]);
        }else{
            return $this->view(["msg" => "No se creó la lista ya que HAY UN CAMPO vacio", Response::HTTP_BAD_REQUEST]);
        }

    }

    /**
     * @Rest\Put("/lists/{id}", name="update_list")
     */
    public function updateList(int $id)
    {}

    /**
     * @Rest\Patch("/lists/{id}", name="patch_list")
     */
    public function patchList(int $id)
    {}

    /**
     * @Rest\Delete("/lists/{id}", name="delete_list")
     */
    public function deleteList(int $id)
    {}
}
