<?php

namespace App\Controller;

use App\Entity\TransportStart;
use App\Form\TransportStartType;
use App\Form\TransportType;
use App\Repository\TransportStartRepository;
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

#[Route('/api/transports/starts', name: 'api_transport_start_', requirements: ['id' => '\d+'])]
class TransportStartController extends AbstractController
{
    private TransportStartRepository $transportStartRepository;
    private EntityManagerInterface $em;
    private Serializer $serializer;


    public function __construct(TransportStartRepository $transportStartRepository, EntityManagerInterface $em)
    {
        $this->transportStartRepository = $transportStartRepository;
        $this->em = $em;
        $this->serializer = SerializerBuilder::create()->setPropertyNamingStrategy(
            new SerializedNameAnnotationStrategy(
                new IdenticalPropertyNamingStrategy()
            ))->build();
    }

    #[Route('/', name: 'get_all', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $data = $this->transportStartRepository->findAll();
        $data = $this->serializer->toArray($data, context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC']));
        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW');
        $start = $this->transportStartRepository->find($id);
        if (!is_null($start)) {
            $start = $this->serializer
                ->toArray(
                    $start,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                );
            return $this->json($start);
        }
        return $this->json(['message' => 'The start does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, FormsErrorManager $formsErrorManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('CREATE');
        $data = json_decode($request->getContent(), true);
        $start = new TransportStart();
//        dd($data);
        $form = $this->createForm(TransportStartType::class, $start);

        try {
            $form->submit($data);
        } catch (\Throwable $exception) {
            return $this->json(
                ['message'=> 'The data is incorrect or is not full'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if ($form->isValid()) {
            $this->em->persist($start);
            $this->em->flush();
            $start = $this->serializer
                ->toArray(
                    $start,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                );
            return $this->json($start);
        } else {
            return $this->json([
                'message' => $formsErrorManager->getErrorsFromForm($form)
            ], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        $this->denyAccessUnlessGranted('DELETE');
        $start = $this->transportStartRepository->find($id);
        if (!is_null($start)) {
            $this->transportStartRepository->remove($start, true);
            return $this->json(['message' => 'The start is deleted']);
        }
        return $this->json(['message' => 'The start does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH', 'PUT'])]
    public function update(Request $request, FormsErrorManager $formsErrorManager, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('UPDATE');
        $data = json_decode($request->getContent(), true);
        $start = $this->transportStartRepository->find($id);
        if (!is_null($start)) {
            $form = $this->createForm(TransportStartType::class, $start);
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
                    $start = $this->serializer
                    ->toArray(
                        $start,
                        context: SerializationContext::create()->setGroups(['TRANSPORT_RUN_PUBLIC'])
                    );
                return $this->json($start);
            } else {
                return $this->json([
                    'message' => $formsErrorManager->getErrorsFromForm($form)
                ], Response::HTTP_CONFLICT);
            }
        }
        return $this->json(['message' => 'The start does not exist'], Response::HTTP_NOT_FOUND);
    }
}
