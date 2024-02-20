<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Contact;
use App\Service\RegionService;
use App\Util\PvTrans;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Region;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(path: '/api/v1/regions', name: 'api_regions_')]
class ApiRegionsController extends AbstractController
{
    
    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific region types (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Limit returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'offset',
        description: 'Skip returned items',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'orderBy[]',
        description: 'Order items by field',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['id', 'createdAt', 'updatedAt']),
    )]
    #[OA\Parameter(
        name: 'orderDirection[]',
        description: 'Set order direction',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns all regions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Region::class, groups: ['id', 'region']))
        )
    )]
    #[OA\Tag(name: 'Regions')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('r')
            ->from(Region::class, 'r')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(r.name LIKE :term OR r.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach($request->get('type') as $key => $type) {

                $typeQuery[] = 'r.type = :type'.$key;

                $qb
                    ->setParameter('type'.$key, $type)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery))
            ;

        }

        if($request->get('limit')) {
            $qb->setMaxResults($request->get('limit'));
        }

        if($request->get('offset')) {
            $qb->setFirstResult($request->get('offset'));
        }

        if($request->get('orderBy') && is_array($request->get('orderBy')) && count($request->get('orderBy'))) {

            foreach($request->get('orderBy') as $key => $orderBy) {

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('r.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('r.name', 'ASC')
            ;
        }

        $regions = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($regions, null, [
            'groups' => ['id', 'region'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single region',
        content: new OA\JsonContent(
            ref: new Model(type: Region::class, groups: ['id', 'region'])
        )
    )]
    #[OA\Tag(name: 'Regions')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $region = $em->getRepository(Region::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($region, null, [
            'groups' => ['id', 'region', 'region_type', 'language', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an region',
        content: new OA\JsonContent(
            ref: new Model(type: Region::class, groups: ['id', 'region'])
        )
    )]
    #[OA\Tag(name: 'Regions')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, RegionService $regionService, CacheInterface $cache): JsonResponse
    {

        $payload = json_decode($request->getContent(), true);

        if(($errors = $regionService->validateRegionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $region = $regionService->createRegion($payload);

        $result = $normalizer->normalize($region, null, [
            'groups' => ['id', 'region'],
        ]);

        $cache->delete('geojson');
        $cache->delete('cities.geojson');
        $cache->delete('regions.geojson.'.md5($region->getType()));
        $cache->delete('regions.geojson.'.$region->getId());

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an region',
        content: new OA\JsonContent(
            ref: new Model(type: Region::class, groups: ['id', 'region'])
        )
    )]
    #[OA\Tag(name: 'Regions')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, RegionService $regionService, CacheInterface $cache): JsonResponse
    {

        $region = $em->getRepository(Region::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $regionService->validateRegionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $region = $regionService->updateRegion($region, $payload);

        $result = $normalizer->normalize($region, null, [
            'groups' => ['id', 'region'],
        ]);

        $cache->delete('geojson');
        $cache->delete('cities.geojson');
        $cache->delete('regions.geojson.'.md5($region->getType()));
        $cache->delete('regions.geojson.'.$region->getId());

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an region',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Regions')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, RegionService $regionService, CacheInterface $cache): JsonResponse
    {

        $region = $em->getRepository(Region::class)
            ->find($request->get('id'));

        $regionService->deleteRegion($region);

        $cache->delete('geojson');
        $cache->delete('cities.geojson');
        $cache->delete('regions.geojson.'.md5($region->getType()));
        $cache->delete('regions.geojson.'.$region->getId());

        return $this->json([]);
    }

    #[Route(path: '/{type}/geojson/{_locale}.json', name: 'regions_geojson', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns geojson',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Regions')]
    public function regionsGeojson(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer, CacheInterface $cache, string $nodeJs): JsonResponse
    {
        $geojson = $cache->get('regions.geojson.'.md5($request->get('type')), function (ItemInterface $item) use ($cache, $em, $request, $normalizer, $nodeJs) {

            $item->expiresAt(new \DateTime('+720 days'));

            $geojson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            $geojsonCities = file_get_contents(__DIR__.'/../../config/gis/cities.json');
            $geojsonCities = json_decode($geojsonCities, true);

            $regions = $em->getRepository(Region::class)
                ->findBy([
                    'type' => $request->get('type'),
                    'isPublic' => true,
                ]);

            foreach($regions as $region) {

                $feature = $cache->get('regions.geojson.'.$region->getId(), function (ItemInterface $item) use ($region, $geojsonCities, $em, $request, $normalizer, $nodeJs) {

                    $item->expiresAt(new \DateTime('+720 days'));

                    $feature = [
                        'id' => $region->getId(),
                        'type' => 'Feature',
                        'properties' => [
                            'id' => $region->getId(),
                            'name' => PvTrans::trans($region, 'name', $request->getLocale()),
                            'color' => $region->getColor(),
                            'cities' => [],
                        ],
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [],
                        ],
                    ];

                    foreach($geojsonCities['features'] as $geojsonCity) {

                        foreach($region->getCities() as $city) {

                            if($city->getMunicipalNumber() !== $geojsonCity['properties']['GMDNR']) {
                                continue;
                            }

                            $feature['properties']['cities'][] = $normalizer->normalize($city, null, [
                                'groups' => ['id', 'city'],
                            ]);

                            if(!count($feature['geometry']['coordinates'])) {
                                $feature['geometry'] = $geojsonCity['geometry'];

                                continue;
                            }

                            $feature['geometry'] = json_decode(
                                shell_exec(
                                    sprintf(
                                        '%s %s union %s %s',
                                        $nodeJs,
                                        __DIR__.'/../../bin/gis-util',
                                        escapeshellarg(json_encode($feature['geometry'])),
                                        escapeshellarg(json_encode($geojsonCity['geometry'])))
                                )
                            , true)['geometry'];

                        }

                    }

                    return $feature;

                });

                $geojson['features'][] = $feature;

            }

            return $geojson;

        });

        return $this->json($geojson);
    }

    #[Route(path: '/geojson/cities/{_locale}.json', name: 'cities_geojson', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns geojson',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Regions')]
    public function geojson(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer, CacheInterface $cache): JsonResponse
    {
        $geojson = $cache->get('cities.geojson', function (ItemInterface $item) use ($em, $request, $normalizer) {
            $item->expiresAfter(3600);

            $geojson = file_get_contents(__DIR__.'/../../config/gis/cities.json');
            $geojson = json_decode($geojson, true);

            foreach($geojson['features'] as $featureKey => $feature) {

                $municipalNumber = $feature['properties']['GMDNR'];
                $city = $em->getRepository(City::class)->findOneBy([
                    'municipalNumber' => $municipalNumber,
                ]);

                $geojson['features'][$featureKey]['properties']['regions'] = [];

                if(!$city) {
                    continue;
                }

                $geojson['features'][$featureKey]['properties']['city'] = [
                    'id' => $city->getId(),
                    'name' => PvTrans::trans($city, 'name', $request->getLocale()),
                ];

                $qb = $em->createQueryBuilder();
                $qb
                    ->select('r')
                    ->from(Region::class, 'r')
                    ->leftJoin('r.cities', 'c')
                    ->andWhere('c.id = :cityId')
                    ->setParameter('cityId', $city->getId())
                    ->andWhere('r.isPublic = :isPublic')
                    ->setParameter('isPublic', true)
                ;

                $regions = $qb->getQuery()->getResult();

                foreach($regions as $region) {
                    $geojson['features'][$featureKey]['properties']['regions'][] = [
                        'id' => $region->getId(),
                        'name' => PvTrans::trans($region, 'name', $request->getLocale()),
                        'type' => $region->getType(),
                        'color' => $region->getColor(),
                    ];
                }

            }

            return $geojson;
        });

        return $this->json($geojson);
    }

    #[Route(path: '.xlsx', name: 'xlsx', methods: ['GET'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Returns a file',
        content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Regions')]
    #[Security(name: 'cookieAuth')]
    public function xlsx(Request $request, EntityManagerInterface $em): Response
    {
        $regions = $em->getRepository(Region::class)
            ->findBy([], [
                'id' => 'ASC',
            ]);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach(['de', 'fr', 'it'] as $localeIndex => $locale) {

            $sheet = new Worksheet(null, 'Regionen - '.strtoupper($locale));
            $spreadsheet->addSheet($sheet, $localeIndex);

            $sheet = $spreadsheet->getSheet($localeIndex);

            $columns = [
                'ID' => 'A',
                'Status' => 'B',
                'Typ' => 'C',
                'Bezeichnung' => 'D',
                'URL' => 'E',
                'Farbe' => 'F',
                'Beschreibung' => 'G',
                'Beschreibung (Formatiert)' => 'H',
                'Gemeinden' => 'I',
                'Kontakte' => 'J',
            ];

            $row = 1;

            foreach($columns as $columnLabel => $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
                $sheet->setCellValue($column.$row, $columnLabel);
            }

            $sheet->getColumnDimension($columns['Typ'])->setWidth(30);
            $sheet->getColumnDimension($columns['Bezeichnung'])->setWidth(40);
            $sheet->getColumnDimension($columns['URL'])->setWidth(60);
            $sheet->getColumnDimension($columns['Beschreibung'])->setWidth(100);
            $sheet->getColumnDimension($columns['Beschreibung (Formatiert)'])->setWidth(100);
            $sheet->getColumnDimension($columns['Gemeinden'])->setWidth(200);
            $sheet->getColumnDimension($columns['Kontakte'])->setWidth(100);

            $row++;

            $regionTypes = [
                'nrp' => 'NRP-Regionen',
                'intercantonal' => 'Überkantonale Programme',
                'ris' => 'Regionale Innovationssysteme (RIS)',
                'cantonal' => 'Kantonale NRP-Fachstellen',
                'energy' => 'Energieregionen',
            ];

            /** @var Region $region */
            foreach($regions as $region) {
                $sheet->setCellValue($columns['ID'].$row, $region->getId());
                $sheet->setCellValue($columns['Status'].$row, $region->getIsPublic() ? 'Öffentlich' : 'Entwurf');
                $sheet->setCellValue($columns['Typ'].$row, $region->getType() && isset($regionTypes[$region->getType()]) ? $regionTypes[$region->getType()] : '');
                $sheet->setCellValue($columns['Bezeichnung'].$row, PvTrans::trans($region, 'name', $locale));
                $sheet->setCellValue($columns['URL'].$row, PvTrans::trans($region, 'url', $locale));
                $sheet->setCellValue($columns['Farbe'].$row, PvTrans::trans($region, 'color', $locale));
                $sheet->setCellValue($columns['Beschreibung'].$row, html_entity_decode(strip_tags(PvTrans::trans($region, 'description', $locale) ?: '')));
                $sheet->setCellValue($columns['Beschreibung (Formatiert)'].$row, PvTrans::trans($region, 'description', $locale));

                $entities = [];
                foreach($region->getCities() as $entity) {
                    $entities[] = PvTrans::trans($entity, 'name', $locale);
                }
                $sheet->setCellValue($columns['Gemeinden'].$row, implode(', ', $entities));

                $entities = [];
                /** @var Contact $entity */
                foreach($region->getContacts() as $entity) {
                    $address = '';
                    $company = null;

                    if(count($entity->getEmployments())) {
                        $company = $entity->getEmployments()[0]->getCompany();
                    }

                    if($company) {
                        $address .= $company->getCompanyName().PHP_EOL;
                    } else if($entity->getCompanyName()) {
                        $address .= $entity->getCompanyName().PHP_EOL;
                    }

                    if($entity->getFirstName()) {
                        $address .= ($entity->getAcademicTitle() ? $entity->getAcademicTitle().' ' : '').$entity->getFirstName().' '.$entity->getLastName().PHP_EOL;
                    }

                    if($company && $entity->getEmployments()[0]->getRole()) {
                        $address .= $entity->getEmployments()[0]->getRole().PHP_EOL;
                    }

                    if($company && $company->getStreet()) {
                        $address .= $company->getStreet().PHP_EOL;
                        $address .= $company->getZipCode().' '.$company->getCity().PHP_EOL;
                    } else if ($entity->getStreet()) {
                        $address .= $entity->getStreet().PHP_EOL;
                        $address .= $entity->getZipCode().' '.$entity->getCity().PHP_EOL;
                    }

                    if($entity->getEmail()) {
                        $address .= $entity->getEmail().PHP_EOL;
                    } else if ($company && $company->getStreet()) {
                        $address .= $company->getEmail().PHP_EOL;
                    }

                    if($company && $company->getWebsite()) {
                        $address .= $company->getWebsite().PHP_EOL;
                    } else if ($entity->getStreet()) {
                        $address .= $entity->getWebsite().PHP_EOL;
                    }

                    $entities[] = trim($address);
                }
                $sheet->setCellValue($columns['Kontakte'].$row, implode(PHP_EOL.PHP_EOL, $entities));

                /*$entities = [];
                foreach((PvTrans::trans($region, 'contacts', $locale) ?: []) as $entity) {
                    $contact = '';

                    if(isset($entity['name']) && $entity['name']) {
                        $contact.= $entity['name'].PHP_EOL;
                    }

                    if(isset($entity['firstName']) && isset($entity['lastName']) && $entity['firstName'] && $entity['lastName']) {

                        if(isset($entity['title']) && $entity['title']) {
                            $contact.= $entity['title'].' ';
                        }

                        $contact.= $entity['firstName'].' '.$entity['lastName'].PHP_EOL;
                    }

                    if(isset($entity['role']) && $entity['role']) {
                        $contact.= $entity['role'].PHP_EOL;
                    }

                    if(isset($entity['street']) && $entity['street']) {
                        $contact.= $entity['street'].PHP_EOL;
                    }

                    if(isset($entity['zipCode']) && isset($entity['city']) && $entity['zipCode'] && $entity['city']) {
                        $contact.= $entity['zipCode'].' '.$entity['city'].PHP_EOL;
                    }

                    if(isset($entity['phone']) && $entity['phone']) {
                        $contact.= $entity['phone'].PHP_EOL;
                    }

                    if(isset($entity['email']) && $entity['email']) {
                        $contact.= $entity['email'].PHP_EOL;
                    }

                    if(isset($entity['website']) && $entity['website']) {
                        $contact.= $entity['website'].PHP_EOL;
                    }

                    if(!trim($contact)) {
                        continue;
                    }

                    $entities[] = $contact;
                }
                $sheet->setCellValue($columns['Kontakte'].$row, implode(PHP_EOL.PHP_EOL, $entities));*/

                $row++;
            }

        }

        $writer = new Xlsx($spreadsheet);

        $extension = 'xlsx';
        $fileName = 'Regionen-'.date('Y-m-d_H-i-s').'.'.$extension;

        $tmpFile = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($tmpFile);

        $response = $this->file($tmpFile, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        $response->deleteFileAfterSend(true);

        return $response;
    }
    
}