<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AppSectionContoller;
use App\Http\Controllers\AppOrderController;
use App\Http\Controllers\AllServicesTotalController;
use App\Http\Controllers\SettingController;

use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceOrderController;

use App\Http\Controllers\TweetcellSectionController;
use App\Http\Controllers\TweetcellController;
use App\Http\Controllers\TweetcellOrderController;
use App\Http\Controllers\TweetcellKontorSectionController;
use App\Http\Controllers\TweetcellKontorController;
use App\Http\Controllers\TweetcellKontorOrderController;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CardOrderController;

use App\Http\Controllers\DataCommunicationController;
use App\Http\Controllers\DataCommunicationSectionController;
use App\Http\Controllers\DataCommunicationOrderController;

use App\Http\Controllers\EbankController;
use App\Http\Controllers\EbankSectionController;
use App\Http\Controllers\EbankOrderController;
use App\Http\Controllers\EcardSectionController;
use App\Http\Controllers\EcardOrderController;
use App\Http\Controllers\EcardController;

use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSectionController;
use App\Http\Controllers\GameOrderController;


use App\Http\Controllers\FaturaController;
use App\Http\Controllers\FaturaSectionController;
use App\Http\Controllers\FaturaOrderController;

use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgramOrderController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\TransferOrderController;

use App\Http\Controllers\TransferMoneyFirmController;
use App\Http\Controllers\TransferMoneyFirmOrderController;

use App\Http\Controllers\TurkificationOrderController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\User;

use App\Http\Controllers\VipController;
use App\Models\Setting;

use Illuminate\Support\Facades\Session;

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CurrencyController;
use App\Http\Middleware\LoadSettings;
use App\Mail\EbankEmail;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Mail;

Route::get('/favorites', [FavoriteController::class, 'getUserFavorites']);

    Route::get('/ss',function(){
Mail::raw('This is a test email using MailerSender', function ($message) {
    $message->to('info.eng.123@gmail.com')
            ->subject('Test MailerSender Email');
});
      });
    Route::get('test', [UserController::class, 'test']);  
    Route::get('test1', [UserController::class, 'test1']);

    
Route::middleware([LoadSettings::class])->group(function () {


Route::group(['middleware'=> 'auth'], function(){

    Route::get('/currency', [CurrencyController::class, 'index']);
    Route::post('/currency/set-base', [CurrencyController::class, 'setBaseCurrency']);
    Route::get('/currency/rate/{code}', [CurrencyController::class, 'getRate']);
    Route::resource('currency', CurrencyController::class);

    Route::get('/move-game-section/{id}', [GameSectionController::class, 'moveGameSectionToAppSection']);
    Route::get('/move-app-to-game-section/{id}', [AppSectionContoller::class, 'moveAppSectionToGameSection']);
    Route::get('/move-app-to-data-section/{id}', [AppSectionContoller::class, 'moveAppSectionToDataSection']);
    Route::get('/move-game-to-data-section/{id}', [GameSectionController::class, 'moveGameSectionToDataSection']);

    Route::get('/admin/unread-notifications', [NotificationController::class, 'getUnreadNotifications'])->name('unread.notifications');
   Route::post('/admin/mark-as-read/{notification}', [NotificationController::class, 'markAsRead']);
    Route::get('/admin/notifications', [NotificationController::class, 'getNotifications']);

  
  Route::get('/dashboard', [AllServicesTotalController::class, 'index']);
    
    Route::get('/', [AllServicesTotalController::class, 'index']);
    Route::get('/home', [AllServicesTotalController::class, 'index']);
    Route::resource( 'user', UserController::class,);
    
    Route::get('users/{id}/category', [UserController::class, 'showCategory']);
    
    Route::post('users/balance', [UserController::class, 'addBalanceToAgent'])->name('users.balance');


    Route::post('users/change-password/{id}', [UserController::class, 'changePassword'])->name('user.change-password');

    Route::get('users/agents', [UserController::class, 'getAgents']);
    Route::get('users/other/agent/{id}', [UserController::class, 'getOtherAgents']);
  
    Route::get('users/wallets', [UserController::class, 'getAgentWallets']);
    
    Route::get('users/transactions/{id}', [TransactionController::class, 'getUserTransactions']);
    
    Route::get('users/transactions/all/done/{id}', [TransactionController::class, 'getUserTransactionsDone']);
    
    Route::get('users/transactions/all/debts/{id}', [TransactionController::class, 'getUserTransactionsDebts']);
    Route::get('users/transactions/all/mydebts/{id}', [TransactionController::class, 'getUserTransactionsMyDebts']);
    
    Route::get('users/transactions/done/{id}', [TransactionController::class, 'setPaymentDone']);

    Route::post('users/transactions/partial/done/{id}', [TransactionController::class, 'setPartialPaymentDone']);
    Route::get('/profile', function () {  return view('backend.users.profile');});
 
    Route::resource('setting', SettingController::class);
    
    Route::resource('service-category', ServiceCategoryController::class);
  
    Route::get('service-category/{type}/type',[  ServiceCategoryController::class,'getType']);
  
    
    Route::post('/service-category/{id}/status',[ ServiceCategoryController::class,'changeStatus']);
    
    Route::get('/service/{id}/category',[ ServiceController::class,'showServices']);

    Route::resource('service', ServiceController::class);
    Route::resource('service-order',ServiceOrderController::class);
    Route::get('service-order/{type}/type',[ServiceOrderController::class,'getOrder']);
    Route::post('/service/{id}/status',[ ServiceController::class,'changeStatus']);   
    Route::post('service-order/reject/{id}',[ ServiceOrderController::class,'reject'])->name('service-order.reject');
    Route::get('service-order/accept/{id}',[ ServiceOrderController::class,'accept']);


  
   Route::resource('fatura-section', FaturaSectionController::class);
  
  
    
    Route::post('/fatura-section/{id}/status',[FaturaSectionController::class,'changeStatus']);
    
    Route::get('/fatura/{id}/section',[ FaturaController::class,'showFaturas']);

    Route::resource('fatura', FaturaController::class);
    

       Route::resource('fatura-order',FaturaOrderController::class);
  
       Route::get('fatura-order/{type}/type',[FaturaOrderController::class,'getOrder']);
  
       Route::post('/fatura/{id}/status',[ FaturaController::class,'changeStatus']);
       
    Route::post('fatura-order/reject/{id}',[ FaturaOrderController::class,'reject'])->name('fatura-order.reject');
    Route::get('fatura-order/accept/{id}',[ FaturaOrderController::class,'accept']);



    Route::resource('myapp', AppController::class);
    Route::resource('app-section', AppSectionContoller::class);
    Route::resource('app-order', AppOrderController::class);
    Route::get('app-order/reject/{id}',[ AppOrderController::class,'reject']);
    Route::get('app-order/accept/{id}',[ AppOrderController::class,'accept']);

    Route::get('/app/{id}/category',[ AppController::class,'showApps']);
    Route::post('/app/{id}/status',[ AppController::class,'changeStatus']);
    Route::post('/app-section/{id}/status',[ AppSectionContoller::class,'changeStatus']);
    
    Route::resource('card' , CardController::class);
    Route::post('/card/{id}/status' , [CardController::class,'changeStatus']);
    Route::resource('card-order' , CardOrderController::class);
    
    Route::get('card-order/reject/{id}',[ CardOrderController::class,'reject']);
    Route::get('card-order/accept/{id}',[ CardOrderController::class,'accept']);
    
    Route::resource('data-communication' , DataCommunicationController::class);
    Route::resource('data-communication-section' , DataCommunicationSectionController::class);
    Route::get('/data-communication/{id}/category'  ,[ DataCommunicationController::class,'showData']);
    Route::resource('data-communication-order' , DataCommunicationOrderController::class);
    Route::post('/data-communication/{id}/status',[ DataCommunicationController::class,'changeStatus']);
    
    Route::get('data-communication-order/reject/{id}',[ DataCommunicationOrderController::class,'reject']);
    Route::get('data-communication-order/accept/{id}',[ DataCommunicationOrderController::class,'accept']);


    Route::resource('ebank' , EbankController::class);
    Route::resource('ebank-section' , EbankSectionController::class);
    Route::get('/ebank/{id}/category',[ EbankController::class,'showEbanks']);
    Route::resource('ebank-order' , EbankOrderController::class);
    Route::post('/ebank/{id}/status',[ EbankController::class,'changeStatus']);
    Route::post('/ebank-section/{id}/status',[ EbankSectionController::class,'changeStatus']);    
    Route::get('ebank-order/reject/{id}',[ EbankOrderController::class,'reject']);
    Route::get('ebank-order/accept/{id}',[ EbankOrderController::class,'accept']);


    Route::resource('ecard' , EcardController::class);
    Route::resource('ecard-section' , EcardSectionController::class);
    Route::get('/ecard/{id}/category',[ EcardController::class,'showEcards']);
    Route::resource('ecard-order' , EcardOrderController::class);
    Route::post('/ecard-section/{id}/status',[ EcardSectionController::class,'changeStatus']);
    Route::post('/ecard/{id}/status',[ EcardController::class,'changeStatus']);
    Route::get('ecard-order/reject/{id}',[ EcardOrderController::class,'reject']);
    Route::get('ecard-order/accept/{id}',[ EcardOrderController::class,'accept']);

    Route::resource('oyun' , TweetcellController::class);
    Route::resource('oyun-section' , TweetcellSectionController::class);
    Route::get('oyun-section/{id}/type/{api}/provider' , [ TweetcellSectionController::class,'getByTypeByProvider']);
    Route::get('/oyun/{id}/category',[ TweetcellController::class,'showGames']);
    Route::get('oyun-order/{type}' , [TweetcellOrderController::class, 'index']);
    Route::post('/oyun-section/{id}/status',[ TweetcellSectionController::class,'changeStatus']);
    Route::post('/oyun/{id}/status',[ TweetcellController::class,'changeStatus']);
    Route::get('/oyun-status/off',[ TweetcellController::class,'offStatus']);


    Route::post('oyun-order/reject/{id}',[ TweetcellOrderController::class,'reject'])->name('oyun-order.reject');
    Route::get('oyun-order/accept/{id}',[TweetcellOrderController::class,'accept']);

    
    Route::resource('kontor' , TweetcellKontorController::class);
    Route::resource('kontor-section' , TweetcellKontorSectionController::class);
    Route::get('/kontor/{id}/category',[ TweetcellKontorController::class,'showGames']);
    Route::resource('kontor-order' ,TweetcellKontorOrderController::class);
    Route::post('/kontor-section/{id}/status',[ TweetcellKontorSectionController::class,'changeStatus']);
    Route::post('/kontor/{id}/status',[ TweetcellKontorController::class,'changeStatus']);

    Route::post('kontor-order/reject/{id}',[ TweetcellKontorOrderController::class,'reject'])->name('kontor-order.reject');
    Route::get('kontor-order/accept/{id}',[TweetcellKontorOrderController::class,'accept']);
      Route::get('kontor/firsat/{id}',[TweetcellKontorController::class,'firsat']);


    
    Route::resource('program' , ProgramController::class);
    Route::post('/program/{id}/status',[ProgramController::class,'changeStatus']);
    Route::resource('program-order' , ProgramOrderController::class);
    
    Route::get('program-order/reject/{id}',[ ProgramOrderController::class,'reject']);
    Route::get('program-order/accept/{id}',[ ProgramOrderController::class,'accept']);

    Route::resource('slider', SliderController::class);
    Route::get('hero/{type}',[ SliderController::class,'heroType']);//slider Or News

  
  
    Route::resource('transfer-money-firm' , TransferMoneyFirmController::class);
    Route::post('/transfer-money-firm/{id}/status',[TransferMoneyFirmController::class,'changeStatus']);
    Route::resource('transfer-money-firm-order' , TransferMoneyFirmOrderController::class);
    
    Route::get('transfer-money-firm-order/reject/{id}',[ TransferMoneyFirmOrderController::class,'reject']);
    Route::get('transfer-money-firm-order/accept/{id}',[ TransferMoneyFirmOrderController::class,'accept']);
    Route::get('transfer-money-firm-order/debt/{id}',[ TransferMoneyFirmOrderController::class,'debt']);
    Route::post('transfer-money-firm-payment-amount-done/debt/{id}',[ TransferMoneyFirmOrderController::class,'paymentDone']);

    Route::resource('transfer-order' , TransferOrderController::class);
    
    Route::get('transfer-order/reject/{id}',[ TransferOrderController::class,'reject']);
    Route::get('transfer-order/accept/{id}',[ TransferOrderController::class,'accept']);
    
    Route::resource('turkification-order' , TurkificationOrderController::class);
    
    Route::get('turkification-order/reject/{id}',[ TurkificationOrderController::class,'reject']);
    Route::get('turkification-order/accept/{id}',[ TurkificationOrderController::class,'accept']);
    
    Route::resource('vip', VipController::class,);
    

}); 

}); 
Auth::routes();