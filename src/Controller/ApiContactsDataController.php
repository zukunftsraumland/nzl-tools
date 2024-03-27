<?php

namespace App\Controller;

use App\Entity\ContactGroup;
use App\Entity\Employment;
use App\Service\ContactService;
use App\Util\PvTrans;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route(path: '/api/v1/contacts-data', name: 'api_contacts_data_')]
class ApiContactsDataController extends AbstractController
{
    
    #[Route(path: '/{contactType}', name: 'index', methods: ['GET'])]
    #[OA\Parameter(
        name: 'ids[]',
        description: 'Set specific ids to select',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'integer')),
    )]
    #[OA\Parameter(
        name: 'term',
        description: 'Search term',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'status[]',
        description: 'Return contacts by status',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['public', 'draft']),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific types',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'contactGroup[]',
        description: 'Include only specific contact groups (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'state[]',
        description: 'Include only specific states (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'country[]',
        description: 'Include only specific countries (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'language[]',
        description: 'Include only specific languages (both name or id are valid values)',
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
        description: 'Returns all contacts and their data',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class, groups: ['id', 'contact']))
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function index(string $contactType, Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(Contact::class, 'c')
            ->distinct()
        ;

        if($request->get('ids') && !is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', array_map('trim', explode(',', $request->get('ids'))))
            ;
        }

        if($request->get('ids') && is_array($request->get('ids'))) {
            $qb
                ->andWhere('c.id IN (:ids)')
                ->setParameter('ids', $request->get('ids'))
            ;
        }

        if($request->get('term')) {
            foreach(explode(' ', (string)$request->get('term')) as $term) {
                $qb
                    ->andWhere('(c.id LIKE :term OR c.companyName LIKE :term OR c.firstName LIKE :term OR c.lastName LIKE :term OR c.translations LIKE :term OR c.gender LIKE :term OR c.street LIKE :term OR c.zipCode LIKE :term OR c.city LIKE :term OR c.email LIKE :term OR c.phone LIKE :term OR c.website LIKE :term OR c.description LIKE :term)')
                    ->setParameter('term', '%'.$term.'%');
            }
        }

        if($request->get('type') && is_array($request->get('type')) && count($request->get('type'))) {

            $typeQuery = [];

            foreach($request->get('type') as $key => $type) {

                $typeQuery[] = 'c.type = :type'.$key;

                $qb
                    ->setParameter('type'.$key, $type)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $typeQuery))
            ;

        }

        if($request->get('status') && is_array($request->get('status')) && count($request->get('status'))) {

            $statusQuery = [];

            foreach($request->get('status') as $key => $status) {

                $statusQuery[] = 'c.isPublic = :isPublic'.$key;

                $qb
                    ->setParameter('isPublic'.$key, $status === 'public')
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $statusQuery))
            ;

        }

        if(!$this->isGranted('ROLE_EDITOR')) {
            $qb->andWhere('c.isPublic = TRUE');
        }

        if($request->get('contactGroup') && is_array($request->get('contactGroup')) && count($request->get('contactGroup'))) {

            $contactGroupQuery = [];

            $qb
                ->leftJoin('c.contactGroups', 'contactGroup')
            ;

            foreach($request->get('contactGroup') as $key => $contactGroup) {

                $contactGroupQuery[] = 'contactGroup.name = :contactGroup'.$key.' OR contactGroup.id = :contactGroupId'.$key;

                $qb
                    ->setParameter('contactGroup'.$key, $contactGroup)
                    ->setParameter('contactGroupId'.$key, $contactGroup)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $contactGroupQuery))
            ;
        }

        if($request->get('state') && is_array($request->get('state')) && count($request->get('state'))) {

            $stateQuery = [];

            foreach($request->get('state') as $key => $state) {

                $stateQuery[] = 'state'.$key.'.name = :state'.$key.' OR state'.$key.'.id = :stateId'.$key;

                $qb
                    ->leftJoin('c.state', 'state'.$key)
                    ->setParameter('state'.$key, $state)
                    ->setParameter('stateId'.$key, $state)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $stateQuery))
            ;
        }

        if($request->get('country') && is_array($request->get('country')) && count($request->get('country'))) {

            $countryQuery = [];

            foreach($request->get('country') as $key => $country) {

                $countryQuery[] = 'country'.$key.'.name = :country'.$key.' OR country'.$key.'.id = :countryId'.$key;

                $qb
                    ->leftJoin('c.country', 'country'.$key)
                    ->setParameter('country'.$key, $country)
                    ->setParameter('countryId'.$key, $country)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $countryQuery))
            ;
        }

        if($request->get('language') && is_array($request->get('language')) && count($request->get('language'))) {

            $languageQuery = [];

            foreach($request->get('language') as $key => $language) {

                $languageQuery[] = 'language'.$key.'.name = :language'.$key.' OR language'.$key.'.id = :languageId'.$key;

                $qb
                    ->leftJoin('c.language', 'language'.$key)
                    ->setParameter('language'.$key, $language)
                    ->setParameter('languageId'.$key, $language)
                ;
            }

            $qb
                ->andWhere(implode(' OR ', $languageQuery))
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

                if(!in_array($orderBy, ['id', 'createdAt', 'updatedAt', 'firstName', 'lastName', 'companyName', 'type'])) {
                    continue;
                }

                $direction = 'ASC';

                if($request->get('orderDirection') && is_array($request->get('orderDirection')) &&
                    count($request->get('orderDirection')) && array_key_exists($key, $request->get('orderDirection')) &&
                    in_array($request->get('orderDirection')[$key], ['ASC', 'DESC'])) {
                    $direction = $request->get('orderDirection')[$key];
                }

                $qb
                    ->addOrderBy('c.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('c.id', 'ASC')
            ;
        }

        $contacts = $qb->getQuery()->getResult();

        $contactsResult = $normalizer->normalize($contacts, null, [
            'groups' => ['id', 'contact', 'country', 'state', 'language']
        ]);

        $qb = $em->createQueryBuilder();

        $qb
            ->select('e, co')
            ->from(Employment::class, 'e')
        ;

        $type = $contactType ?: 'employee';
        $typeOther = $contactType === 'company' ? 'employee' : 'company';

        if($contactType === 'company') {
            $qb
                ->leftJoin('e.employee', 'co')
                ->andWhere('e.company IN (:contacts)')
            ;

        } else {
            $qb
                ->leftJoin('e.company', 'co')
                ->andWhere('e.employee IN (:contacts)')
            ;
        }

        $qb
            ->setParameter('contacts', $contacts)
        ;

        $employments = $qb->getQuery()->getResult();

        $employmentsResult = $normalizer->normalize($employments, null, [
            'groups' => ['id', 'employment', 'contact'],
            'attributes' => [
                'id',
                'role' => [
                    'name',
                    'translations',
                ],
                $typeOther => [
                    'id',
                    'name',
                    'companyName',
                    'specification',
                    'street',
                    'zipCode',
                    'city',
                    'state',
                    'phone',
                    'email',
                    'website',
                    'translations',
                ],
                $type => [
                    'id',
                ],
                'contactGroups' => [
                    'id',
                ],
                'position',
                'translations',
            ],
        ]);

        $resultData = [
            'contacts' => $contactsResult,
            'employments' => $employmentsResult,
        ];

        return $this->json($resultData);
    }
    
}