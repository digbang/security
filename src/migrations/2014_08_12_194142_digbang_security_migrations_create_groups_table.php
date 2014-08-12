<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DigbangSecurityMigrationsCreateGroupsTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];

	function __construct()
	{
		$this->shouldUpdate = Schema::hasTable('groups');
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
		Schema::create('groups', function(Blueprint $table)
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
		Schema::table('groups', function(Blueprint $table)
		{
			if (! Schema::hasColumn('groups', 'id'))
			{
				$this->createdColumns[] = 'id';
				$table->increments('id');
			}

			if (! Schema::hasColumn('groups', 'name'))
			{
				$this->createdColumns[] = 'name';
				$table->string('name');
			}

			if (! Schema::hasColumn('groups', 'permissions'))
			{
				$this->createdColumns[] = 'permissions';
				$table->text('permissions')->nullable();
			}

			if (! Schema::hasColumn('groups', 'created_at'))
			{
				$this->createdColumns[] = 'created_at';
				$table->timestamp('created_at');
			}

			if (! Schema::hasColumn('groups', 'updated_at'))
			{
				$this->createdColumns[] = 'updated_at';
				$table->timestamp('updated_at');
			}

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails('groups');

			if (! $doctrineTable->hasIndex('groups_name_unique'))
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
		Schema::drop('groups');
	}

	protected function removeCautiously()
	{
		$columns = $this->createdColumns;

		if (!empty($columns))
		{
			Schema::table('groups', function(Blueprint $table) use ($columns) {
				$table->dropColumn($columns);
			});
		}
	}
}
