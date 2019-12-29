<?php

use App\Models\UserNotification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateUserNotificationsTable
 */
class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->text('message');
            $table->string('ref_name', 100)->nullable();
            $table->bigInteger('ref_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(UserNotification::STATUS_UNREAD);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('user_notifications', function (Blueprint $table) {
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
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropForeign('user_notifications_user_id_foreign');
        });

        Schema::dropIfExists('user_notifications');
    }
}
