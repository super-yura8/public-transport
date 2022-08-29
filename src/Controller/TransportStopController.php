<?php

namespace App\Controller;

use App\Entity\TransportStop;
use App\Form\TransportStopType;
use App\Repository\TransportRepository;
use App\Repository\TransportRunsRepository;
use App\Repository\TransportStartRepository;
use App\Repository\TransportStopRepository;
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

#[Route('api/transports/stops', name: 'api_transport_stop_', requirements: ['id' => '\d+'])]
class TransportStopController extends AbstractController
{

    private TransportStopRepository $transportStopRepository;
    private EntityManagerInterface $em;
    private Serializer $serializer;


    public function __construct(TransportStopRepository $transportStopRepository, EntityManagerInterface $em)
    {
        $this->transportStopRepository = $transportStopRepository;
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
        $data = $this->transportStopRepository->findAll();
        $data = $this->serializer
            ->toArray(
                $data,
                context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
            );
        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get($id): JsonResponse
    {
//        dd($this->get('security.token_storage')->getToken()->getUser());
        $this->denyAccessUnlessGranted('VIEW');
        $stop = $this->transportStopRepository->find($id);
        if (!is_null($stop)) {
            $stop = $this->serializer
                ->toArray(
                    $stop,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($stop);
        }
        return $this->json(['message' => 'The stop does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, FormsErrorManager $formsErrorManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('CREATE');
        $data = json_decode($request->getContent(), true);
        $stop = new TransportStop();
        $form = $this->createForm(TransportStopType::class, $stop);

        try {
            $form->submit($data);
        } catch (\Throwable $exception) {
            return $this->json(
                ['message' => 'The data is incorrect or is not full'],
                Response::HTTP_BAD_REQUEST
            );
        }
        if ($form->isValid()) {
            $this->em->persist($stop);
            $this->em->flush();
            $stop = $this->serializer
                ->toArray(
                    $stop,
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($stop);
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
        $stop = $this->transportStopRepository->find($id);
        if (!is_null($stop)) {
            $this->transportStopRepository->remove($stop, true);
            return $this->json(['message' => 'The stop is deleted']);
        }
        return $this->json(['message' => 'The stop does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH', 'PUT'])]
    public function update(Request $request, FormsErrorManager $formsErrorManager, $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('UPDATE');
        $data = json_decode($request->getContent(), true);
        $stop = $this->transportStopRepository->find($id);
        if (!is_null($stop)) {
            $form = $this->createForm(TransportStopType::class, $stop);
            $clearMissing = $request->getMethod() != 'PATCH';
            try {
                $form->submit($data, $clearMissing);
            } catch (\Throwable $exception) {
                return $this->json(
                    ['message' => 'The data is incorrect or is not full'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($form->isValid()) {
                $this->em->flush();
                $stop = $this->serializer
                    ->toArray(
                        $stop,
                        context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                    );
                return $this->json($stop);
            } else {
                return $this->json([
                    'message' => $formsErrorManager->getErrorsFromForm($form)
                ], Response::HTTP_CONFLICT);
            }
        }
        return $this->json(['message' => 'The transport does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}/transports', name: 'transports', methods: ['GET'])]
    public function getTransportsByStop($id, TransportRepository $transportRepository)
    {
        $this->denyAccessUnlessGranted('VIEW');
        if (!is_null($this->transportStopRepository->find($id))) {
            $transports = $this->serializer
                ->toArray(
                    $transportRepository->findByStop($id),
                    context: SerializationContext::create()->setGroups(['TRANSPORT_PUBLIC'])
                );
            return $this->json($transports);
        }
        return $this->json(['message' => 'The stop does not exist'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{stop}/transports/{transport}', name: 'transports_times',
        requirements: ['stop' => '\d+', 'transport' => '\d+'], methods: ['GET'])]
    public function getTransportDestinationTimes(
        $stop,
        $transport,
        TransportRunsRepository $transportRunsRepository,
        TransportStartRepository $transportStartRepository
    )
    {
        $transportRun = $transportRunsRepository->findOneBy(['transport' => $transport, 'transportStop' => $stop]);
        $transportStart = $transportStartRepository->findOneBy(['transport' => $transport]);
        if (!is_null($transportStart)) {
            if (!is_null($transportRun)) {
                $times = $transportStart->getTimes();
                $arrivalTime = $transportRun->getArrivalTime();
                $times = array_map(
                    function ($x) use ($arrivalTime) {
                        return $x + $arrivalTime;
                    },
                    $times
                );
                return $this->json($times);
            }
            return $this->json(['message' => "The transport route has not yet been launched"], Response::HTTP_NOT_FOUND);
        }
        return $this->json(['message' => 'The stop does not exist'], Response::HTTP_NOT_FOUND);
    }
}
