<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\TaskList;
use App\Repository\TaskListRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function getLists(): View
    {
        $data = $this->tlr->findAll();
        return $this->view([$data, Response::HTTP_OK]);
    }

    /**
     * @Rest\Get("/lists/{id}", name="get_list")
     */
    public function getList(int $id): View
    {
        $data = $this->tlr->findOneBy(["id" => $id]);
        if($data) return $this->view([$data, Response::HTTP_FOUND]);
        return $this->view(["msg" => "No se encontro la lista con ese id", Response::HTTP_NOT_FOUND]);
    }

    /**
     * @Rest\Get("/lists/{id}/tasks", name="get_lists_tasks")
     * @param int $id
     * @return View
     */
    public function getListsTasks(int $id): View
    {
        $list = $this->tlr->findOneBy(["id" => $id]);

        if($list){
            $tasks = $list->getTasks();

            return $this->view(["task" => $tasks], Response::HTTP_OK);
        }

        return $this->view(["msg" => "Lista no encontrada"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Post("/lists", name="create_list")
     * @Rest\RequestParam(name="title", description="Title of the list", nullable=false)
     * @Rest\RequestParam(name="background", description="Background Title", nullable=true)
     * @Rest\RequestParam(name="backgroundPath", description="Background url", nullable=true)
     */
    public function createList(ParamFetcher $paramFetcher): View
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
     * @Rest\Post("/lists/{id}/task", name="create_lists_task")
     * @Rest\RequestParam(name="title", description="Title of the new Task", nullable=false)
     * @param ParamFetcher $paramFetcher
     * @param int $id
     * @return View
     */
    public function createListsTask(ParamFetcher $paramFetcher, int $id): View
    {
        $list = $this->tlr->findOneBy(["id" => $id]);

        if($list){
            $title = $paramFetcher->get("title");

            $task = new Task();
            $task->setTitle($title);
            $task->setList($list);

            $list->addTask($task);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            return $this->view($task, Response::HTTP_OK);
        }

        return $this->view(["msg" => "No se encontró la lista"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Patch("/lists/{id}/title", name="update_title_list", methods={"POST", "PATCH"})
     * @Rest\RequestParam(name="title", description="The new title for the list", nullable=false)
     * @param ParamFetcher $paramFetcher
     * @param int $id
     * @return View
     */
    public function patchListTitle(ParamFetcher $paramFetcher, int $id): View
    {
        $list = $this->tlr->findOneBy(["id" => $id]);

        if($list){
            $data = $paramFetcher->get("title");
            $list->setTitle($data);

            $this->entityManager->persist($list);
            $this->entityManager->flush();

            return $this->view(["msg" => "Lista actualizada correctamente"], Response::HTTP_OK);
        }

        return $this->view(["msg" => "No se encontro la lista con ese id"], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Delete("/lists/{id}", name="delete_list")
     * @param int $id
     * @return View
     */
    public function deleteList(int $id): View
    {
        $list = $this->tlr->findOneBy(["id" => $id]);

        if($list){
            $this->entityManager->remove($list);
            $this->entityManager->flush();

            return $this->view(["msg" => "Lista eliminada correctamente"], Response::HTTP_NO_CONTENT);
        }

        return $this->view(["msg" => "Lista no encontrada"], Response::HTTP_NOT_FOUND);
    }

    /**
     * @Rest\Patch("/lists/{id}/background", name="background_list", methods={"POST", "PATCH"})
     * @Rest\FileParam(name="image", description="The background of the list", nullable=false, image=true)
     * @param Request $request
     * @param ParamFetcher $paramFetcher
     * @param int $id
     * @return View
     */
    public function backgroundLists(Request $request, ParamFetcher $paramFetcher, int $id): View
    {
        $list = $this->tlr->findOneBy(["id" => $id]);

        if($list){
            $currentBg = $list->getBackground();
            if(!is_null($currentBg)){
                $filesystem = new Filesystem();
                $filesystem->remove($this->getUploadsDir() . $currentBg);
            }

            /** @var UploadedFile $file */
            $file = $paramFetcher->get("image");
            if($file){
                $filename = md5(uniqid()) . "." . $file->guessClientExtension();

                $file->move(
                    $this->getUploadsDir(),
                    $filename
                );

                $list->setBackground($filename);
                $list->setBackgroundPath("/uploads/" . $filename);

                $this->entityManager->persist($list);
                $this->entityManager->flush();

                $data = $request->getUriForPath($list->getBackgroundPath());

                return $this->view(["msg" => "Imagén cargada correctamente", $data], Response::HTTP_OK);
            }

            return $this->view(["msg" => "Hubo un error"], Response::HTTP_NOT_FOUND);
        }

        return $this->view(["msg" => "No se encontro la lista con ese id"], Response::HTTP_NOT_FOUND);
    }

    private function getUploadsDir(){
        return $this->getParameter("uploads_dir");
    }
}
