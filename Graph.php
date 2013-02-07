<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\Graph as JsonLdGraph;
use ML\JsonLD\DocumentInterface;
use ML\IRI\IRI;
use Guzzle\Http\ClientInterface;

/**
 * A Graph represents a JSON-LD graph to be used with Hydra APIs
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class Graph extends JsonLdGraph
{
    /**
     * @var ClientInterface HTTP client for web access
     */
    protected $client = null;

    /**
     * Constructor
     *
     * @param ClientInterface $client HTTP client for web access.
     * @param DocumentInterface $document The document the graph belongs to.
     */
    public function __construct(ClientInterface $client = null, DocumentInterface $document = null)
    {
        $this->client = $client;

        parent::__construct($document);
    }

    /**
     * {@inheritdoc}
     */
    public function createNode($id = null)
    {
        if (!is_string($id) || ('_:' === substr($id, 0, 2))) {
            $id = $this->createBlankNodeId();
        } else {
            $id = (string) $this->resolveIri($id);
            if (isset($this->nodes[$id])) {
                return $this->nodes[$id];
            }
        }

        return $this->nodes[$id] = new Node($this->client, $this, $id);
    }

    /**
     * Retrieve a node by it's type
     *
     * @param string|IRI The type's IRI
     *
     * @return Node The node
     *
     * @throws NotFound        If no node with the passed type was found.
     * @throws MultipleMatches If more than one node with the passed type was found.
     */
    public function getNodeByType($iri)
    {
        $nodes = $this->getNodesByType($iri);

        if (count($nodes) === 1) {
            return $nodes[0];
        }

        // TODO Throw exception
    }
}
