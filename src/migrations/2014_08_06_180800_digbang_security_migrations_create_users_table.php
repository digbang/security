<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DigbangSecurityMigrationsCreateUsersTable extends Migration
{
	protected $shouldUpdate;
	protected $createdColumns = [];
	protected $tableName = 'users';

	function __construct()
	{
		$this->tableName = \Config::get('security::auth.users.table', $this->tableName);
		$this->shouldUpdate  = Schema::hasTable($this->tableName);
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
		Schema::create($this->tableName, function(Blueprint $table) {
			$table->increments('id');
			$table->string('email');
			$table->string('password');
			$table->text('permissions')->nullable();
			$table->boolean('activated')->default(0);
			$table->string('activation_code')->nullable();
			$table->timestamp('activated_at')->nullable();
			$table->timestamp('last_login')->nullable();
			$table->string('persist_code')->nullable();
			$table->string('reset_password_code')->nullable();
			$table->string('first_name')->nullable();
			$table->string('last_name')->nullable();
			$table->timestamps();

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->unique('email');
			$table->index('activation_code');
			$table->index('reset_password_code');
		});
	}

	protected function update()
	{
		Schema::table($this->tableName, function(Blueprint $table){
			if (! Schema::hasColumn($this->tableName, 'id'))
			{
				$this->createdColumns[] = 'id';
				$table->increments('id');
			}

			if (! Schema::hasColumn($this->tableName, 'email'))
			{
				$this->createdColumns[] = 'email';
				$table->string('email');
			}

			if (! Schema::hasColumn($this->tableName, 'password'))
			{
				$this->createdColumns[] = 'password';
				$table->string('password');
			}

			if (! Schema::hasColumn($this->tableName, 'permissions'))
			{
				$this->createdColumns[] = 'permissions';
				$table->text('permissions')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'activated'))
			{
				$this->createdColumns[] = 'activated';
				$table->boolean('activated')->default(0);
			}

			if (! Schema::hasColumn($this->tableName, 'activation_code'))
			{
				$this->createdColumns[] = 'activation_code';
				$table->string('activation_code')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'activated_at'))
			{
				$this->createdColumns[] = 'activated_at';
				$table->timestamp('activated_at')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'last_login'))
			{
				$this->createdColumns[] = 'last_login';
				$table->timestamp('last_login')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'persist_code'))
			{
				$this->createdColumns[] = 'persist_code';
				$table->string('persist_code')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'reset_password_code'))
			{
				$this->createdColumns[] = 'reset_password_code';
				$table->string('reset_password_code')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'first_name'))
			{
				$this->createdColumns[] = 'first_name';
				$table->string('first_name')->nullable();
			}

			if (! Schema::hasColumn($this->tableName, 'last_name'))
			{
				$this->createdColumns[] = 'last_name';
				$table->string('last_name')->nullable();
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
			$doctrineTable = Schema::getConnection()->getDoctrineSchemaManager()->listTableDetails('users');

			if (! $doctrineTable->hasIndex("{$this->tableName}_email_unique"))
			{
				$table->unique('email');
			}

			if (! $doctrineTable->hasIndex("{$this->tableName}_activation_code_index"))
			{
				$table->index('activation_code');
			}

			if (! $doctrineTable->hasIndex("{$this->tableName}_reset_password_code_index"))
			{
				$table->index('reset_password_code');
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