<?php

namespace App\Service;

use App\Entity\Language;
use App\Entity\Location;
use App\Entity\Topic;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;

class PostService {

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

    public function validatePostPayload($payload)
    {
        if(($errors = $this->validateFields($payload, [
            'isPublic',
            'title',
            'description',
            'text',
            'date',
            'topics',
            'links',
            'videos',
            'images',
            'files',
            'translations',
        ])) !== true) {
            return $errors;
        }

        return true;
    }

    public function createPost($payload)
    {
        $post = new Post();

        $post->setCreatedAt(new \DateTime());

        $post = $this->applyPostPayload($payload, $post);

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function updatePost($post, $payload)
    {
        $post->setUpdatedAt(new \DateTime());

        $post = $this->applyPostPayload($payload, $post);

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function deletePost($post)
    {
        $this->em->remove($post);
        $this->em->flush();

        return $post;
    }

    public function applyPostPayload($payload, Post $post)
    {
        $post
            ->setIsPublic($payload['isPublic'])
            ->setTitle($payload['title'])
            ->setDescription($payload['description'])
            ->setText($payload['text'])
            ->setDate($payload['date'] ? new \DateTime(date('Y-m-d H:i:s', strtotime($payload['date']))) : null)
            ->setLinks($payload['links'] ?: [])
            ->setVideos($payload['videos'] ?: [])
            ->setImages($payload['images'] ?: [])
            ->setFiles($payload['files'] ?: [])
            ->setTopics(new ArrayCollection())
            ->setTranslations($payload['translations'] ?: [])
        ;

        foreach($payload['topics'] as $item) {
            $entity = null;
            if(array_key_exists('id', $item) && $item['id']) {
                $entity = $this->em->getRepository(Topic::class)->find($item['id']);
            }
            if(!$entity && array_key_exists('name', $item)) {
                $entity = $this->em->getRepository(Topic::class)
                    ->findOneBy(['name' => $item['name']]);
            }
            if($entity) {
                $post->addTopic($entity);
            }
        }

        return $post;
    }

}