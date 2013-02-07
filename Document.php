<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\Document as JsonLdDocument;
use ML\IRI\IRI;
use Guzzle\Http\ClientInterface;

/**
 * A Document represents a JSON-LD document to be used with Hydra APIs
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class Document extends JsonLdDocument
{
    /**
     * @var ClientInterface HTTP client for web access
     */
    protected $client = null;

    /**
     * Constructor
     *
     * @param ClientInterface $client HTTP client for web access.
     * @param null|string|IRI $iri The document's IRI
     */
    public function __construct(ClientInterface $client = null, $iri = null)
    {
        $this->client = $client;
        $this->iri = new IRI($iri);
        $this->defaultGraph = new Graph($client, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function createGraph($name)
    {
        $name = (string) $this->iri->resolve($name);

        if (isset($this->namedGraphs[$name])) {
            return $this->namedGraphs[$name];
        }

        return $this->namedGraphs[$name] = new Graph($this->client, $this, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function removeGraph($graph = null)
    {
        if (null === $graph) {
            $this->defaultGraph = new Graph($this);

            return $this;
        }

        parent::removeGraph($graph);

        return $this;
    }
}
