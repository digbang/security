<?php namespace Digbang\Security\Roles;

use Digbang\Doctrine\TimestampsTrait;
use Digbang\Security\Contracts\Role as RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Role implements RoleInterface
{
	use TimestampsTrait;
	use RoleTrait;

	/**
	 * @return \Carbon\Carbon
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

}
