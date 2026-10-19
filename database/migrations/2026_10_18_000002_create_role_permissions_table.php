<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('role_permissions',function(Blueprint $t){$t->id();$t->string('role',30);$t->string('module',40);$t->boolean('enabled')->default(true);$t->timestamps();$t->unique(['role','module']);});
  $defaults=[
   'admin'=>['pos','orders','shifts','kitchen','tables','menu','sales','expenses','inventory'],
   'manager'=>['pos','orders','shifts','kitchen','tables','menu','sales','expenses','inventory'],
   'cashier'=>['pos','orders','shifts'],
   'kitchen'=>['kitchen'],
  ];
  $now=now();
  foreach($defaults as $role=>$modules){
   foreach($modules as $module)DB::table('role_permissions')->insert(['role'=>$role,'module'=>$module,'enabled'=>true,'created_at'=>$now,'updated_at'=>$now]);
  }
 }
 public function down():void {Schema::dropIfExists('role_permissions');}
};
