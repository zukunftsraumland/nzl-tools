<?php

namespace App\Controller;

use App\Service\ContactService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use App\Entity\Contact;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/contacts', name: 'api_contacts_')]
class ApiContactsController extends AbstractController
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
        description: 'Include only specific contact types (both name or id are valid values)',
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
        description: 'Returns all contacts',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Contact::class, groups: ['id', 'contact']))
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('c')
            ->from(Contact::class, 'c')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(c.companyName LIKE :term OR c.firstName LIKE :term OR c.lastName LIKE :term OR c.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
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
                    ->addOrderBy('c.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('c.id', 'ASC')
            ;
        }

        $contacts = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($contacts, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactService $contactService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $contactService->validateContactPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $contact = $contactService->createContact($payload);

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an contact',
        content: new OA\JsonContent(
            ref: new Model(type: Contact::class, groups: ['id', 'contact'])
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactService $contactService): JsonResponse
    {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $contactService->validateContactPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $contact = $contactService->updateContact($contact, $payload);

        $result = $normalizer->normalize($contact, null, [
            'groups' => ['id', 'contact'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an contact',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Contacts')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, ContactService $contactService): JsonResponse
    {
        $contact = $em->getRepository(Contact::class)
            ->find($request->get('id'));

        $contactService->deleteContact($contact);

        return $this->json([]);
    }
    
}