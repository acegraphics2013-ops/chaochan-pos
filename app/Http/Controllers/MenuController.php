<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class MenuController {
 public function index(){$products=DB::table('products')->join('categories','products.category_id','=','categories.id')->select('products.*','categories.name as category')->orderBy('products.id')->get();$categories=DB::table('categories')->orderBy('sort_order')->get();return view('menu.index',compact('products','categories'));}
 public function store(Request $request){$data=$this->input($request);$data['created_at']=now();$data['updated_at']=now();DB::table('products')->insert($data);return back()->with('success','Menu item added.');}
 public function update(Request $request,int $id){abort_unless(DB::table('products')->where('id',$id)->exists(),404);$data=$this->input($request,$id);$data['updated_at']=now();DB::table('products')->where('id',$id)->update($data);return back()->with('success','Menu item updated.');}
 public function destroy(int $id){$product=DB::table('products')->where('id',$id)->first();abort_unless($product,404);
   if(DB::table('order_items')->where('product_id',$id)->exists())return back()->with('error','This item appears in past orders. Hide it instead of deleting to keep sales history intact.');
   if(!empty($product->image_path))\Illuminate\Support\Facades\Storage::disk('public')->delete($product->image_path);
   DB::table('products')->where('id',$id)->delete();return back()->with('success','Menu item deleted.');}
 private function input(Request $request,?int $id=null):array {
   $data=$request->validate(['sku'=>['required','string','max:40',\Illuminate\Validation\Rule::unique('products','sku')->ignore($id)],'name'=>'required|string|max:200','category_id'=>'required|exists:categories,id','price'=>'required|numeric|min:0.01|max:999999','description'=>'nullable|string|max:500','available'=>'nullable|boolean','image'=>'nullable|image|max:4096']);
   unset($data['image']);$data['available']=$request->boolean('available');
   if($request->hasFile('image'))$data['image_path']=$request->file('image')->store('menu-images','public');
   return $data;
 }
}
