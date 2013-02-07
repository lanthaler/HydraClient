<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\NodeInterface;
use Guzzle\Parser\UriTemplate\UriTemplate;

/**
 * A Hydra IRI Template
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * @see http://purl.org/hydra/core#IriTemplate
 */
class IriTemplate
{
    /**
     * @var NodeInterface A reference to the node representing the IRI template.
     */
    protected $node = null;

    /**
     * Constructor
     *
     * @param NodeInterface $node The node representing the IRI template.
     */
    public function __construct(NodeInterface $node)
    {
        $this->node = $node;

        // TODO Validate node
    }

    /**
     * Get the IRI template
     *
     * @return string The IRI template.
     */
    public function getTemplate()
    {
        return $this->node->getProperty(Hydra::template);
    }

    /**
     * Get the variable mappings
     *
     * An IRI template contains zero or more variables that have to be replaced
     * with concrete values to construct a valid IRI. In Hydra variables can
     * be mapped to properties. This method returns those mappings as associative
     * array with the variable as the key and the property as value.
     *
     * @param boolean $onlyRequired If set to true, only required variables
     *                              will be returned.
     * @param boolean $justIri      If set to true, the property in returned
     *                              array will be included as string (IRI)
     *                              instead of a {@link NodeInterface Node}
     *                              object.
     *
     * @return array The IRI variable mappings.
     *
     * @see http://purl.org/hydra/core#IriTemplateMapping
     */
    public function getVariableMappings($onlyRequired = false, $justIri = false)
    {
        if (null === ($mappings = $this->node->getProperty(Hydra::mappings))) {
            return array();
        }

        if (false === is_array($mappings)) {
            $mappings = array($mappings);
        }

        $variables = array();
        foreach ($mappings as $mapping) {
            $variables[$mapping->getProperty(Hydra::variable)] = (false === $justIri)
                ? $mapping->getProperty(Hydra::property)
                : $mapping->getProperty(Hydra::property)->getId();
        }

        return $variables;
    }

    /**
     * Expand the IRI template with the specified values
     *
     * @return string The expanded IRI.
     */
    public function expand($values)
    {
        $mappings = $this->getVariableMappings(false, true);
        $variables = array();

        foreach ($mappings as $var => $property) {
            if (array_key_exists($property, $values)) {
                $variables[$var] = $values[$property];
            }
        }

        // TODO Throw error if required parameters are missing

        $uriTemplateExpander = new UriTemplate();
        return $uriTemplateExpander->expand($this->getTemplate(), $variables);
    }
}
