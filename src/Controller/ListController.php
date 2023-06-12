<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

class ListController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/lists", name="get_lists")
     */
    public function getLists()
    {}

    /**
     * @Rest\Get("/lists/{id}", name="get_list")
     */
    public function getList(int $id)
    {}

    /**
     * @Rest\Get("/tasks/{id}/list", name="get_lists_tasks")
     */
    public function getListsTasks(int $id)
    {}

    /**
     * @Rest\Post("/lists", name="create_list")
     */
    public function createList()
    {}

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
