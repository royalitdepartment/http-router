<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Sunrise\Http\Router\Annotation\OpenApi\AbstractReference;
use Sunrise\Http\Router\Annotation\OpenApi\Operation;
use Sunrise\Http\Router\Annotation\OpenApi\Parameter;
use Sunrise\Http\Router\Annotation\OpenApi\Schema;
use Sunrise\Http\Router\OpenApi\Info;
use Sunrise\Http\Router\OpenApi\SecurityRequirement;
use Sunrise\Http\Router\OpenApi\SecurityScheme;
use Sunrise\Http\Router\OpenApi\Server;
use ReflectionClass;

/**
 * Import functions
 */
use function array_walk_recursive;
use function strtolower;

/**
 * OpenApi
 */
class OpenApi
{

    /**
     * @var string
     */
    public const VERSION = '3.0.2';

    /**
     * @var array
     */
    private $documentation;

    /**
     * @var SimpleAnnotationReader
     */
    private $annotationReader;

    /**
     * @var RouteInterface[]
     */
    private $routes = [];

    /**
     * Constructor of the class
     *
     * @param Info $info
     */
    public function __construct(Info $info)
    {
        $this->documentation = [
            'openapi' => self::VERSION,
            'info' => $info->toArray(),
        ];

        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('Sunrise\Http\Router\Annotation');
    }

    /**
     * @param RouteInterface ...$routes
     *
     * @return void
     */
    public function addRoute(RouteInterface ...$routes) : void
    {
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
    }

    /**
     * @param Server $server
     *
     * @return void
     */
    public function pushServer(Server $server) : void
    {
        $this->documentation['servers'][] = $server->toArray();
    }

    /**
     * @param SecurityRequirement $securityRequirement
     *
     * @return void
     */
    public function pushSecurityRequirement(SecurityRequirement $securityRequirement) : void
    {
        $this->documentation['security'][] = $securityRequirement->toArray();
    }

    /**
     * @param string $name
     * @param SecurityScheme $securityScheme
     *
     * @return void
     */
    public function pushSecurityScheme(string $name, SecurityScheme $securityScheme) : void
    {
        $this->documentation['components']['securitySchemes'][$name] = $securityScheme->toArray();
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->documentation;
    }

    /**
     * @return string
     */
    public function toJson() : string
    {
        return json_encode($this->documentation);
    }

    /**
     * @return string
     */
    public function toYaml() : string
    {
        return yaml_emit($this->documentation);
    }

    /**
     * @return void
     */
    public function generateDocumentation() : void
    {
        foreach ($this->routes as $route) {
            $path = path_plain($route->getPath());
            $operation = $this->convertRouteToOperation($route);

            foreach ($route->getMethods() as $method) {
                $method = strtolower($method);

                $this->documentation['paths'][$path][$method]['operationId'] = $route->getName();
                $this->documentation['paths'][$path][$method] += $operation->toArray();
            }
        }

        $this->handleReferences();
    }

    /**
     * @param RouteInterface $route
     *
     * @return Operation
     */
    private function convertRouteToOperation(RouteInterface $route) : Operation
    {
        $target = new ReflectionClass($route->getRequestHandler());
        $operation = $this->annotationReader->getClassAnnotation($target, Operation::class) ?? new Operation();
        $attributes = path_parse($route->getPath());

        foreach ($attributes as $attribute) {
            $parameter = new Parameter();
            $parameter->in = 'path';
            $parameter->name = $attribute['name'];
            $parameter->required = !$attribute['isOptional'];

            if (isset($attribute['pattern'])) {
                $parameter->schema = new Schema();
                $parameter->schema->type = 'string';
                $parameter->schema->pattern = $attribute['pattern'];
            }

            $operation->parameters[] = $parameter;
        }

        return $operation;
    }

    /**
     * @return void
     */
    private function handleReferences() : void
    {
        array_walk_recursive($this->documentation, function (&$value) {
            if (!($value instanceof AbstractReference)) {
                return;
            }

            $ref = $value;
            $value = $ref->getComponentPath();

            $component =& $this->documentation['components'][$ref->getComponentName()];
            if (isset($component[$ref->name])) {
                return;
            }

            $annotation = $ref->getAnnotation($this->annotationReader);
            if (!isset($annotation)) {
                return;
            }

            $component[$ref->name] = $annotation->toArray();
        });
    }
}
