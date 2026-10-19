<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,PosController,QrController,OrdersController,PaymentController,KitchenController,TableController,MenuController,ShiftController,SalesController,UserController,ExpenseController,InventoryController,ReceiptController,PermissionController};
Route::get('/',function(){ return auth()->check()?redirect()->route('dashboard'):redirect()->route('login');});
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'loginForm'])->name('login');Route::post('/login',[AuthController::class,'login'])->middleware('throttle:7,1')->name('login.submit');});
Route::get('/q/{token}',[QrController::class,'menu'])->name('qr.menu');
Route::post('/q/{token}/orders',[QrController::class,'submit'])->middleware('throttle:15,1')->name('qr.submit');
Route::get('/q/{token}/orders',[QrController::class,'orders'])->middleware('throttle:120,1')->name('qr.orders');
Route::get('/q/{token}/track/{order}',[QrController::class,'tracking'])->middleware('throttle:90,1')->name('qr.track');
Route::middleware('auth')->group(function(){
 Route::post('/logout',[AuthController::class,'logout'])->name('logout');
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
 Route::middleware('module:pos')->group(function(){
   Route::get('/pos',[PosController::class,'index'])->name('pos.index');Route::post('/pos/orders',[PosController::class,'store'])->name('pos.orders');
 });
 Route::middleware('module:orders')->group(function(){
   Route::get('/orders',[OrdersController::class,'index'])->name('orders.index');Route::get('/orders/feed',[OrdersController::class,'feed'])->name('orders.feed');Route::post('/orders/{id}/accept',[OrdersController::class,'accept'])->name('orders.accept');
   Route::post('/bills/{bill}/payments',[PaymentController::class,'store'])->name('bills.payments');
   Route::get('/bills/{bill}/receipt',[ReceiptController::class,'show'])->name('bills.receipt');
 });
 Route::middleware('module:shifts')->group(function(){
   Route::get('/shifts',[ShiftController::class,'index'])->name('shifts.index');Route::post('/shifts/open',[ShiftController::class,'open'])->name('shifts.open');Route::post('/shifts/close',[ShiftController::class,'close'])->name('shifts.close');
 });
 Route::post('/orders/{id}/cancel',[OrdersController::class,'cancel'])->middleware(['module:orders','role:admin,manager'])->name('orders.cancel');
 Route::middleware('module:kitchen')->group(function(){Route::get('/kitchen',[KitchenController::class,'index'])->name('kitchen.index');Route::get('/kitchen/feed',[KitchenController::class,'feed'])->name('kitchen.feed');Route::post('/kitchen/{id}/advance',[KitchenController::class,'advance'])->name('kitchen.advance');});
 Route::middleware('module:tables')->group(function(){Route::get('/tables',[TableController::class,'index'])->name('tables.index');Route::post('/tables',[TableController::class,'store'])->name('tables.store');Route::put('/tables/{id}',[TableController::class,'update'])->name('tables.update');Route::delete('/tables/{id}',[TableController::class,'destroy'])->name('tables.destroy');Route::get('/tables/{id}/qr.svg',[TableController::class,'qr'])->name('tables.qr');Route::post('/tables/{id}/close',[TableController::class,'close'])->name('tables.close');});
 Route::middleware('module:menu')->group(function(){Route::get('/menu',[MenuController::class,'index'])->name('menu.index');Route::post('/menu',[MenuController::class,'store'])->name('menu.store');Route::put('/menu/{id}',[MenuController::class,'update'])->name('menu.update');Route::delete('/menu/{id}',[MenuController::class,'destroy'])->name('menu.destroy');});
 Route::middleware('module:sales')->group(function(){Route::get('/sales',[SalesController::class,'index'])->name('sales.index');Route::get('/sales/export.csv',[SalesController::class,'csv'])->name('sales.csv');});
 Route::middleware('module:expenses')->group(function(){Route::get('/expenses',[ExpenseController::class,'index'])->name('expenses.index');Route::post('/expenses',[ExpenseController::class,'store'])->name('expenses.store');});
 Route::middleware('module:inventory')->group(function(){Route::get('/inventory',[InventoryController::class,'index'])->name('inventory.index');Route::post('/inventory',[InventoryController::class,'store'])->name('inventory.store');Route::post('/inventory/{id}/adjust',[InventoryController::class,'adjust'])->name('inventory.adjust');});
 Route::middleware('role:admin')->group(function(){
   Route::get('/users',[UserController::class,'index'])->name('users.index');Route::post('/users',[UserController::class,'store'])->name('users.store');Route::put('/users/{id}',[UserController::class,'update'])->name('users.update');Route::delete('/users/{id}',[UserController::class,'destroy'])->name('users.destroy');
   Route::get('/settings/permissions',[PermissionController::class,'index'])->name('settings.permissions');Route::post('/settings/permissions',[PermissionController::class,'update'])->name('settings.permissions.update');
 });
});
