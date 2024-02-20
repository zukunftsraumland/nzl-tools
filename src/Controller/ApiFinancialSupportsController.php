<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\FinancialSupport;
use App\Service\FinancialSupportService;
use App\Util\PvTrans;
use Doctrine\ORM\EntityManagerInterface;
use Mpdf\Mpdf;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/api/v1/financial-supports', name: 'api_financial_supports_')]
class ApiFinancialSupportsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all financial supports',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support']))
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function index(EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $financialSupports = $em->getRepository(FinancialSupport::class)->findBy([], ['position' => 'ASC']);

        $result = $normalizer->normalize($financialSupports, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function find(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create a financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer, 
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $financialSupportService->validateFinancialSupportPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $financialSupport = $financialSupportService->createFinancialSupport($payload);

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update a financial support',
        content: new OA\JsonContent(
            ref: new Model(type: FinancialSupport::class, groups: ['id', 'financial_support'])
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));
        
        $payload = json_decode($request->getContent(), true);
        
        if(($errors = $financialSupportService->validateFinancialSupportPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }
        
        $financialSupport = $financialSupportService->updateFinancialSupport($financialSupport, $payload);

        $result = $normalizer->normalize($financialSupport, null, [
            'groups' => ['id', 'financial_support'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete a financial support',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Financial Supports')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           FinancialSupportService $financialSupportService): JsonResponse
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));
        
        $financialSupportService->deleteFinancialSupport($financialSupport);
        
        return $this->json([]);
    }

    #[Route(path: '/export/{id}-{_locale}.pdf', name: 'export_pdf', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/pdf', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Financial Supports')]
    public function exportPdf(Request $request, EntityManagerInterface $em,
                               TranslatorInterface $translator): Response
    {
        $financialSupport = $em->getRepository(FinancialSupport::class)
            ->find($request->get('id'));

        if(!$financialSupport) {
            throw $this->createNotFoundException();
        }

        if(!$financialSupport->getIsPublic() && !$this->isGranted('ROLE_EDITOR')) {
            throw $this->createNotFoundException();
        }

        // expecting the font dir to have *.ttf fonts and using the following naming convention:
        // <font_name>_<font_weight>.ttf, e.g.:
        // helveticaneue_r.ttf, helveticaneue_b.ttf

        $fontsDir = __DIR__.'/../../assets/fonts';
        $fontData = [];
        $defaultFont = null;

        foreach(glob($fontsDir.'/*.ttf') as $font) {
            list($fontName, $fontWeight) = explode('_', basename($font, '.ttf'), 2);

            if(!array_key_exists($fontName, $fontData)) {
                $fontData[$fontName] = [];
            }

            $fontData[$fontName][strtoupper($fontWeight)] = $fontName.'_'.$fontWeight.'.ttf';

            if(!$defaultFont) {
                $defaultFont = $fontName;
            }
        }

        $mpdf = new Mpdf([
            'fontDir' => [
                $fontsDir.'/',
            ],
            'fontdata' => $fontData,
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 20,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'default_font' => $defaultFont,
        ]);

        $mpdf->SetTitle(PvTrans::translate($financialSupport, 'name', $request->getLocale()));
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->shrink_tables_to_fit = 1;

        $logo = PvTrans::translate($financialSupport, 'logo', $request->getLocale());

        if($logo) {
            $file = $em->getRepository(File::class)
                ->find($logo['id']);
            $imagick = new \Imagick();
            $data = stream_get_contents($file->getData());
            $data = count(explode(';base64,', $data)) >= 2 ? explode(';base64,', $data, 2)[1] : $data;
            $imagick->readImageBlob(base64_decode($data));

            $logo = tempnam(sys_get_temp_dir(), 'logo'.$file->getId());
            file_put_contents($logo, $imagick->getImageBlob());
        }

        $mpdf->WriteHTML($this->renderView('pdf/financial-support.html.twig', [
            'financialSupport' => $financialSupport,
            'logo' => $logo,
        ]));

        $extension = 'pdf';
        $fileName = $translator->trans('Finanzhilfen')
            .' - '.PvTrans::translate($financialSupport, 'name', $request->getLocale())
            .'.'.$extension;

        $tmpFile = tempnam(sys_get_temp_dir(), 'fs'.$financialSupport->getId());

        $mpdf->Output($tmpFile, \Mpdf\Output\Destination::FILE);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        $response->headers->set('Content-Type', 'application/pdf');

        $response->deleteFileAfterSend(true);

        return $response;

    }
    
}