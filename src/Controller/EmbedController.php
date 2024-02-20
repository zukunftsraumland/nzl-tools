<?php

namespace App\Controller;

use App\Entity\Education;
use App\Entity\Event;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/embed', name: 'embed_')]
class EmbedController extends AbstractController
{

    #[Route('/projects/documentation.html', name: 'projects_documentation')]
    public function projectsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/projects-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Projects Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/projects-{_locale}.html', name: 'iframe_projects')]
    public function iframeProjects(): Response
    {
        return $this->render('embed/iframe/projects.html.twig', []);
    }

    #[Route('/static/projects/{id}-{_locale}.html', name: 'static_project')]
    public function staticProject(Request $request, EntityManagerInterface $em): Response
    {
        $project = $em->getRepository(Project::class)->find($request->get('id'));

        if(!$project) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/project.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/static/projects/{id}-{_locale}/meta.html', name: 'static_project_meta')]
    public function staticProjectMeta(Request $request, EntityManagerInterface $em): Response
    {
        $project = $em->getRepository(Project::class)->find($request->get('id'));

        if(!$project) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/project-meta.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project-collection/documentation.html', name: 'project_collection_documentation')]
    public function projectCollectionDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/project-collection-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Project Collection Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/project-collection/{collectionId}-{_locale}.html', name: 'iframe_project_collection')]
    public function iframeProjectCollection(): Response
    {
        return $this->render('embed/iframe/project-collection.html.twig', []);
    }

    #[Route('/events/documentation.html', name: 'events_documentation')]
    public function eventsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/events-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Events Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/events-{_locale}.html', name: 'iframe_events')]
    public function iframeEvents(): Response
    {
        return $this->render('embed/iframe/events.html.twig', []);
    }

    #[Route('/static/events/{id}-{_locale}.html', name: 'static_event')]
    public function staticEvent(Request $request, EntityManagerInterface $em): Response
    {
        $event = $em->getRepository(Event::class)->find($request->get('id'));

        if(!$event) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/event.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/static/events/{id}-{_locale}/meta.html', name: 'static_event_meta')]
    public function staticEventMeta(Request $request, EntityManagerInterface $em): Response
    {
        $event = $em->getRepository(Event::class)->find($request->get('id'));

        if(!$event) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/event-meta.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event-collection/documentation.html', name: 'event_collection_documentation')]
    public function eventCollectionDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/event-collection-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Event Collection Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/event-collection/{collectionId}-{_locale}.html', name: 'iframe_event_collection')]
    public function iframeEventCollection(): Response
    {
        return $this->render('embed/iframe/event-collection.html.twig', []);
    }

    #[Route('/educations/documentation.html', name: 'educations_documentation')]
    public function educationsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/educations-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Educations Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/educations-{_locale}.html', name: 'iframe_educations')]
    public function iframeEducations(): Response
    {
        return $this->render('embed/iframe/educations.html.twig', []);
    }

    #[Route('/static/educations/{id}-{_locale}.html', name: 'static_education')]
    public function staticEducations(Request $request, EntityManagerInterface $em): Response
    {
        $education = $em->getRepository(Education::class)->find($request->get('id'));

        if(!$education) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/education.html.twig', [
            'education' => $education,
        ]);
    }

    #[Route('/static/educations/{id}-{_locale}/meta.html', name: 'static_education_meta')]
    public function staticEducationMeta(Request $request, EntityManagerInterface $em): Response
    {
        $education = $em->getRepository(Education::class)->find($request->get('id'));

        if(!$education) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/education-meta.html.twig', [
            'education' => $education,
        ]);
    }

    #[Route('/jobs/documentation.html', name: 'jobs_documentation')]
    public function jobsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/jobs-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Jobs Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/jobs-{_locale}.html', name: 'iframe_jobs')]
    public function iframeJobs(): Response
    {
        return $this->render('embed/iframe/jobs.html.twig', []);
    }

    #[Route('/static/jobs/{id}-{_locale}.html', name: 'static_job')]
    public function staticJobs(Request $request, EntityManagerInterface $em): Response
    {
        $job = $em->getRepository(Job::class)->find($request->get('id'));

        if(!$job) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/job.html.twig', [
            'job' => $job,
        ]);
    }

    #[Route('/static/jobs/{id}-{_locale}/meta.html', name: 'static_job_meta')]
    public function staticJobMeta(Request $request, EntityManagerInterface $em): Response
    {
        $job = $em->getRepository(Job::class)->find($request->get('id'));

        if(!$job) {
            throw $this->createNotFoundException();
        }

        return $this->render('embed/static/job-meta.html.twig', [
            'job' => $job,
        ]);
    }

    #[Route('/regions/documentation.html', name: 'regions_documentation')]
    public function regionsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/regions-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Regions Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/regions-{_locale}.html', name: 'iframe_regions')]
    public function iframeRegions(): Response
    {
        return $this->render('embed/iframe/regions.html.twig', []);
    }

    #[Route('/posts/documentation.html', name: 'posts_documentation')]
    public function postsDocumentation(string $host, string $instanceId): Response
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => true,
        ]);
        $markdown = file_get_contents(__DIR__.'/../../documentation/posts-embed.md');
        $markdown = str_replace('%HOST%', $host, $markdown);
        $markdown = str_replace('%INSTANCE_ID%', $instanceId, $markdown);

        return $this->render('embed/documentation.html.twig', [
            'title' => $instanceId.'Posts Documentation',
            'documentation' => $converter->convert($markdown),
        ]);
    }

    #[Route('/iframe/posts-{_locale}.html', name: 'iframe_posts')]
    public function iframePosts(): Response
    {
        return $this->render('embed/iframe/posts.html.twig', []);
    }

}
