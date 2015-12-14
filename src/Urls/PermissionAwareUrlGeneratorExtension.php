<?php namespace Digbang\Security\Urls;

use Illuminate\Routing\UrlGenerator;

class PermissionAwareUrlGeneratorExtension extends UrlGenerator
{
	/**
	 * @var PermissionAwareUrlGenerator
	 */
	private $urlGenerator;

	/**
	 * @param PermissionAwareUrlGenerator $urlGenerator
	 */
	public function setUrlGenerator(PermissionAwareUrlGenerator $urlGenerator)
	{
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function route($name, $parameters = [], $absolute = true)
	{
		return $this->urlGenerator->route($name, $parameters, $absolute);
	}

	/**
	 * {@inheritdoc}
	 */
	public function action($action, $parameters = [], $absolute = true)
	{
		return $this->urlGenerator->action($action, $parameters, $absolute);
	}

	/**
	 * {@inheritdoc}
	 */
	public function to($path, $extra = [], $secure = null)
	{
		return $this->urlGenerator->to($path, $extra, $secure);
	}
}
