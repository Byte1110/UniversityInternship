<?php

namespace App\Controller;

use App\Service\TreeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class TreeController extends AbstractController
{
    private $service;

    public function __construct(TreeService $service)
    {
        $this->service = $service;
    }
    /**
     * @Route("/university/tree", name="tree_show", methods="GET")
     */
    public function tree(): JsonResponse
    {
        return $this->json($this->service->getTree());
    }
    /**
     * @Route("/university/tree/pdf/{indent}", name="tree_pdf_show", methods="GET")
     */
    public function treePdf(int $indent): StreamedResponse
    {
        return new StreamedResponse($this->service->treePdf($indent), Response::HTTP_OK, [
            'Content-type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Table.pdf"',
            'Cache-Control'=>'max-age=0'
        ]);
    }
    /**
     * @Route("/university/table/pdf", name="tree_table_show", methods="GET")
     */
    public function treeTable(): StreamedResponse
    {
        return new StreamedResponse($this->service->treeTableDompdf(), Response::HTTP_OK, [
            'Content-type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Table.pdf"',
            'Cache-Control'=>'max-age=0'
        ]);
    }
    /**
     * @Route("/ex", name="ex", methods="GET")
     */
    public function ex(): StreamedResponse
    {
        require_once 'Classes/PHPExcel/IOFactory.php';

        $inputFileType = 'Excel5';
        $inputFileName = 'YOUR_EXCEL_FILE_PATH';

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcelReader = $objReader->load($inputFileName);

        $loadedSheetNames = $objPHPExcelReader->getSheetNames();

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcelReader, 'CSV');

        foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            $objWriter->setSheetIndex($sheetIndex);
            $objWriter->save($loadedSheetName.'.csv');
        }

        return new StreamedResponse($this->service->treeTableDompdf(), Response::HTTP_OK, [
            'Content-type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Table.pdf"',
            'Cache-Control'=>'max-age=0'
        ]);
    }

}