<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DigbangSecurityMigrationsCreateThrottleTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];

	function __construct()
	{
		$this->shouldUpdate = Schema::hasTable('throttle');
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
		Schema::create('throttle', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('ip_address')->nullable();
			$table->integer('attempts')->default(0);
			$table->boolean('suspended')->default(0);
			$table->boolean('banned')->default(0);
			$table->timestamp('last_attempt_at')->nullable();
			$table->timestamp('suspended_at')->nullable();
			$table->timestamp('banned_at')->nullable();

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->index('user_id');
		});
	}

	protected function update()
	{
		Schema::table('throttle', function(Blueprint $table)
		{
			if (! Schema::hasColumn('throttle', 'id'))
			{
				$this->createdColumns[] = 'id';
				$table->increments('id');
			}

			if (! Schema::hasColumn('throttle', 'user_id'))
			{
				$this->createdColumns[] = 'user_id';
				$table->integer('user_id')->unsigned()->nullable();
			}

			if (! Schema::hasColumn('throttle', 'ip_address'))
			{
				$this->createdColumns[] = 'ip_address';
				$table->string('ip_address')->nullable();
			}

			if (! Schema::hasColumn('throttle', 'attempts'))
			{
				$this->createdColumns[] = 'attempts';
				$table->integer('attempts')->default(0);
			}

			if (! Schema::hasColumn('throttle', 'suspended'))
			{
				$this->createdColumns[] = 'suspended';
				$table->boolean('suspended')->default(0);
			}

			if (! Schema::hasColumn('throttle', 'banned'))
			{
				$this->createdColumns[] = 'banned';
				$table->boolean('banned')->default(0);
			}

			if (! Schema::hasColumn('throttle', 'last_attempt_at'))
			{
				$this->createdColumns[] = 'last_attempt_at';
				$table->timestamp('last_attempt_at')->nullable();
			}

			if (! Schema::hasColumn('throttle', 'suspended_at'))
			{
				$this->createdColumns[] = 'suspended_at';
				$table->timestamp('suspended_at')->nullable();
			}

			if (! Schema::hasColumn('throttle', 'banned_at'))
			{
				$this->createdColumns[] = 'banned_at';
				$table->timestamp('banned_at')->nullable();
			}

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails('groups');

			if (! $doctrineTable->hasIndex('throttle_user_id_index'))
			{
				$table->index('user_id');
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
		Schema::drop('throttle');
	}

	protected function removeCautiously()
	{
		$columns = $this->createdColumns;

		if (!empty($columns))
		{
			Schema::table('throttle', function(Blueprint $table) use ($columns) {
				$table->dropColumn($columns);
			});
		}
	}
}
