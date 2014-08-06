<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DigbangSecurityMigrationsCreateUsersTable extends Migration
{
	protected $shouldUpdate;

	function __construct()
	{
		$this->shouldUpdate  = Schema::hasTable('users');
	}

	public function up()
	{
		if ($this->shouldUpdate)
			$this->update();
		else
			$this->create();
	}

	protected function create()
	{
		Schema::create('users', function(Blueprint $table) {
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
		Schema::table('users', function(Blueprint $table){
			if (! Schema::hasColumn('users', 'id'))
				$table->increments('id');

			if (! Schema::hasColumn('users', 'email'))
				$table->string('email');

			if (! Schema::hasColumn('users', 'password'))
				$table->string('password');

			if (! Schema::hasColumn('users', 'permissions'))
				$table->text('permissions')->nullable();

			if (! Schema::hasColumn('users', 'activated'))
				$table->boolean('activated')->default(0);

			if (! Schema::hasColumn('users', 'activation_code'))
				$table->string('activation_code')->nullable();

			if (! Schema::hasColumn('users', 'activated_at'))
				$table->timestamp('activated_at')->nullable();

			if (! Schema::hasColumn('users', 'last_login'))
				$table->timestamp('last_login')->nullable();

			if (! Schema::hasColumn('users', 'persist_code'))
				$table->string('persist_code')->nullable();

			if (! Schema::hasColumn('users', 'reset_password_code'))
				$table->string('reset_password_code')->nullable();

			if (! Schema::hasColumn('users', 'first_name'))
				$table->string('first_name')->nullable();

			if (! Schema::hasColumn('users', 'last_name'))
				$table->string('last_name')->nullable();

			if (! Schema::hasColumn('users', 'created_at'))
				$table->timestamp('created_at');

			if (! Schema::hasColumn('users', 'updated_at'))
				$table->timestamp('updated_at');

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->unique('email');
			$table->index('activation_code');
			$table->index('reset_password_code');
		});
	}

	public function down()
	{
		if ($this->shouldUpdate)
			$this->removeCautiously();
		else
			$this->drop();
	}

	protected function drop()
	{
		Schema::drop('users');
	}

	protected function removeCautiously()
	{
		// Do nothing. I guess.
	}
}