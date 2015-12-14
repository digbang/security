<?php
namespace Digbang\Security\Support;

trait TimestampsTrait
{
	/**
	 * @var \Carbon\Carbon
	 */
	private $createdAt;

	/**
	 * @var \Carbon\Carbon
	 */
	private $updatedAt;

	public function onPrePersist()
	{
		$now = new \Carbon\Carbon();

		$this->createdAt = $now;
		$this->updatedAt = $now;
	}

	public function onPreUpdate()
	{
		$now = new \Carbon\Carbon();

		$this->updatedAt = $now;
	}
}
