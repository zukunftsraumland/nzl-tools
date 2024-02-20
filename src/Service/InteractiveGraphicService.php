<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Event;
use App\Entity\EventCollection;
use App\Entity\Inbox;
use App\Entity\InteractiveGraphic;
use App\Entity\Project;
use App\Entity\ProjectCollection;
use Doctrine\ORM\EntityManagerInterface;

class InteractiveGraphicService {

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function validateFields($payload, $fields = [])
    {
        foreach($fields as $field) {
            if(!array_key_exists($field, $payload)) {
                return [
                    [
                        'field' => $field,
                    ]
                ];
            }
        }

        return true;
    }

    public function validateInteractiveGraphicPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'title',
            'description',
            'svg',
            'selector',
            'config',
            'start',
            'copyright',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createInteractiveGraphic($payload)
    {
        $interactiveGraphic = new InteractiveGraphic();

        $interactiveGraphic->setCreatedAt(new \DateTime());

        $interactiveGraphic = $this->applyInteractiveGraphicPayload($payload, $interactiveGraphic);

        $this->em->persist($interactiveGraphic);
        $this->em->flush();

        return $interactiveGraphic;
    }

    public function updateInteractiveGraphic($interactiveGraphic, $payload)
    {
        $interactiveGraphic->setUpdatedAt(new \DateTime());

        $interactiveGraphic = $this->applyInteractiveGraphicPayload($payload, $interactiveGraphic);

        $this->em->persist($interactiveGraphic);
        $this->em->flush();

        return $interactiveGraphic;
    }

    public function deleteInteractiveGraphic($interactiveGraphic)
    {
        $this->em->remove($interactiveGraphic);
        $this->em->flush();

        return $interactiveGraphic;
    }

    public function applyInteractiveGraphicPayload($payload, InteractiveGraphic $interactiveGraphic)
    {
        $interactiveGraphic
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setSelector($payload['selector'])
            ->setStart($payload['start'])
            ->setCopyright($payload['copyright'])
            ->setSvg($payload['svg'])
            ->setConfig($payload['config'] ?: [])
        ;

        return $interactiveGraphic;
    }

}