<?php

use App\Models\UserTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateUserTasksTable
 */
class CreateUserTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('assigned_user_id')->unsigned();
            $table->text('description');
            $table->date('deadline');
            $table->tinyInteger('status')->default(UserTask::STATUS_ASSIGNED);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('user_tasks', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_tasks', function (Blueprint $table) {
            $table->dropForeign('user_tasks_user_id_foreign');
        });

        Schema::dropIfExists('user_tasks');
    }
}
