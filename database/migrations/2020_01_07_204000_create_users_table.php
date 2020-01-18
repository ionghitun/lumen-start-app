<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateUsersTable
 */
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('name')->nullable();
            $table->binary('email')->nullable();
            $table->string('password', 100)->nullable();
            $table->text('picture')->nullable();
            $table->tinyInteger('status')->default(User::STATUS_UNCONFIRMED);
            $table->bigInteger('language_id')->unsigned();
            $table->bigInteger('role_id')->unsigned();
            $table->string('activation_code', 50)->nullable();
            $table->string('forgot_code', 50)->nullable();
            $table->dateTime('forgot_time')->nullable();
            $table->text('facebook_id')->nullable();
            $table->text('google_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index([DB::raw('email(150)')]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_language_id_foreign');
            $table->dropForeign('users_role_id_foreign');
        });

        Schema::dropIfExists('users');
    }
}
