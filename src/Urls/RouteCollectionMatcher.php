<?php

namespace Digbang\Security\Urls;

use Illuminate\Http\Request;
use Illuminate\Routing\AbstractRouteCollection;
use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;

class RouteCollectionMatcher extends RouteCollection
{
    /**
     * @var AbstractRouteCollection
     */
    private $collection;

    public function __construct(AbstractRouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param  Request  $request
     * @return Route|null
     */
    public function getRouteForRequest(Request $request)
    {
        $routes = $this->collection->get($request->getMethod());

        return $this->check($routes, $request);
    }

    /**
     * Determine if a route in the array matches the request.
     *
     * @param  array  $routes
     * @param  Request  $request
     * @param  bool  $includingMethod
     * @return Route|null
     */
    protected function check(array $routes, $request, $includingMethod = true)
    {
        $methodName = $this->getCheckMethodName();

        return $this->collection->$methodName($routes, $request, $includingMethod);
    }

    /**
     * @return string
     */
    private function getCheckMethodName()
    {
        if (method_exists($this->collection, 'matchAgainstRoutes')) {
            // Laravel >= 5.4
            return 'matchAgainstRoutes';
        }

        //Laravel <= 5.3
        return 'check';
    }
}
