<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\JsonLD;
use ML\JsonLD\GraphInterface;
use Guzzle\Common\Collection;
use Guzzle\Service\Client as ServiceClient;

/**
 * Hydra Client
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
class Client extends ServiceClient
{
    /**
     * @var GraphInterface The graph containing the API documentation.
     */
    protected $apiDocumentation;

    /**
     * Factory method to create a new Hydra Client
     *
     * The following array keys and values are available options:
     *  - entrypoint:    The Web API's entry point, an absolute URL
     *  - documentation: A direct link to the Web API's documentation; if
     *                   not passed, the client will try to discover the
     *                   documentation using the passed entry point
     *  - context:       A context used to abbreviate long IRIs
     *
     * @param array|Collection $config Configuration data
     *
     * @return self
     */
    public static function factory($config = array())
    {
        $default = array(
            'documentation' => null,
            'context' => null
        );
        $required = array('entrypoint');
        $config = Collection::fromConfig($config, $default, $required);

        $client = new static($config->get('entrypoint'), $config);

        $doc = $config->get('documentation') ?: $config->get('entrypoint');

        $client->apiDocumentation = JsonLD::getDocument(
            $doc,
            array('base' => $config->get('entrypoint'), 'documentFactory' => new DocumentFactory($client))
        )->getGraph();

        return $client;
    }

    /**
     * {@inheritdoc}
     *
     * @param string           $baseUrl Base URL of the web service
     * @param array|Collection $config  Configuration settings
     */
    public function __construct($baseUrl = '', $config = null)
    {
        parent::__construct($baseUrl, $config);

        $this->context = $this->getConfig('context');
        $this->getConfig()->remove('context');
        $this->setUserAgent(sprintf(
            'HydraClient/1.0 (PHP %s; curl %s)',
            PHP_VERSION,
            \Guzzle\Http\Curl\CurlVersion::getInstance()->get('version')
        ));
    }

    /**
     * Get the API documentation
     *
     * @return GraphInterface The API documentation.
     */
    public function getApiDocumentation()
    {
        return $this->apiDocumentation;
    }

    /**
     * Get an operation by IRI
     *
     * @param string $iri The IRI of the operation to retrieve.
     *
     * @return null|Operation The operation or null if not found.
     */
    public function getOperation($iri)
    {
        if (null === ($operation = $this->apiDocumentation->getNode($iri))) {
            return null;
        }

        return new Operation($operation);
    }

    /**
     * Get all operations documented in the API documentation
     *
     * @return array[Operation] The documented operations.
     */
    public function getOperations()
    {
        $operations = $this->apiDocumentation->getNodesByType(Hydra::Operation);

        return array_map(function ($op) { return new Operation($op); }, $operations);
    }

    /**
     * Get a command by IRI
     *
     * @param string $iri  The IRI of the operation defining the command to retrieve.
     * @param array  $args Arguments to pass to the command.
     *
     * @return CommandInterface
     * @throws InvalidArgumentException If the operation can not be found.
     */
    public function getCommand($iri, array $args = array())
    {
        // if (!($command = $this->getCommandFactory()->factory($iri, $args))) {
        //     throw new InvalidArgumentException("Command was not found matching {$iri}");
        // }

        if (null === ($operation = $this->getOperation($iri))) {
            throw new \Exception('Operation with the passed IRI not found.');
        }

        /***************************************************************
        Construct IRI
            Target IRI known        -> target IRI must be known
                   or
            Expand IRI template     -> requires template and variable values
        ****************************************************************/
        $nodeId = null;
        if (isset($args['@id'])) {
            $target = $nodeId = $args['@id'];
            unset($args['@id']);
        } else {
            if (null === ($template = $operation->getIriTemplate())) {
                throw new \Exception('Passed no node and no IRI template is defined for this operation');
            }

            $target = $template->expand($args);
        }

        if (null !== ($type = $operation->getExpectedType())) {
            $graph = new Graph();
            $node = $graph->createNode($nodeId);
            $node->setType($graph->createNode($type->getId()));

            // TODO Add additional types if passed!?

            // if (isset($args['@type'])) {
            //     $types = (is_array(($args['@type'])) ? $args['@type'] : array($args['@type']));

            //     foreach ($types as $type) {
            //         if ($type instanceof NodeInterface) {
            //             $type = $type->getId();
            //         } elseif (false === is_string($type)) {
            //             var_dump($type);
            //             throw new \Exception('Invalid @type passed');
            //         }

            //         $node->addType($graph->createNode($type));
            //     }

            //     unset($args['@type']);
            // }

            // TODO Switch to Hydra::properties as soon as it is available
            $properties = $type->getReverseProperty('http://www.w3.org/1999/02/22-rdf-syntax-ns#domain');

            foreach ($properties as $property) {
                $property = $property->getId();
                if (isset($args[$property])) {
                    $node->setProperty($property, $args[$property]);
                }
            }
        } else {
            $graph = null;
        }
        $command = new Command($operation, $graph, $target, $args);


        // ---------- From here on the same as Service/Client ---------------

        $command->setClient($this);

        // Add global client options to the command
        if ($command instanceof Collection) {
            if ($options = $this->getConfig(self::COMMAND_PARAMS)) {
                foreach ($options as $key => $value) {
                    if (!$command->hasKey($key)) {
                        $command->set($key, $value);
                    }
                }
            }
        }

        $this->dispatch('client.command.create', array(
            'client'  => $this,
            'command' => $command
        ));

        return $command;
    }
}
