<?php

namespace App\Controller;

use App\Entity\Municipality;
use App\Entity\Province;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DefaultController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function index(Request $request): Response
    {
        // La función index carga los resultados de todas los municipios (páginados según el parámetro GET) y lo envía a la plantilla de Twig
        // También se envía a la plantilla de Twig el listado de provincias para que el usuario pueda seleccionar una a la hora de crear un municipio

        $maxResults = 10;

        $pagination = [
            'page' => max($request->query->getInt('page', 1), 1),
            'maxResults' => $maxResults,
        ];

        $municipalities = $this->entityManager->getRepository(Municipality::class)->getPaginatedMunicipalities($pagination);

        return $this->render('index.html.twig', [
            'municipalities' => $municipalities,
            'maxResults' => $maxResults,
            'provinces' => $this->entityManager->getRepository(Province::class)->findAll()
        ]);
    }

    public function getNearestMunicipalityToCardinal(Request $request): JsonResponse
    {
        // Example: http://localhost:8080/api/municipality/n?municipalities=[1,2]
        // En esta función se ha optado por obtener el municipio más cercana al punto cardinal a través de una consulta a la bbdd
        // También se ha optado por abstraer la consulta en el repositorio (parámetro municipality) y separarla de la función del controlador (parámetro cardinalPoint)

        $municipalityIds = json_decode($request->get('municipalities'));
        $cardinal = $request->get('cardinal');

        if (empty($municipalityIds)) {
            throw new BadRequestHttpException('Bad request parameters', null, 400);
        }

        switch ($cardinal) {
            case 'n':
                $cardinalName = 'north';
                break;
            case 's':
                $cardinalName = 'south';
                break;
            case 'e':
                $cardinalName = 'east';
                break;
            case 'w':
                $cardinalName = 'west';
                break;
        }

        return $this->json([
            'cardinalPoint' => $cardinalName,
            'municipality' => $this->entityManager->getRepository(Municipality::class)->getNearestByCardinal($municipalityIds, $cardinal),
        ]);
    }

    public function getMunicipalities(): JsonResponse
    {
        //En está función también se ha optado por abstraer la consulta al repositorio

        return $this->json([
            'municipalities' =>  $this->entityManager->getRepository(Municipality::class)->getMunicipalities()
        ]);
    }

    public function saveMunicipality(Request $request): JsonResponse
    {
        //En esta función se obtienen los parametros del request para crear un nuevo municipio.

        $slug = $request->get('slug');
        $name = $request->get('name');
        $latitude = $request->get('latitude', 0);
        $longitude = $request->get('longitude', 0);
        $province = $this->entityManager->getRepository(Province::class)->findOneBy(['id' => $request->get('province', 1)]);

        if (empty($slug) || empty($name) || empty($province)) {
            throw new BadRequestHttpException('Bad request parameters', null, 400);
        }

        $municipality = (new Municipality())
            ->setId(null)
            ->setSlug($slug)
            ->setName($name)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setProvince($province)
        ;

        $this->entityManager->persist($municipality);
        $this->entityManager->flush();

        return $this->json([
            'status' => $municipality->getName() . ' successfully created'
        ]);
    }

    public function deleteMunicipality(int $idMunicipality): JsonResponse
    {
        // En esta función se borra un municipio. Se comprueba antes que ese municipio existe.

        $municipality = $this->entityManager->getRepository(Municipality::class)
            ->findOneBy(['id' => $idMunicipality]);
        ;

        if (!$municipality) {
            throw new BadRequestHttpException('Requested municipality colud not be found', null, 400);;
        }

        $this->entityManager->remove($municipality);
        $this->entityManager->flush();

        return $this->json([
            'status' => $municipality->getName() . ' successfully deleted'
        ]);
    }

    public function getPercentageByProvince(Request $request): JsonResponse
    {
        $provincesIds = json_decode($request->get('provinces'));

        // Aseguramos que se reciben los datos correctos del request
        if (empty($provincesIds)) {
            throw new BadRequestHttpException('Bad request parameters', null, 400);
        }

        // Obtenemos del respositorio el total de población de todas las provincias, el total de población de las provincias pedidas así como el nombre de éstas
        $totalPopulationFromProvinces = $this->entityManager->getRepository(Province::class)->getTotalPopulationFromProvinces($provincesIds);
        $provincesNames = $this->entityManager->getRepository(Province::class)->getProvincesNames($provincesIds);
        $totalPopulation = $this->entityManager->getRepository(Province::class)->getTotalPopulation();

        //Se envía la información calculando el porcentaje con los datos obtenidos
        return $this->json([
            'provinces' => array_column($provincesNames, "name"),
            'totalPopulationFromProvinces' => array_column($totalPopulationFromProvinces, "total_population")[0],
            'totalPopulation' => array_column($totalPopulation, "total_population")[0],
            'percentage' => round(array_column($totalPopulationFromProvinces, "total_population")[0] / array_column($totalPopulation, "total_population")[0] * 100, 2)
        ]);
    }
}




