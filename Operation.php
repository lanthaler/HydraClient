<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\NodeInterface;

/**
 * A Hydra Operation
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * @see http://purl.org/hydra/core#Operation
 */
class Operation
{
    /**
     * @var NodeInterface A reference to the node representing the operation.
     */
    protected $node = null;

    /**
     * @var string The target IRI the operation should be invoked on.
     */
    protected $targetIri = null;

    /**
     * Constructor
     *
     * @param NodeInterface $node      The node representing the operation.
     * @param null|string   $targetIri If known, the IRI the operation should
     *                                 be invoked on; otherwise, the target IRI
     *                                 will be constructed using the operation's
     *                                 IRI template.
     */
    public function __construct(NodeInterface $node, $targetIri = null)
    {
        $this->node = $node;
        $this->targetIri = $targetIri;

        // TODO Validate node
    }

    /**
     * Get the name
     *
     * @return string The operation's name.
     */
    public function getName()
    {
        return $this->node->getProperty(Hydra::title);
    }

    /**
     * Get the description
     *
     * @return null|string The operation's description.
     */
    public function getDescription()
    {
        return $this->node->getProperty(Hydra::description);
    }

    /**
     * Get the HTTP method of the operation
     *
     * @return string The HTTP method to be used for this operation.
     */
    public function getHttpMethod()
    {
        return $this->node->getProperty(Hydra::method);
    }

    /**
     * Get the type this operation expects
     *
     * The request invoking this operation has to contain a node of the type
     * returned by this method.
     *
     * @param boolean $justIri If set to true, instead of returning a node,
     *                         this method will just return the node's IRI.
     *
     * @return null|string|NodeInterface The expected type.
     */
    public function getExpectedType($justIri = false)
    {
        $expects = $this->node->getProperty(Hydra::expects);

        if ((true === $justIri) && (null !== $expects)) {
            return $expects->getId();
        }

        return $expects;
    }

    /**
     * Get the type of the primary node this operation returns
     *
     * The response of this operation will contain a node of the type
     * returned by this method. This represents the primary node of the
     * returned graph.
     *
     * @param boolean $justIri If set to true, instead of returning a node,
     *                         this method will just return the node's IRI.
     *
     * @return null|string|NodeInterface The type of the primary node returned.
     */
    public function getReturnType($justIri = false)
    {
        $returns = $this->node->getProperty(Hydra::returns);

        if ((true === $justIri) && (null !== $returns)) {
            return $returns->getId();
        }

        return $returns;
    }

    /**
     * Get documented status codes
     *
     * Hydra allows to associate additional documentation to the status codes
     * an operation might return. This method returns these.
     *
     * @return array The documented status codes
     */
    public function getDocumentedStatusCodes()
    {
        $statusCodes = $this->node->getProperty(Hydra::statusCodes);

        if (null === $statusCodes) {
            return array();
        }
        // Hydra::statusCode    = 'http://purl.org/hydra/core#statusCode';

        return $statusCodes;
    }

    /**
     * Get the IRI template
     *
     * An operation may specify an IRI template that can be used to construct
     * the target IRI.
     *
     * @return null|IriTemplate The IRI template if set; otherwise null.
     */
    public function getIriTemplate()
    {
        $template = $this->node->getProperty(Hydra::template);

        if (null === $template) {
            return null;
        }

        return new IriTemplate($template);
    }
}
