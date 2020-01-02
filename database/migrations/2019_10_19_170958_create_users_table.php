<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->bigInteger('language_id')->unsigned();
            $table->binary('email')->nullable();
            $table->string('password', 100)->nullable();
            $table->text('picture')->nullable();
            $table->tinyInteger('status')->default(User::STATUS_UNCONFIRMED);
            $table->string('activation_code', 50)->nullable();
            $table->string('forgot_code', 50)->nullable();
            $table->dateTime('forgot_time')->nullable();
            $table->text('facebook_id')->nullable();
            $table->text('twitter_id')->nullable();
            $table->text('google_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
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
        });

        Schema::dropIfExists('users');
    }
}
