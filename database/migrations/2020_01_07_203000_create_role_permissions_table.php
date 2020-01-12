<?php

use App\Models\RolePermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateRolePermissionsTable
 */
class CreateRolePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('role_id')->unsigned();
            $table->bigInteger('permission_id')->unsigned();
            $table->tinyInteger('read')->default(RolePermission::PERMISSION_FALSE);
            $table->tinyInteger('create')->default(RolePermission::PERMISSION_FALSE);
            $table->tinyInteger('update')->default(RolePermission::PERMISSION_FALSE);
            $table->tinyInteger('delete')->default(RolePermission::PERMISSION_FALSE);
            $table->tinyInteger('manage')->default(RolePermission::MANAGE_OWN);
            $table->timestamps();
            $table->unique(['role_id', 'permission_id']);
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('permission_id')->references('id')->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('role_permissions_role_id_foreign');
            $table->dropForeign('role_permissions_permission_id_foreign');
        });

        Schema::dropIfExists('role_permissions');
    }
}
