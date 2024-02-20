<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCollection;
use App\Service\EventCollectionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/event-collections', name: 'api_event_collections_')]
class ApiEventCollectionsController extends AbstractController
{

    #[Route(path: '', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns all event collections',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: EventCollection::class, groups: ['id', 'event_collection']))
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    public function index(EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $eventCollections = $em->getRepository(EventCollection::class)->findAll();

        $result = $normalizer->normalize($eventCollections, null, [
            'groups' => ['id', 'event_collection'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single event collection',
        content: new OA\JsonContent(
            ref: new Model(type: EventCollection::class, groups: ['id', 'event_collection'])
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $eventCollection = $em->getRepository(EventCollection::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($eventCollection, null, [
            'groups' => ['id', 'event_collection'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an event collection',
        content: new OA\JsonContent(
            ref: new Model(type: EventCollection::class, groups: ['id', 'event_collection'])
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, NormalizerInterface $normalizer,
                           EventCollectionService $eventCollectionService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $eventCollectionService->validateEventCollectionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $eventCollection = $eventCollectionService->createEventCollection($payload);

        $result = $normalizer->normalize($eventCollection, null, [
            'groups' => ['id', 'event_collection'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an event collection',
        content: new OA\JsonContent(
            ref: new Model(type: EventCollection::class, groups: ['id', 'event_collection'])
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em, NormalizerInterface $normalizer,
                           EventCollectionService $eventCollectionService): JsonResponse
    {
        $eventCollection = $em->getRepository(EventCollection::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $eventCollectionService->validateEventCollectionPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $eventCollection = $eventCollectionService->updateEventCollection($eventCollection, $payload);

        $result = $normalizer->normalize($eventCollection, null, [
            'groups' => ['id', 'event_collection'],
        ]);

        return $this->json($result);
    }

    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an event collection',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EventCollectionService $eventCollectionService): JsonResponse
    {
        $eventCollection = $em->getRepository(EventCollection::class)
            ->find($request->get('id'));

        $eventCollectionService->deleteEventCollection($eventCollection);

        return new JsonResponse([]);
    }

    #[Route(path: '/public/{id}', name: 'public', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns events for a given event collection',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(
                    property: 'events',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Event::class, groups: ['id', 'event']))
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Tag(name: 'Event Collections')]
    public function publicAction(Request $request, EntityManagerInterface $em,
                                 NormalizerInterface $normalizer): JsonResponse
    {
        $eventCollection = $em
            ->getRepository(EventCollection::class)
            ->find($request->get('id'));

        if(!$eventCollection) {
            throw $this->createNotFoundException();
        }

        $result = [
            'title' => $eventCollection->getTitle(),
            'events' => [],
        ];

        if(!$eventCollection->getIsDynamic()) {

            foreach($eventCollection->getSelection() as $selection) {

                $event = $em->getRepository(Event::class)
                    ->find($selection['id']);

                $result['events'][] = $normalizer->normalize($event, null, [
                    'groups' => ['id', 'event', 'topic', 'language', 'location'],
                ]);

            }

            return $this->json($result);

        }

        $qb = $em
            ->getRepository(Event::class)
            ->createQueryBuilder('event')
            ->innerJoin('event.topics', 'topic')
            ->innerJoin('event.languages', 'language')
            ->innerJoin('event.locations', 'location')
        ;

        $types = [];

        foreach($eventCollection->getFilters() as $key => $filter) {

            if($filter['type'] === 'type') {
                $types[] = $filter['value'];
            }

        }

        if(count($types)) {
            $qb->andWhere('event.type IN (:types)');
            $qb->setParameter('types', $types);
        }

        foreach($eventCollection->getFilters() as $key => $filter) {

            if($filter['type'] === 'status' && $filter['value'] === 'archive') {
                $qb->andWhere('event.endDate IS NOT NULL AND event.endDate < :endDate'.$key);
                $qb->setParameter('endDate'.$key, new \DateTime());
            }

            if($filter['type'] === 'status' && $filter['value'] === 'current') {
                $qb->andWhere('event.endDate IS NULL OR event.endDate >= :endDate'.$key);
                $qb->setParameter('endDate'.$key, new \DateTime());
            }

            if($filter['type'] === 'topic') {
                $qb->andWhere(':topic'.$key.' = topic.name');
                $qb->setParameter('topic'.$key, $filter['value']);
            }

            if($filter['type'] === 'language') {
                $qb->andWhere(':language'.$key.' = language.name');
                $qb->setParameter('language'.$key, $filter['value']);
            }

            if($filter['type'] === 'location') {
                $qb->andWhere(':location'.$key.' = location.name');
                $qb->setParameter('location'.$key, $filter['value']);
            }

        }

        $qb->addOrderBy('event.startDate', 'ASC');

        $events = $qb->getQuery()->getResult();

        foreach($events as $event) {

            $result['events'][] = $normalizer->normalize($event, null, [
                'groups' => ['id', 'event', 'topic', 'language', 'location'],
            ]);

        }

        return $this->json($result);

    }

}