<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InventoryController {
 public function index(){$items=DB::table('inventory_items')->orderBy('name')->get();return view('inventory.index',compact('items'));}
 public function store(Request $request){$data=$request->validate(['name'=>'required|string|max:100','unit'=>'required|string|max:20','quantity'=>'required|numeric|min:0','reorder_level'=>'required|numeric|min:0','unit_cost'=>'required|numeric|min:0']);DB::table('inventory_items')->insert($data+['created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Ingredient recorded.');}
 public function adjust(Request $request,int $id){$data=$request->validate(['delta'=>'required|numeric|not_in:0','reason'=>'required|string|max:255']);DB::transaction(function()use($id,$data){$row=DB::table('inventory_items')->where('id',$id)->lockForUpdate()->first();abort_unless($row,404);$next=(float)$row->quantity+(float)$data['delta'];if($next<0)throw ValidationException::withMessages(['delta'=>'Insufficient available stock.']);DB::table('inventory_items')->where('id',$id)->update(['quantity'=>$next,'updated_at'=>now()]);DB::table('stock_movements')->insert(['inventory_item_id'=>$id,'user_id'=>auth()->id(),'delta'=>$data['delta'],'reason'=>$data['reason'],'created_at'=>now(),'updated_at'=>now()]);});return back()->with('success','Inventory quantity updated.');}
}
