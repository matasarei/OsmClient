<?php

namespace OsmClient;

/**
 * @author Yevhen Matasar
 * @package osm-client
 */
class OsmClientException extends \Exception
{
    /**
     * @var int
     */
    protected $httpCode = 500;

    /**
     * @param string $message
     * @param int|null $httpCode
     */
    public function __construct($message, $httpCode = null)
    {
        parent::__construct($message);

        if (null !== $httpCode) {
            $this->httpCode = $httpCode;
        }
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
