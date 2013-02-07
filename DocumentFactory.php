<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\DocumentFactoryInterface;
use Guzzle\Http\ClientInterface;

/**
 * DocumentFactory creates new Hydra Documents
 *
 * @see Document
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @var ClientInterface HTTP client for web access
     */
    protected $client = null;

    /**
     * Constructor
     *
     * @param ClientInterface $client HTTP client for web access.
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function createDocument($iri = null)
    {
        return new Document($this->client, $iri);
    }
}
