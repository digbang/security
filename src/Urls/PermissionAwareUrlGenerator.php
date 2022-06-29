<?php

namespace Digbang\Security\Urls;

use Digbang\Security\Contracts\SecurityApi;
use Digbang\Security\Exceptions\Unauthorized;
use Digbang\Security\Permissions\Permissible;
use Illuminate\Contracts\Routing\UrlGenerator;

class PermissionAwareUrlGenerator implements UrlGenerator
{
    /**
     * @var UrlGenerator
     */
    private $url;

    /**
     * @var SecurityApi
     */
    private $securityApi;

    /**
     * @param  UrlGenerator  $url
     * @param  SecurityApi  $securityApi
     */
    public function __construct(UrlGenerator $url, SecurityApi $securityApi)
    {
        $this->url = $url;
        $this->securityApi = $securityApi;
    }

    public function url(): UrlGenerator
    {
        return $this->url;
    }

    public function __call($name, $args)
    {
        if (is_callable([$this->url, $name])) {
            return call_user_func_array([$this->url, $name], $args);
        }
    }

    /**
     * @inheritdoc
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $permission = $this->securityApi->permissions()->getForRoute($name);

        $this->checkPermission($permission);

        return $this->url->route($name, $parameters, $absolute);
    }

    /**
     * @inheritdoc
     */
    public function action($action, $parameters = [], $absolute = true)
    {
        $permission = $this->securityApi->permissions()->getForAction($action);

        $this->checkPermission($permission);

        return $this->url->action($action, $parameters, $absolute);
    }

    /**
     * @inheritdoc
     */
    public function getRootControllerNamespace()
    {
        return $this->url->getRootControllerNamespace();
    }

    /**
     * @inheritdoc
     */
    public function to($path, $extra = [], $secure = null)
    {
        $url = $this->url->to($path, $extra, $secure);

        $permission = $this->securityApi->permissions()->getForPath($url);

        $this->checkPermission($permission);

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function secure($path, $parameters = [])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * @inheritdoc
     */
    public function asset($path, $secure = null)
    {
        // Assets are not subject to permissions.
        return $this->url->asset($path, $secure);
    }

    /**
     * @inheritdoc
     */
    public function setRootControllerNamespace($rootNamespace)
    {
        $this->url->setRootControllerNamespace($rootNamespace);

        return $this;
    }

    /**
     * Allow access to the UrlGenerator object without checking permissions.
     *
     * @return UrlGenerator
     */
    public function insecure()
    {
        return $this->url;
    }

    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        return $this->url->current();
    }

    /**
     * Get the URL for the previous request.
     *
     * @param  mixed  $fallback
     * @return string
     */
    public function previous($fallback = false)
    {
        return $this->url->previous($fallback);
    }

    /**
     * Check if the logged user has access to the given permission(s).
     * Users must implement the Digbang\Security\Permissions\Permissible interface.
     *
     * @param  string|array  $permission
     *
     * @throws Unauthorized
     */
    private function checkPermission($permission)
    {
        if (! $permission) {
            return;
        }

        $user = $this->securityApi->getUser(true);
        if ($user instanceof Permissible && $user->hasAccess($permission)) {
            return;
        }

        throw Unauthorized::permissionDenied($permission, $this->securityApi);
    }
}
