<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\NodeInterface;
use ML\JsonLD\GraphInterface;
use ML\JsonLD\Node as JsonLdNode;
use ML\IRI\IRI;
use Guzzle\Http\ClientInterface;

/**
 * A Node represents a node in a JSON-LD graph returned by a Hydra API
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class Node extends JsonLdNode
{
    /**
     * @var ClientInterface HTTP client for web access.
     */
    protected $client = null;

    /**
     * @var boolean Has the node been loaded using its IRI?
     */
    private $loadedFromId = false;

    /**
     * Constructor
     *
     * @param ClientInterface $client HTTP client for web access.
     * @param GraphInterface  $graph  The graph the node belongs to.
     * @param null|string     $id     The ID of the node.
     */
    public function __construct(ClientInterface $client = null, GraphInterface $graph, $id = null)
    {
        $this->client = $client;

        parent::__construct($graph, $id);

        try {
            $nodeIri = new IRI($id);
            $nodeIri = $nodeIri->getAbsoluteIri();
            if ($graph->getDocument() && ($graph->getDocument()->getIri(true)->getAbsoluteIri()->equals($nodeIri))) {
                $this->loadedFromId = true;
            }
        } catch (\Exception $e) {
            // do nothing, either the node or the document has just a relative IRI
        }
    }

    /**
     * Retrieve the operations associated with this node
     *
     * This method returns the operations directly associated with the node,
     * the ones associated with the node's types, and the ones associated
     * with properties pointing to this node.
     *
     * @param boolean $loadNodes If set to true, nodes that haven't been fully
     *                           may be dereferenced; otherwise, just data that
     *                           has already been loaded is used.
     *
     * @return array[Node] An array of nodes representing documented operations
     *                     (might be empty)
     */
    public function getOperations($loadNodes = false)
    {
        $operations = array();
        $apiDoc = (null === $this->client) ?: $this->client->getApiDocumentation();

        // Operations directly associated with the node
        if (null !== ($ops = $this->getProperty(Hydra::operations, $loadNodes))) {
            foreach ($ops as $op) {
                $operations[] = $op;
            }
        }

        // Operations associated with the node's type
        if (null !== ($types = $this->getType($loadNodes))) {
            if (false === is_array($types)) {
                $types = array($types);
            }
            foreach ($types as $type) {
                if (null !== ($ops = $type->getProperty(Hydra::operations, $loadNodes))) {
                    foreach ($ops as $op) {
                        $operations[] = $op;
                    }
                }

                if ((null !== $apiDoc) && (null !== ($type = $apiDoc->getNode($type->getId()))) &&
                    (null !== ($ops = $type->getProperty(Hydra::operations, $loadNodes)))) {
                    foreach ($ops as $op) {
                        $operations[] = $op;
                    }
                }
            }
        }

        // Operations associated with properties pointing to the node
        $getNode = function($n) { return $this->getGraph()->getNode($n); };
        $revProps = array_map($getNode, array_keys($this->getReverseProperties()));

        foreach ($revProps as $prop) {
            if (null !== ($ops = $prop->getProperty(Hydra::operations, $loadNodes))) {
                foreach ($ops as $op) {
                    $operations[] = $op;
                }
            }

            if ((null !== $apiDoc) && (null !== ($type = $apiDoc->getNode($prop->getId()))) &&
                (null !== ($ops = $type->getProperty(Hydra::operations, $loadNodes)))) {
                foreach ($ops as $op) {
                    $operations[] = $op;
                }
            }
        }

        return $operations;
    }

    public function getOperationsByType($type)
    {
        $operations = $this->getOperations();

        $operations = array_filter($operations, function ($o) use ($type) {
            $types = $o->getType();
            if (false === is_array($types)) {
                $types = array($types);
            }

            foreach ($types as $t) {
                if ($type === $t->getId()) {
                    return true;
                }
            }

            return false;
        });

        return $operations;
    }

    public function getCommand($name, array $args = array())
    {
        $properties = $this->getProperties();
        $properties['@id'] = $this->getId();

        $args = array_merge($properties, $args);

        // TODO Handle the case when the operation is not in the service desc

        return $this->client->getCommand($name, $args);
    }

    /**
     * {@inheritdoc}
     *
     * @param boolean $load If set to true and the specified property is not set,
     *                      the node will be loaded using its IRI to return the
     *                      property even if it has not been loaded yet.
     */
    public function getProperty($property, $load = true)
    {
        if (null !== ($result = parent::getProperty($property))) {
            return $result;
        } elseif (false === $load) {
            return null;
        }

        $this->load();

        return parent::getProperty($property);
    }

    /**
     * {@inheritdoc}
     *
     * @param boolean $load If set to true and the specified property is not set,
     *                      the node will be loaded using its IRI to return the
     *                      property even if it has not been loaded yet.
     */
    public function getType($load = true)
    {
        return $this->getProperty(self::TYPE, $load);
    }

    public function getAction($operation, GraphInterface $data = null, array $parameters = array())
    {
        return $this->client->getAction($operation, $data, $parameters);
    }

    /**
     * Loads the node and all its properties using its IRI
     *
     * @throws NodeNotFoundException If the node was not found.
     */
    private function load()
    {
        if ((false === $this->loadedFromId) && (null !== $this->client)) {
            $this->loadedFromId = true;

            var_dump('########### loading ' . $this->getId());

            xdebug_print_function_stack();

            // if ($this->client->load($this->getId()) === null) {
            //     throw new \Doctrine\ORM\EntityNotFoundException();
            // }
        }
    }
}
