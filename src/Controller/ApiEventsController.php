<?php

namespace App\Controller;

use App\Service\EventService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Jsvrcek\ICS\CalendarExport;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\Model\Description\Location;
use Jsvrcek\ICS\Utility\Formatter;
use App\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route(path: '/api/v1/events', name: 'api_events_')]
class ApiEventsController extends AbstractController
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
        name: 'archive',
        description: 'Return or exclude archived events',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', enum: [0, 1]),
    )]
    #[OA\Parameter(
        name: 'type[]',
        description: 'Include only specific event types',
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
        name: 'location[]',
        description: 'Include only specific locations (both name or id are valid values)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
    )]
    #[OA\Parameter(
        name: 'topic[]',
        description: 'Include only specific topics (both name or id are valid values)',
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
        description: 'Returns all events',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Event::class, groups: ['id', 'event']))
        )
    )]
    #[OA\Tag(name: 'Events')]
    public function index(Request $request, EntityManagerInterface $em,
                          NormalizerInterface $normalizer): JsonResponse
    {
        $qb = $em->createQueryBuilder();

        $qb
            ->select('e')
            ->from(Event::class, 'e')
        ;

        if($request->get('term')) {
            $qb
                ->andWhere('(e.title LIKE :term OR e.description LIKE :term OR e.text LIKE :term OR e.translations LIKE :term)')
                ->setParameter('term', '%'.$request->get('term').'%');
        }

        if($request->get('type') && !is_array($request->get('type'))) {
            $qb
                ->andWhere('e.type = :type')
                ->setParameter('type', $request->get('type'))
            ;
        }

        if($request->get('type') && is_array($request->get('type'))) {
            $qb
                ->andWhere('e.type IN (:types)')
                ->setParameter('types', $request->get('type'))
            ;
        }

        if($request->get('archive')) {
            $qb
                ->andWhere('e.endDate < :endDate')
                ->setParameter('endDate', new \DateTime(), Types::DATETIME_MUTABLE)
                ->addOrderBy('e.startDate', 'DESC')
            ;
        }

        if($request->get('archive') === '0') {
            $qb
                ->andWhere('e.endDate >= :endDate')
                ->setParameter('endDate', new \DateTime(), Types::DATETIME_MUTABLE)
                ->addOrderBy('e.startDate', 'ASC')
            ;
        }

        if($request->get('language') && is_array($request->get('language')) && count($request->get('language'))) {
            foreach($request->get('language') as $key => $language) {
                $qb
                    ->leftJoin('e.languages', 'language'.$key)
                    ->andWhere('language'.$key.'.name = :language'.$key.' OR language'.$key.'.id = :languageId'.$key)
                    ->setParameter('language'.$key, $language)
                    ->setParameter('languageId'.$key, $language)
                ;
            }
        }

        if($request->get('location') && is_array($request->get('location')) && count($request->get('location'))) {
            foreach($request->get('location') as $key => $location) {
                $qb
                    ->leftJoin('e.locations', 'location'.$key)
                    ->andWhere('location'.$key.'.name = :location'.$key.' OR location'.$key.'.id = :locationId'.$key)
                    ->setParameter('location'.$key, $location)
                    ->setParameter('locationId'.$key, $location)
                ;
            }
        }

        if($request->get('topic') && is_array($request->get('topic')) && count($request->get('topic'))) {

            foreach($request->get('topic') as $key => $topic) {

                $qb
                    ->leftJoin('e.topics', 'topic'.$key)
                    ->andWhere('topic'.$key.'.name = :topic'.$key.' OR topic'.$key.'.id = :topicId'.$key)
                    ->setParameter('topic'.$key, $topic)
                    ->setParameter('topicId'.$key, $topic)
                ;

            }

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
                    ->addOrderBy('e.'.$orderBy, $direction)
                ;

            }

        } else {
            $qb
                ->addOrderBy('e.startDate', 'DESC')
            ;
        }

        $events = $qb->getQuery()->getResult();

        $result = $normalizer->normalize($events, null, [
            'groups' => ['id', 'event', 'topic', 'language', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'get', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single event',
        content: new OA\JsonContent(
            ref: new Model(type: Event::class, groups: ['id', 'event'])
        )
    )]
    #[OA\Tag(name: 'Events')]
    public function find(Request $request, EntityManagerInterface $em,
                         NormalizerInterface $normalizer): JsonResponse
    {
        $event = $em->getRepository(Event::class)
            ->find($request->get('id'));

        $result = $normalizer->normalize($event, null, [
            'groups' => ['id', 'event', 'topic', 'language', 'location'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Create an event',
        content: new OA\JsonContent(
            ref: new Model(type: Event::class, groups: ['id', 'event'])
        )
    )]
    #[OA\Tag(name: 'Events')]
    #[Security(name: 'cookieAuth')]
    public function create(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EventService $eventService): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if(($errors = $eventService->validateEventPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $event = $eventService->createEvent($payload);

        $result = $normalizer->normalize($event, null, [
            'groups' => ['id', 'event'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'update', methods: ['PUT'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Update an event',
        content: new OA\JsonContent(
            ref: new Model(type: Event::class, groups: ['id', 'event'])
        )
    )]
    #[OA\Tag(name: 'Events')]
    #[Security(name: 'cookieAuth')]
    public function update(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EventService $eventService): JsonResponse
    {
        $event = $em->getRepository(Event::class)
            ->find($request->get('id'));

        $payload = json_decode($request->getContent(), true);

        if(($errors = $eventService->validateEventPayload($payload)) !== true) {
            return $this->json($errors, 400);
        }

        $event = $eventService->updateEvent($event, $payload);

        $result = $normalizer->normalize($event, null, [
            'groups' => ['id', 'event'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_EDITOR')]
    #[OA\Response(
        response: 200,
        description: 'Delete an event',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'string'),
            default: []
        )
    )]
    #[OA\Tag(name: 'Events')]
    #[Security(name: 'cookieAuth')]
    public function delete(Request $request, EntityManagerInterface $em,
                           NormalizerInterface $normalizer, EventService $eventService): JsonResponse
    {
        $event = $em->getRepository(Event::class)
            ->find($request->get('id'));

        $eventService->deleteEvent($event);

        return $this->json([]);
    }
    
    #[Route(path: '/public/{id}', name: 'public', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single event',
        content: new OA\JsonContent(
            ref: new Model(type: Event::class, groups: ['id', 'event'])
        )
    )]
    #[OA\Tag(name: 'Events')]
    public function publicAction(Request $request, EntityManagerInterface $em,
                                 NormalizerInterface $normalizer): JsonResponse
    {
        $event = $em
            ->getRepository(Event::class)
            ->find($request->get('id'));

        if(!$event) {
            throw $this->createNotFoundException();
        }

        $result = $normalizer->normalize($event, null, [
            'groups' => ['id', 'event'],
        ]);

        return $this->json($result);
    }
    
    #[Route(path: '/{_locale}/{id}.ics', name: 'ics', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a calendar file for the event',
        content: new OA\MediaType(mediaType: 'text/calendar', schema: new OA\Schema(type: 'string', format: 'binary'))
    )]
    #[OA\Tag(name: 'Events')]
    public function ics(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Event $event */
        $event = $em
            ->getRepository(Event::class)
            ->find($request->get('id'));

        if(!$event || (!$event->getIsPublic() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createNotFoundException();
        }

        $title = $event->getTitle();

        if($request->getLocale() !== 'de' && isset($event->getTranslations()[$request->getLocale()]['title']) && $event->getTranslations()[$request->getLocale()]['title']) {
            $title = $event->getTranslations()[$request->getLocale()]['title'];
        }

        $description = $event->getText();

        if($request->getLocale() !== 'de' && isset($event->getTranslations()[$request->getLocale()]['text']) && $event->getTranslations()[$request->getLocale()]['text']) {
            $description = $event->getTranslations()[$request->getLocale()]['text'];
        }

        $description = str_replace('</p>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h1>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h2>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h3>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h4>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h5>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('</h6>', PHP_EOL.PHP_EOL, $description);
        $description = str_replace('<ul>', PHP_EOL, $description);
        $description = str_replace('<ol>', PHP_EOL, $description);
        $description = str_replace('</ul>', PHP_EOL, $description);
        $description = str_replace('</ol>', PHP_EOL, $description);
        $description = str_replace('</li>', PHP_EOL, $description);
        $description = str_replace('</div>', PHP_EOL, $description);
        $description = strip_tags(html_entity_decode($description));

        $icsEvent = new CalendarEvent();

        $icsEvent
            ->setStart($event->getStartDate())
            ->setEnd($event->getEndDate())
            ->setSummary($title)
            ->setDescription($description)
            ->setUid('event-'.$request->getLocale().'-'.$event->getId())
        ;

        if($event->getLocation()) {
            $icsLocation = new Location();
            $icsLocation
                ->setName($event->getLocation())
            ;
            $icsEvent->addLocation($icsLocation);
        }

        $calendar = new Calendar();
        $calendar
            ->setProdId('-//Planval//EventCalendar//'.strtoupper($request->getLocale()))
            ->addEvent($icsEvent)
        ;

        $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
        $calendarExport->addCalendar($calendar);

        $response = new Response();
        $response->setContent($calendarExport->getStream());
        $response->headers->set('Content-Type', 'text/calendar');

        return $response;

    }
}