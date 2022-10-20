<?php

namespace App\Controller;

use App\Entity\TransportRun;
use App\Form\TransportRunType;
use App\Repository\TransportRunsRepository;
use App\Service\FormsErrorManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/transports/runs', name: 'api_transport_run_', requirements: ['id' => '\d+'], )]
class TransportRunController extends AbstractController
{

    private TransportRunsRepository $transportRunsRepository;
    private EntityManagerInterface $em;
    private Serializer $serializer;

    public function __construct(TransportRunsRepository $transportRunsRepository, EntityManagerInterface $em)
    {
        $this->transportRunsRepository = $transportRunsRepository;
        $this->em = $em;
        $this->serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(
                new IdenticalPropertyNamingStrategy()
            ))->build();
    }

    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $data = $this->transportRunsRepository->findAll();
        $data = $this->serializer
            ->toArray(
                $data,
                context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
            );
        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $data = $this->transportRunsRepository->find($id);
        if(!is_null($data)) {
            $data = $this->serializer
                ->toArray(
                    $data,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                );
            return $this->json($data);
        }
        return $this->json(['message' => 'The run does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, FormsErrorManager $formsErrorManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('CREATE');
        $data = json_decode($request->getContent(), true);
        $run = new TransportRun();
        $form = $this->createForm(TransportRunType::class, $run);

        try {
            $form->submit($data);
        } catch (\Throwable $exception) {
            return $this->json(
                ['message'=> 'The data is incorrect or is not full'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if ($form->isValid()) {
            $this->em->persist($run);
            $this->em->flush();
            $run = $this->serializer
                ->toArray(
                    $run,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                );
            return $this->json($run);
        } else {
            return $this->json([
                'message' => $formsErrorManager->getErrorsFromForm($form)
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('DELETE');
        $run = $this->transportRunsRepository->find($id);
        if (!is_null($run)) {
            $this->transportRunsRepository->remove($run, true);
            return $this->json(['message' => 'The run is deleted']);
        }
        return $this->json(['message' => 'The run does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH', 'PUT'])]
    public function update(Request $request, FormsErrorManager $formsErrorManager, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('UPDATE');
        $data = json_decode($request->getContent(), true);
        $run = $this->transportRunsRepository->find($id);
        if (!is_null($run)) {
            $form = $this->createForm(TransportRunType::class, $run);
            $clearMissing = $request->getMethod() != 'PATCH';
            try {
                $form->submit($data, $clearMissing);
            } catch (\Throwable $exception) {
                return $this->json(
                    ['message'=> 'The data is incorrect or is not full'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($form->isValid()) {
                $this->em->flush();
                $run = $this->serializer
                    ->toArray(
                        $run,
                        context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                    );
                return $this->json($run);
            } else {
                return $this->json([
                    'message' => $formsErrorManager->getErrorsFromForm($form)
                ], Response::HTTP_BAD_REQUEST);
            }
        }
        return $this->json(['message' => 'The run does not exist'], Response::HTTP_NOT_FOUND);
    }
}
