<?php

use App\Http\Controllers\Legacy\ConfigController;
use App\Http\Controllers\Legacy\CustomerAdminController;
use App\Http\Controllers\Legacy\HomeAuthController;
use App\Http\Controllers\Legacy\OrderController;
use App\Http\Controllers\Legacy\PacketController;
use App\Http\Controllers\Legacy\SysAccountController;
use App\Http\Controllers\Legacy\SysAccountOrderController;
use App\Http\Controllers\Legacy\VietQrController;
use Illuminate\Support\Facades\Route;

Route::get('config6789.php', [ConfigController::class, 'show']);

Route::prefix('home')->group(function (): void {
    Route::post('register', [HomeAuthController::class, 'register']);
    Route::post('login', [HomeAuthController::class, 'login']);
    Route::post('logout1', [HomeAuthController::class, 'logout']);
    Route::post('SendCode', [HomeAuthController::class, 'sendCode']);
    Route::post('resetpass', [HomeAuthController::class, 'resetPassword']);
    Route::post('changepass', [HomeAuthController::class, 'changePassword']);
    Route::post('DeleteUser1', [HomeAuthController::class, 'deleteUser']);
    Route::get('GetInfoCustomer_ById/{id}', [HomeAuthController::class, 'getById']);
    Route::get('GetInfoCustomer_ByEmail/{email}', [HomeAuthController::class, 'getByEmail'])->where('email', '.*');
    Route::post('UpdateInfoCustomer_ById/{id}', [HomeAuthController::class, 'updateById']);
    Route::get('GetListCustomer_Bysericomputer/{serial}', [HomeAuthController::class, 'getBySerial']);

    Route::get('GetAllPacket', [PacketController::class, 'index']);
    Route::post('CreatePacket', [PacketController::class, 'store']);
    Route::post('UpdatePacket_ById/{id}', [PacketController::class, 'update']);
    Route::delete('DeletePacket_ById/{id}', [PacketController::class, 'destroy']);

    Route::post('BuyPacket_ByIdCustomer_1', [OrderController::class, 'buy']);
    Route::get('GetPacket_ByCustomerId/{customerId}', [OrderController::class, 'byCustomer']);
    Route::get('CancelPacket_ById/{paidId}', [OrderController::class, 'cancel']);
    Route::post('Get_Transactions_ByCustomerId', [OrderController::class, 'transactions']);

    Route::get('GetListCustomer', [CustomerAdminController::class, 'index']);
    Route::get('GetListCustomer_Delete', [CustomerAdminController::class, 'deleted']);
});

Route::prefix('sysaccount')->group(function (): void {
    Route::post('login', [SysAccountController::class, 'login']);
    Route::post('changepass', [SysAccountController::class, 'changePassword']);
    Route::post('createaccount', [SysAccountController::class, 'createAccount']);
    Route::post('SendCode', [SysAccountController::class, 'sendCode']);
    Route::post('resetpass', [SysAccountController::class, 'resetPassword']);
    Route::get('GetListAccount', [SysAccountController::class, 'listAccounts']);

    Route::get('OrderNew', [SysAccountOrderController::class, 'newOrders']);
    Route::get('GetAllOrder', [SysAccountOrderController::class, 'allOrders']);
    Route::get('order_endtime', [SysAccountOrderController::class, 'expiredOrders']);
    Route::get('detail_order/{paidId}', [SysAccountOrderController::class, 'detail']);
    Route::post('Filter_Packet', [SysAccountOrderController::class, 'filter']);
    Route::post('active_order_1/{paidId}', [SysAccountOrderController::class, 'activate']);
    Route::post('UpdateStatusCustomer', [CustomerAdminController::class, 'updateStatus']);
});

Route::prefix('vietQR')->group(function (): void {
    Route::post('getQRCode_ByPaidId', [VietQrController::class, 'getQrCode']);
    Route::get('page/{paidId}', [VietQrController::class, 'page']);
});
