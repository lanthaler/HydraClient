<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

use ML\JsonLD\JsonLD;
use Guzzle\Service\Command\AbstractCommand;
use Guzzle\Service\Exception\CommandException;
use Guzzle\Common\Collection;

// TODO Abstract command depends on OperationInterface, should be changed in Guzzle
class Command extends AbstractCommand
{
    protected $graph;

    protected $target;

    /**
     * Constructor
     *
     * @param array|Collection   $parameters Collection of parameters to set on the command
     *
     * TODO $operation parameter uses a different type than abstract command
     *
     * @param Operation $operation Command definition from API documentation
     */
    public function __construct(Operation $operation, $graph = null, $target = null, $parameters = null)
    {
        parent::__construct($parameters);

        $this->operation = $operation;
        $this->graph = $graph;
        $this->target = $target;

        $headers = $this->get(self::HEADERS_OPTION);
        if (!$headers instanceof Collection) {
            $this->set(self::HEADERS_OPTION, new Collection((array) $headers));
        }

        // You can set a command.on_complete option in your parameters to set an onComplete callback
        if ($onComplete = $this->get('command.on_complete')) {
            $this->remove('command.on_complete');
            $this->setOnComplete($onComplete);
        }

        $this->init();
    }

    /**
     * {@inheritdoc}
     */
    protected function build()
    {
        /***************************************************************
        Serialize body              -> requires data
            Query for expects type
            Serialize result
                   or
            Just serialize graph and let server do the work?
                Perhaps check at least if graph contains expects!?
        ****************************************************************/

        $headers = null;
        if (null !== ($expected = $this->operation->getExpectedType(true))) {
            if (null === $this->graph) {
                throw new \Exception("The operation expects $expected but no graph has been passed.");
            }

            $nodes = $this->graph->getNodesByType($expected);

            if (0 === count($nodes)) {
                throw new \Exception("The operation expects $expected but no such nodes have been found in the passed graph.");
            }
        }

        if (null === $this->graph) {
            $body = null;
        } else {
            $body = JsonLD::toString(JsonLD::compact($this->graph->toJsonLd()), true);    // TODO Do pretty print by default?
            $headers = array('Content-Type' => 'application/ld+json');
        }


        $this->request = $this->client->createRequest($this->operation->getHttpMethod(), $this->target, $headers, $body);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate()
    {
        // This method overwrites AbstractCommand's validate method to disable validation completely.
    }

    /**
     * Initialize the command (hook that can be implemented in subclasses)
     */
    protected function init() {
        // TODO Called from constructor, remove if not needed
    }

    /**
     * Processes the response
     */
    protected function process()
    {
        /***************************************************************
        Parse response
            graph = JsonLD::getDocument()
            primaryNode = graph->getNodeByType(returns)
        ****************************************************************/

        // Do not process the response if 'command.response_processing' is set to 'raw'
        if ($this->get(self::RESPONSE_PROCESSING) === self::TYPE_RAW) {
            $this->result = $this->request->getResponse();
        } else {
            // TODO What about Content-Location? Make this smarter! There are several cases to consider
            $response = $this->request->getResponse();
            $base = $response->getLocation();
            if (null === $base) {
                $base = $this->request->getUrl();
            }

            $this->result = JsonLD::getDocument($response->getBody(true), array('base' => $base));
        }

        return $this->result;
    }
}
