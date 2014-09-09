<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DigbangSecurityMigrationsCreateUsersGroupsPivotTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];
	protected $tableName = 'users_groups';

	function __construct()
	{
		$this->tableName = \Config::get('security::auth.user_groups_pivot_table', $this->tableName);
		$this->shouldUpdate = Schema::hasTable($this->tableName);
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
		Schema::create($this->tableName, function(Blueprint $table)
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
		Schema::table($this->tableName, function(Blueprint $table)
		{
			if (! Schema::hasColumn($this->tableName, 'user_id'))
			{
				$this->createdColumns[] = 'user_id';
				$table->integer('user_id')->unsigned();
			}

			if (! Schema::hasColumn($this->tableName, 'group_id'))
			{
				$this->createdColumns[] = 'group_id';
				$table->integer('group_id')->unsigned();
			}

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails($this->tableName);

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
		Schema::drop($this->tableName);
	}

	protected function removeCautiously()
	{
		$columns = $this->createdColumns;

		if (!empty($columns))
		{
			Schema::table($this->tableName, function(Blueprint $table) use ($columns) {
				$table->dropColumn($columns);
			});
		}
	}
}
