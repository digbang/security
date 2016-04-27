<?php
declare(strict_types = 1);

namespace Digbang\Security\Urls;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;

class RouteCollectionMatcher extends RouteCollection
{
    /**
     * @var RouteCollection
     */
    private $collection;

    /**
     * @param RouteCollection $collection
     */
    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Routing\Route|null
     */
    public function getRouteForRequest(Request $request)
    {
        $routes = $this->collection->get($request->getMethod());

        return $this->collection->check($routes, $request);
    }
}
