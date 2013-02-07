<?php

/*
 * (c) Markus Lanthaler <mail@markus-lanthaler.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ML\HydraClient;

/**
 * Hydra Core Vocabulary
 *
 * @author Markus Lanthaler <mail@markus-lanthaler.com>
 */
final class Hydra
{
    // Classes
    const Resource                 = 'http://purl.org/hydra/core#Resource';
    const Collection               = 'http://purl.org/hydra/core#Collection';
    const PagedCollection          = 'http://purl.org/hydra/core#PagedCollection';
    const Operation                = 'http://purl.org/hydra/core#Operation';
    const CreateResourceOperation  = 'http://purl.org/hydra/core#CreateResourceOperation';
    const DeleteResourceOperation  = 'http://purl.org/hydra/core#DeleteResourceOperation';
    const ReplaceResourceOperation = 'http://purl.org/hydra/core#ReplaceResourceOperation';
    const HttpMethod               = 'http://purl.org/hydra/core#HttpMethod';
    const IriTemplate              = 'http://purl.org/hydra/core#IriTemplate';
    const IriTemplateMapping       = 'http://purl.org/hydra/core#IriTemplateMapping';
    const Error                    = 'http://purl.org/hydra/core#Error';
    const StatusCodeDescription    = 'http://purl.org/hydra/core#StatusCodeDescription';

    // Properties
    const apiDocumentation = 'http://purl.org/hydra/core#apiDocumentation';
    const title         = 'http://purl.org/hydra/core#title';
    const description   = 'http://purl.org/hydra/core#description';
    const itemsPerPage  = 'http://purl.org/hydra/core#itemsPerPage';
    const firstPage     = 'http://purl.org/hydra/core#firstPage';
    const nextPage      = 'http://purl.org/hydra/core#nextPage';
    const previousPage  = 'http://purl.org/hydra/core#previousPage';
    const lastPage      = 'http://purl.org/hydra/core#lastPage';
    const totalItems    = 'http://purl.org/hydra/core#totalItems';
    const operations    = 'http://purl.org/hydra/core#operations';
    const method        = 'http://purl.org/hydra/core#method';
    const expects       = 'http://purl.org/hydra/core#expects';
    const returns       = 'http://purl.org/hydra/core#returns';
    const statusCodes   = 'http://purl.org/hydra/core#statusCodes';
    const statusCode    = 'http://purl.org/hydra/core#statusCode';
    const template      = 'http://purl.org/hydra/core#template';
    const mappings      = 'http://purl.org/hydra/core#mappings';
    const variable      = 'http://purl.org/hydra/core#variable';
    const property      = 'http://purl.org/hydra/core#property';
    const freetextQuery = 'http://purl.org/hydra/core#freetextQuery';
    const search        = 'http://purl.org/hydra/core#search';

    /**
     * Hiding the constructor as PHP doesn't allow final abstract classes
     */
    private function __construct() {}
}
