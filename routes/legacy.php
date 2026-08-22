<?php

use App\Http\Controllers\Legacy\CampaignController;
use App\Http\Controllers\Legacy\CommandController;
use App\Http\Controllers\Legacy\ConfigController;
use App\Http\Controllers\Legacy\CustomerAdminController;
use App\Http\Controllers\Legacy\DeviceController;
use App\Http\Controllers\Legacy\DirController;
use App\Http\Controllers\Legacy\HomeAuthController;
use App\Http\Controllers\Legacy\MediaController;
use App\Http\Controllers\Legacy\NotifyController;
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

    Route::post('CreateDir', [DirController::class, 'create']);
    Route::get('GetDirCustomer_ById/{customerId}', [DirController::class, 'byCustomer']);
    Route::get('GetDir_ById/{idDir}', [DirController::class, 'byId']);
    Route::get('GetDirCustomer_SharedById/{customerId}', [DirController::class, 'sharedToCustomer']);
    Route::get('GetShareDir_ByCustomerId/{customerId}', [DirController::class, 'sharedFromCustomer']);
    Route::post('UpDateDir_ById/{idDir}', [DirController::class, 'update']);
    Route::get('DeleteDir_ById/{idDir}', [DirController::class, 'destroy']);
    Route::post('InsertDirShare', [DirController::class, 'share']);
    Route::get('GetSharedCustomerList_ByDirID/{idDir}', [DirController::class, 'sharedCustomers']);
    Route::get('DeleteDir_shared/{idDir}/{customerId}', [DirController::class, 'deleteShare']);
    Route::post('UpDateOnOffDeviceDir_ById/{idDir}', [DirController::class, 'updateOnOff']);

    Route::post('CreateDevice', [DeviceController::class, 'create']);
    Route::get('GetDevices_ByCustomerId/{customerId}', [DeviceController::class, 'byCustomer']);
    Route::get('GetDevice_ByComputerID/{computerId}', [DeviceController::class, 'byComputerId']);
    Route::get('GetDevice_ByIdDir/{idDir}', [DeviceController::class, 'byDir']);
    Route::get('GetDevicesNotBelongAnyDir_ByCustomerId/{customerId}', [DeviceController::class, 'notInDir']);
    Route::post('UpDateDevice_ById/{computerId}', [DeviceController::class, 'update']);
    Route::get('DeleteDevice_ById/{computerId}', [DeviceController::class, 'destroy']);
    Route::get('GetListDeviceOfCamp_ByCampId/{campaignId}', [DeviceController::class, 'ofCampaign']);
    Route::post('InsertDeviceShare', [DeviceController::class, 'share']);
    Route::get('GetSharedCustomerList_ByComputeID/{computerId}', [DeviceController::class, 'sharedCustomers']);
    Route::get('GetDeviceCustomer_SharedById/{customerId}', [DeviceController::class, 'sharedToCustomer']);
    Route::get('GetSharedDevices_ByCustomerId/{customerId}', [DeviceController::class, 'sharedFromCustomer']);
    Route::get('DeleteDevice_shared/{computerId}/{customerId}', [DeviceController::class, 'deleteShare']);
    Route::post('UpdateRomMemory/{computerId}', [DeviceController::class, 'updateRom']);
    Route::get('UpdateAliveTimeDevice_ById/{computerId}', [DeviceController::class, 'updateAlive']);
    Route::get('UpdateComputerToken_ById/{computerId}/{token}', [DeviceController::class, 'updateToken'])->where('token', '.*');

    Route::post('CreateCamp', [CampaignController::class, 'create']);
    Route::post('UpdateCamp_ById/{campaignId}', [CampaignController::class, 'update']);
    Route::get('DeleteCamp_ById/{campaignId}', [CampaignController::class, 'destroy']);
    Route::post('ApproveCamp_ById/{campaignId}', [CampaignController::class, 'approve']);
    Route::get('GetAllCamp_ById/{customerId}', [CampaignController::class, 'allByCustomer']);
    Route::get('Getcamp_ByComputerId/{computerId}/{status}', [CampaignController::class, 'byComputer']);
    Route::get('Getcamp_ByDirId/{idDir}/{status}', [CampaignController::class, 'byDir']);
    Route::get('GetCampToday_ByComputerId/{computerId}/{date}/{flag}', [CampaignController::class, 'today']);
    Route::post('GetAllRunTimeOfComputer_4', [CampaignController::class, 'runTimesOfComputer']);
    Route::get('GetTimeRun_ByCampId/{campaignId}', [CampaignController::class, 'timeRuns']);
    Route::get('GetTimeRun_ByCampId_1/{campaignId}/{idDir}', [CampaignController::class, 'timeRunsWithDir']);
    Route::post('AddTimeRun_ByCamp', [CampaignController::class, 'addTimeRun']);
    Route::post('UpdateTimeRun_ByIdRun', [CampaignController::class, 'updateTimeRun']);
    Route::get('DeleteTimeRun_ByIdRun/{idRun}', [CampaignController::class, 'deleteTimeRun']);
    Route::get('GetDefaultTimeRun_ByIdDir/{idDir}', [CampaignController::class, 'defaultTimeRun']);
    Route::get('UpdateDefaultCamp_ById/{campaignId}', [CampaignController::class, 'setDefault']);
    Route::get('UpdateRunByDefaultCamp_ById/{campaignId}/{used}', [CampaignController::class, 'setRunByDefault']);
    Route::get('GetCamp_SharedByCustomerId/{customerId}', [CampaignController::class, 'sharedToCustomer']);
    Route::get('GetShareCamp_ByCustomerId/{customerId}', [CampaignController::class, 'shareCampByCustomer']);
    Route::post('AddCampaignRunProfile', [CampaignController::class, 'addRunProfile']);
    Route::post('GetCampaignRunProfile', [CampaignController::class, 'runProfile']);
    Route::post('GetCampaignRunProfile_Genaral', [CampaignController::class, 'runProfileGeneral']);

    Route::post('checkdir_customer', [MediaController::class, 'checkDir']);
    Route::post('createdir_customer', [MediaController::class, 'createDir']);
    Route::post('getfiles_customer', [MediaController::class, 'files']);
    Route::post('getsizeofdir_customer', [MediaController::class, 'dirSize']);
    Route::post('uploadfile_customer', [MediaController::class, 'upload']);
    Route::post('uploadfile_customer_large', [MediaController::class, 'uploadLarge']);
    Route::post('deletefile_customer', [MediaController::class, 'delete']);
    Route::post('cancelUpload', [MediaController::class, 'cancel']);

    Route::get('GetNofity_ByIdCustomer/{customerId}', [NotifyController::class, 'byCustomer']);
    Route::get('GetNofityNew_ByIdCustomer/{customerId}', [NotifyController::class, 'newCountByCustomer']);
    Route::get('GetNofity_ByIdAccount/{accountId}', [NotifyController::class, 'byAccount']);
    Route::get('GetNofityNew_ByIdAccount/{accountId}', [NotifyController::class, 'newCountByAccount']);
    Route::get('GetNofity_ById/{id}', [NotifyController::class, 'byId']);
    Route::get('UpdateNotify/{id}', [NotifyController::class, 'markRead']);
    Route::post('InsertNotify', [NotifyController::class, 'insert']);
    Route::post('InsertNotify_Account', [NotifyController::class, 'insertAccount']);

    Route::post('CreateCommand', [CommandController::class, 'create']);
    Route::get('GetInfoCommand_ByID/{id}', [CommandController::class, 'byId']);
    Route::get('GetNewCommands_BySeriComputer/{serial}', [CommandController::class, 'newBySerial'])->where('serial', '.*');
    Route::post('ReplyCommand/{id}', [CommandController::class, 'reply']);
});

Route::match(['get', 'head'], 'uploads/{token}/{filename}', [MediaController::class, 'serve'])->where('filename', '.*');

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
