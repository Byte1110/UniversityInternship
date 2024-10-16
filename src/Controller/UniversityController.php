<?php

namespace App\Controller;

use App\Dto\UniversityDto;
use App\Entity\University;
use App\Service\UniversityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\DBAL\Exception;
use Symfony\Component\Routing\Annotation\Route;

class UniversityController extends AbstractController
{
    private $service;

    public function __construct(UniversityService  $service)
    {
        $this->service = $service;
    }
    /**
     * @Route("/university/newElem", name="new_elem", methods="POST")
     */
    public function newElem(Request $request): JsonResponse
    {
        $dto = new UniversityDto();
        $dto->name = $request->get('name');
        $dto->parentId = $request->get('parentId');
        $dto->alias = $request->get('alias');
        return $this->json($this->service->newElem($dto));
    }
    /**
     * @Route("/university/show", name="elem_show", methods="GET")
     */
    public function show(Request $request)
    {
        if (is_numeric($request->get('id'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->showElem($request->get('id')));
    }

    /**
     * @Route("/university/edit", name="elem_edit", methods="PUT")
     * @throws Exception
     */
    public function update(Request $request): JsonResponse
    {
        if (is_numeric($request->get('id'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        $dto = new UniversityDto();
        $dto->name = $request->get('name');
        $dto->parentId = $request->get('parentId');
        $dto->alias = $request->get('alias');
        return $this->json($this->service->update($dto, $request->get('id')));
    }
    /**
     * @Route("/university/remove", methods="DELETE")
     */
    public function remove(Request $request): JsonResponse
    {
        if (is_numeric($request->get('id'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->remove($request->get('id')));
    }
    /**
     * @Route("/university/show/user", name="students_show", methods="GET")
     */
    public function userShow(Request $request): JsonResponse
    {
        if (is_numeric($request->get('facultyId'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->userShow($request->get('facultyId'), $request->get('alias')));
    }
    /**
     * @Route("/university/show/group", name="group_show", methods="GET")
     */
    public function groupShow(Request $request): JsonResponse
    {
        if (is_string($request->get('name'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return $this->json($this->service->groupShow($request->get('name')));
    }
    /**
     * @Route("/university/show/group/excel", name="group_show_excel", methods="GET")
     */
    public function groupShowExcel(Request $request): StreamedResponse
    {
        if (is_string($request->get('name'))){
            throw new Exception(json_encode(['success'=> false, 'msg'=>'invalid type']), 406);
        }
        return new StreamedResponse($this->service->groupShowExcel($request->get('name')), Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename=' . $request->get('name') . ".xlsx",
            'Cache-Control'=>'max-age=0'
        ]);
    }
}