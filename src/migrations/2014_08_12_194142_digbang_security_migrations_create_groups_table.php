<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DigbangSecurityMigrationsCreateGroupsTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];
	protected $tableName = 'groups';

	function __construct()
	{
		$this->tableName = \Config::get('security::auth.groups.table', $this->tableName);
		$this->shouldUpdate = Schema::hasTable($this->tableName);
	}

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
			$table->increments('id');
			$table->string('name');
			$table->text('permissions')->nullable();
			$table->timestamps();

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->unique('name');
		});
	}

	protected function update()
	{
		Schema::table($this->tableName, function(Blueprint $table)
		{
			if (! Schema::hasColumn($this->tableName, 'id'))
			{
				$this->createdColumns[] = 'id';
				$table->increments('id');
			}

			if (! Schema::hasColumn($this->tableName, 'name'))
			{
				$this->createdColumns[] = 'name';
				$table->string('name');
			}

			if (! Schema::hasColumn($this->tableName, 'permissions'))
			{
				$this->createdColumns[] = 'permissions';
				$table->text('permissions')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'created_at'))
			{
				$this->createdColumns[] = 'created_at';
				$table->timestamp('created_at');
			}

			if (! Schema::hasColumn($this->tableName, 'updated_at'))
			{
				$this->createdColumns[] = 'updated_at';
				$table->timestamp('updated_at');
			}

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails($this->tableName);

			if (! $doctrineTable->hasIndex("{$this->tableName}_name_unique"))
			{
				$table->unique('name');
			}
		});
	}

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
