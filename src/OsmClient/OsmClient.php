<?php

namespace OsmClient;

/**
 * @author Yevhen Matasar
 * @package osm-client
 */
class OsmClient
{
    const DEFAULT_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko';
    const DEFAULT_SEARCH_ENDPOINT = 'https://nominatim.openstreetmap.org/search.php';
    const DEFAULT_POLYGONS_ENDPOINT = 'http://polygons.openstreetmap.fr/get_geojson.py';
    const SUPPORTED_BOUNDARIES_TYPES = ['Point', 'Polygon', 'MultiPolygon'];

    /**
     * @var string
     */
    protected $searchEndpoint;

    /**
     * @var string
     */
    protected $polygonsEndpoint;

    /**
     * @var string|null
     */
    protected $countryIso;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * @param string|null $countryIso
     */
    public function __construct(string $countryIso = null)
    {
        $this->searchEndpoint = static::DEFAULT_SEARCH_ENDPOINT;
        $this->polygonsEndpoint = static::DEFAULT_POLYGONS_ENDPOINT;
        $this->userAgent = static::DEFAULT_USERAGENT;

        if (null !== $countryIso) {
            $this->countryIso = $countryIso;
        }
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @param string $url
     */
    public function setSearchEndpoint(string $url)
    {
        $this->searchEndpoint = $url;
    }

    /**
     * @param string $url
     */
    public function setPolygonsEndpoint(string $url)
    {
        $this->polygonsEndpoint = $url;
    }

    /**
     * @param string $countryIso
     */
    public function setCountryIso(string $countryIso)
    {
        $this->countryIso = $countryIso;
    }

    public function resetCountryIso()
    {
        $this->countryIso = null;
    }

    /**
     * @param string $cityName
     * @param string|null $postalCode
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    public function findCityBoundaries(string $cityName, string $postalCode = null)
    {
        return $this->findBoundaries(trim($postalCode . ' ' . $cityName), [
            'city' => $cityName
        ]);
    }

    /**
     * @param int $relationId
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    public function getBoundaries(int $relationId)
    {
        $url = $this->polygonsEndpoint . '?' . http_build_query(['id' => $relationId]);

        $response = $this->sendRequest($url);

        if (empty($response['geometries'][0])) {
            return null;
        }

        return $this->extractGeoData($response['geometries'][0]);
    }

    /**
     * @param $query
     * @param array $params
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    public function find($query, $params = [])
    {
        return $this->query($query, array_merge($params, [
            'addressdetails' => 1,
            'format' => 'json',
            'limit' => 1,
        ]));
    }

    /**
     * @param $query
     * @param array $params
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    public function findOne($query, $params = [])
    {
        $response = $this->find($query, [
            'limit' => 1
        ]);

        $current = current($response);

        if (empty($current)) {
            return null;
        }

        return $current;
    }

    /**
     * @param string $query
     * @param array $params
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    public function findBoundaries(string $query, array $params = [])
    {
        $response = $this->query($query, array_merge($params, [
            'polygon_geojson' => 1,
            'addressdetails' => 0,
            'format' => 'json',
            'limit' => 1,
        ]));

        $first = current($response);

        if (empty($first['geojson'])) {
            return null;
        }

        return $this->extractGeoData($first['geojson']);
    }

    /**
     * @param $query
     * @param $params
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    protected function query($query, $params)
    {
        $params['q'] = $query;

        if (null !== $this->countryIso) {
            $params['countrycodes'] = $this->countryIso;
        }

        $url = $this->searchEndpoint . '?' . http_build_query($params);

        return $this->sendRequest($url);
    }

    /**
     * @param array $geometries
     *
     * @return array|null
     */
    protected function extractGeoData(array $geometries)
    {
        if (
            empty($geometries['coordinates'][0]) ||
            !in_array($geometries['type'], static::SUPPORTED_BOUNDARIES_TYPES, true)
        ) {
            return null;
        }

        if ($geometries['type'] === 'Point') {
            return [
                'type' => 'Point',
                'coordinates' => [
                    'lat' => $geometries['coordinates'][1],
                    'lon' => $geometries['coordinates'][0]
                ]
            ];
        }

        $polygons = $this->transformPolygon($geometries['coordinates'][0]);

        if ($geometries['type'] === 'MultiPolygon') {
            $polygons = [];

            foreach ($geometries['coordinates'] as $polygon) {
                $polygons[] = $this->transformPolygon(array_shift($polygon));
            }
        }

        return [
            'type' => $geometries['type'],
            'coordinates' => $polygons
        ];
    }

    /**
     * @param array $polygon
     *
     * @return array
     */
    protected function transformPolygon(array $polygon)
    {
        foreach ($polygon as $key => $point) {
            $polygon[$key] = [
                'lat' => $point[1],
                'lon' => $point[0]
            ];
        }

        return $polygon;
    }

    /**
     * @param string $url
     *
     * @return array|null
     *
     * @throws OsmClientException
     */
    protected function sendRequest(string $url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $this->userAgent
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (false === $response || 200 !== $httpCode) {
            throw new OsmClientException(
                429 === $httpCode ?
                    'Bandwidth limit exceeded!' :
                    'Unexpected server response!',
                $httpCode
            );
        }

        return json_decode($response, true);
    }
}
