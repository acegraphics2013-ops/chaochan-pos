<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
class DatabaseSeeder extends Seeder {
 public function run():void {
  $menu=[
   'Paldo Meals'=>[['H1','Hungarian Sausage w/ Fried Rice & Egg',120],['L1','Lechon Kawali w/ Fried Rice & Egg',129],['F1','Fried Pork Chop w/ Fried Rice & Egg',129],['B1','Beef Tapa w/ Fried Rice & Egg',129],['S1','Sisig w/ Fried Rice & Egg',120]],
   'Paldo Combo'=>[['H2','Hungarian Sausage + Sisig w/ Fried Rice & Egg',189],['H3','Hungarian Sausage + Beef Tapa w/ Fried Rice & Egg',189],['H4','Hungarian Sausage + Kawali w/ Fried Rice & Egg',189],['H5','Hungarian Sausage + Fried Pork Chop w/ Fried Rice & Egg',189],['B2','Beef Tapa + Kawali w/ Fried Rice & Egg',199]],
   'Canned Goods & Silog'=>[['CB','Corned Beef w/ Fried Rice & Egg',85],['LM','Luncheon Meat w/ Fried Rice & Egg',89],['ML','Meatloaf w/ Fried Rice & Egg',85],['HD','Hotsilog w/ Fried Rice & Egg',75],['TOC','Tocino w/ Fried Rice & Egg',89],['SP','Spam w/ Fried Rice & Egg',109],['SEO','Special Egg Omelette w/ Fried Rice',65]],
   'Canton & Noodles'=>[['CS','Canton Solo',29],['CE','Canton w/ Egg',39],['CFR','Canton w/ Fried Rice & Egg',59],['N','Noodles',20],['NE','Noodles w/ Egg',30]],
   'Add-ons & Drinks'=>[['ADD-EGG','Extra Egg',15],['ADD-RICE','Extra Fried Rice',25],['DRK-SWAKTO','Coke Swakto',18],['DRK-MISMO','Coke Mismo',25]]
  ];
  foreach($menu as $cat=>$items){
    $categoryId=DB::table('categories')->updateOrInsert(['name'=>$cat],['sort_order'=>array_search($cat,array_keys($menu))+1,'updated_at'=>now()]);
    $id=DB::table('categories')->where('name',$cat)->value('id');
    foreach($items as [$sku,$name,$price]) DB::table('products')->updateOrInsert(['sku'=>$sku],['category_id'=>$id,'name'=>$name,'price'=>$price,'available'=>true,'updated_at'=>now(),'created_at'=>now()]);
  }
  // Seed 8 tables once; table count is configurable through Table Management.
  for($i=1;$i<=8;$i++){
    $n=str_pad((string)$i,2,'0',STR_PAD_LEFT);
    if(!DB::table('restaurant_tables')->where('number',$n)->exists()){
      $token=bin2hex(random_bytes(24));
      DB::table('restaurant_tables')->insert(['number'=>$n,'capacity'=>4,'status'=>'available','qr_token_hash'=>hash('sha256',$token),'qr_token_encrypted'=>Crypt::encryptString($token),'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
    }
  }
 }
}
