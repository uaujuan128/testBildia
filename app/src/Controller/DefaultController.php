<?php

namespace App\Controller;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class DefaultController extends AbstractController
{
    private EntityManagerInterface $_em;

    public function __construct(private ManagerRegistry $registry)
    {
        $this->_em = $this->registry->getManager();
    }

    /**
     * @throws Exception
     */
    #[Route('/municipality/{cardinal}', name: 'nearestMunicipalityToCardinal', requirements: ['cardinal' => 'n|s|e|w'], methods: ['GET', 'HEAD'])]
    public function getNearestMunicipalityToCardinal(Request $request): JsonResponse
    {
        /* Example: http://localhost:8080/api/municipality/n?municipalities=[1,2] */

        $municipalityIds = json_decode($request->get('municipalities'));
        $cardinal = $request->get('cardinal');

        $sql = "SELECT * FROM municipality WHERE id = ";

        $nearestMunicipality = [];
        $cardinalName = '';

        switch ($cardinal) {
            case 'n': {
                $cardinalName = 'north';

                foreach ($municipalityIds as $municipalityId) {
                    $municipality = $this->getDatabaseResult($sql . $municipalityId);

                    if (empty($nearestMunicipality)) {
                        $nearestMunicipality = $municipality[0];
                    } else {
                        if ($nearestMunicipality['latitude'] < $municipality[0]['latitude']) {
                            $nearestMunicipality = $municipality[0];
                        }
                    }
                }

                break;
            }
            case 's': {
                $cardinalName = 'south';

                foreach ($municipalityIds as $municipalityId) {
                    $municipality = $this->getDatabaseResult($sql . $municipalityId);

                    if (empty($nearestMunicipality)) {
                        $nearestMunicipality = $municipality[0];
                    } else {
                        if ($nearestMunicipality['latitude'] > $municipality[0]['latitude']) {
                            $nearestMunicipality = $municipality[0];
                        }
                    }
                }

                break;
            }
            case 'e': {
                $cardinalName = 'east';

                foreach ($municipalityIds as $municipalityId) {
                    $municipality = $this->getDatabaseResult($sql . $municipalityId);

                    if (empty($nearestMunicipality)) {
                        $nearestMunicipality = $municipality[0];
                    } else {
                        if ($nearestMunicipality['longitude'] > $municipality[0]['longitude']) {
                            $nearestMunicipality = $municipality[0];
                        }
                    }
                }

                break;
            }
            case 'w': {
                $cardinalName = 'west';

                foreach ($municipalityIds as $municipalityId) {
                    $municipality = $this->getDatabaseResult($sql . $municipalityId);

                    if (empty($nearestMunicipality)) {
                        $nearestMunicipality = $municipality[0];
                    } else {
                        if ($nearestMunicipality['longitude'] < $municipality[0]['longitude']) {
                            $nearestMunicipality = $municipality[0];
                        }
                    }
                }

                break;
            }
        }

        return $this->json([
            'cardinalPoint' => $cardinalName,
            'municipality' => $nearestMunicipality,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route(
        '/community/{firstCommunity}/{secondCommunity}',
        name: 'largestPopulationCommunity',
        requirements: ['firstCommunity' => '\d+', 'secondCommunity' => '\d+'],
        methods: ['GET', 'HEAD']
    )]
    public function getLargestPopulationCommunity(int $firstCommunity, int $secondCommunity): JsonResponse
    {
        $sqlProvinces = 'SELECT p.name, p.population FROM province p INNER JOIN community c ON (p.community_id = c.id) WHERE c.id = ';
        $sqlCommunity = 'SELECT * FROM community WHERE id = ';

        $firstProvincesPopulation = $this->getDatabaseResult($sqlProvinces . $firstCommunity);
        $secondProvincesPopulation = $this->getDatabaseResult($sqlProvinces . $secondCommunity);

        $firstCommunityPopulation = 0;
        $secondCommunityPopulation = 0;
        foreach ($firstProvincesPopulation as $firstProvincePopulation) {
            $firstCommunityPopulation += (int)$firstProvincePopulation['population'];
        }
        foreach ($secondProvincesPopulation as $secondProvincePopulation) {
            $secondCommunityPopulation += (int)$secondProvincePopulation['population'];
        }

        $sqlCommunity .= ($firstCommunityPopulation > $secondCommunityPopulation) ? $firstCommunity : $secondCommunity;

        return $this->json([
            'community' => $this->getDatabaseResult($sqlCommunity)
        ]);
    }

    /**
     * @throws Exception
     */
    private function getDatabaseResult($sql): array
    {
        return $this->_em->getConnection()->prepare($sql)->executeQuery()->fetchAllAssociative();
    }
}
