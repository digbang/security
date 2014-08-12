<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DigbangSecurityMigrationsCreateUsersGroupsPivotTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];

	function __construct()
	{
		$this->shouldUpdate = Schema::hasTable('users_groups');
	}


	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if ($this->shouldUpdate)
		{
			$this->update();
		}
		else
		{
			$this->create();
		}
	}

	protected function create()
	{
		Schema::create('users_groups', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned();
			$table->integer('group_id')->unsigned();

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->primary(array('user_id', 'group_id'));
		});
	}

	protected function update()
	{
		Schema::table('users_groups', function(Blueprint $table)
		{
			if (! Schema::hasColumn('users_groups', 'user_id'))
			{
				$this->createdColumns[] = 'user_id';
				$table->integer('user_id')->unsigned();
			}

			if (! Schema::hasColumn('users_groups', 'group_id'))
			{
				$this->createdColumns[] = 'group_id';
				$table->integer('group_id')->unsigned();
			}

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails('groups');

			if (! $doctrineTable->hasPrimaryKey())
			{
				$table->primary(array('user_id', 'group_id'));
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if ($this->shouldUpdate)
		{
			$this->removeCautiously();
		}
		else
		{
			$this->drop();
		}
	}

	protected function drop()
	{
		Schema::drop('users_groups');
	}

	protected function removeCautiously()
	{
		$columns = $this->createdColumns;

		if (!empty($columns))
		{
			Schema::table('users_groups', function(Blueprint $table) use ($columns) {
				$table->dropColumn($columns);
			});
		}
	}
}
