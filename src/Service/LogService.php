<?php

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class LogService {

    protected $em;
    protected $requestStack;
    protected $security;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, Security $security)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->security = $security;
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

    public function validateLogPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'context',
            'category',
            'action',
            'value',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createLog($payload)
    {
        $log = new Log();

        $log->setCreatedAt(new \DateTime());

        $log = $this->applyLogPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function updateLog($log, $payload)
    {
        $log = $this->applyLogPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function deleteLog($log)
    {
        $this->em->remove($log);
        $this->em->flush();

        return $log;
    }

    public function applyLogPayload($payload, Log $log)
    {
        $log
            ->setContext($payload['context'])
            ->setCategory($payload['category'])
            ->setAction($payload['action'])
            ->setValue($payload['value'])
            ->setUsername($this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null)
        ;

        return $log;
    }

    public function validateTelemetryPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
                'category',
                'action',
                'value',
            ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createTelemetry($payload)
    {
        $log = new Log();

        $log->setCreatedAt(new \DateTime());

        $log = $this->applyTelemetryPayload($payload, $log);

        $this->em->persist($log);
        $this->em->flush();

        return $log;
    }

    public function applyTelemetryPayload($payload, Log $log)
    {
        $fingerprint = $this->requestStack->getCurrentRequest()->getClientIp() ?: 'X';
        $fingerprint .= ':';
        $fingerprint .= $this->requestStack->getCurrentRequest()->headers->get('User-Agent') ?: 'X';
        $fingerprint .= ':';
        $fingerprint .= $this->requestStack->getCurrentRequest()->headers->get('Accept-Language') ?: 'X';

        $log
            ->setContext('Telemetry')
            ->setCategory($payload['category'])
            ->setAction($payload['action'])
            ->setValue($payload['value'])
            ->setReferer($this->requestStack->getCurrentRequest()->headers->get('Referer'))
            ->setUsername($this->security->getUser() ? $this->security->getUser()->getUserIdentifier() : null)
            ->setFingerprint(md5($fingerprint))
        ;

        return $log;
    }

}