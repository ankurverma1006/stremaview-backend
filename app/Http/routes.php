<?php

use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/clear-cache', function() {

    $exitCode = Artisan::call('config:cache');

   return back();

})->name('clear-cache');

/***********************  UI Routes *********************/


/***********************  UI Routes *********************/

/*********************** TEST ROUTES *********************/

Route::any('/interview_tasks' , 'SampleController@interview_tasks');

Route::get('/test' , 'SampleController@test');

// Route::get('/test' , 'SampleController@test');

Route::post('/test' , 'SampleController@get_image')->name('sample');

Route::get('/export' , 'SampleController@sample_export');

Route::get('/compress' , 'SampleController@compress_image_upload')->name('compress.image');

Route::get('/compress/i' , 'SampleController@compress_image_check');

Route::post('/compress/image' , 'SampleController@getImageThumbnail');

Route::get('/sendpush' , 'SampleController@send_push_notification');

/*********************** TEST ROUTES *********************/

Route::get('/generate/index' , 'ApplicationController@generate_index');


/***********************  UI Routes *********************/


Route::get('/embed', 'UserController@embed_video')->name('embed_video');

Route::get('/g_embed', 'UserController@genre_embed_video')->name('genre_embed_video');


// Singout from all devices which is expired account

Route::get('signout/all/devices', 'ApplicationController@signout_all_devices');

Route::get('/email/verification' , 'ApplicationController@email_verify')->name('email.verify');

Route::get('/check/token', 'ApplicationController@check_token_expiry')->name('check_token_expiry');

// Installation

Route::get('/configuration', 'InstallationController@install')->name('installTheme');

Route::get('/system/check', 'InstallationController@system_check_process')->name('system-check');

Route::post('/configuration', 'InstallationController@theme_check_process')->name('install.theme');

Route::post('/install/settings', 'InstallationController@settings_process')->name('install.settings');


// CRON

Route::get('/publish/video', 'ApplicationController@cron_publish_video')->name('publish');

Route::get('/notification/payment', 'ApplicationController@send_notification_user_payment')->name('notification.user.payment');

Route::get('/payment/expiry', 'ApplicationController@user_payment_expiry')->name('user.payment.expiry');

Route::get('/payment/failure' , 'ApplicationController@payment_failure')->name('payment.failure');

Route::get('/automatic/renewal', 'ApplicationController@automatic_renewal_stripe')->name('automatic.renewal');

Route::get('check/download/status', 'ApplicationController@checkDownloadVideoStatus')->name('checkDownloadVideoStatus');

// Generral configuration routes 

Route::post('project/configurations' , 'ApplicationController@configuration_site');


// Static Pages

Route::get('/privacy', 'UserApiController@privacy')->name('user.privacy');

Route::get('/terms_condition', 'UserApiController@terms')->name('user.terms');

Route::get('/static/terms', 'UserApiController@terms')->name('user.terms');

Route::get('/contact', 'UserController@contact')->name('user.contact');

Route::get('/privacy_policy', 'ApplicationController@privacy')->name('user.privacy_policy');

Route::get('/terms', 'ApplicationController@terms')->name('user.terms-condition');

Route::get('/about', 'ApplicationController@about')->name('user.about');

// Video upload 

Route::post('select/sub_category' , 'ApplicationController@select_sub_category')->name('select.sub_category');

Route::post('select/genre' , 'ApplicationController@select_genre')->name('select.genre');

Route::get('/admin-control', 'ApplicationController@admin_control')->name('admin_control');

Route::post('save_admin_control', 'ApplicationController@save_admin_control')->name('save_admin_control');

Route::get('/admin/check_role', 'AdminController@check_role');


Route::get('/', 'AdminController@dashboard')->name('dashboard');


Route::group(['prefix' => 'admin'  , 'as' => 'admin.'], function() {

    Route::get('/', 'AdminController@dashboard')->name('dashboard');

    Route::get('login', 'Auth\AdminAuthController@showLoginForm')->name('login');

    Route::post('login', 'Auth\AdminAuthController@login')->name('login.post');

    Route::get('logout', 'Auth\AdminAuthController@logout')->name('logout');

    // Registration Routes...

    Route::get('register', 'Auth\AdminAuthController@showRegistrationForm');

    Route::post('register', 'Auth\AdminAuthController@register');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\AdminPasswordController@showResetForm');

    Route::post('password/email', 'Auth\AdminPasswordController@sendResetLinkEmail');

    Route::post('password/reset', 'Auth\AdminPasswordController@reset');

    //  Admin User Methods starts

    Route::get('/users', 'AdminController@users_index')->name('users.index');

    Route::get('users/create', 'AdminController@users_create')->name('users.create');

    Route::get('users/edit', 'AdminController@users_edit')->name('users.edit');

    Route::post('users/create', 'AdminController@users_save')->name('users.save');

    Route::get('/users/view', 'AdminController@users_view')->name('users.view');

    Route::get('users/delete', 'AdminController@users_delete')->name('users.delete');

    Route::get('users/status/change', 'AdminController@users_status_change')->name('users.status.change');
    
    Route::get('/users/verify', 'AdminController@users_verify_status')->name('users.verify');

    Route::get('/users/upgrade', 'AdminController@users_upgrade')->name('users.upgrade');

    Route::any('users/upgrade-disable', 'AdminController@users_upgrade_disable')->name('users.upgrade.disable');

    Route::get('/users/subprofiles', 'AdminController@users_sub_profiles')->name('users.subprofiles');

    Route::get('/users/clear-login', 'AdminController@users_clear_login')->name('users.clear-login');

    Route::get('/uses/subscriptions', 'AdminController@users_subscriptions')->name('subscriptions.plans');

    //  Admin Users History - admin

    Route::get('/users/history', 'AdminController@users_history')->name('users.history');

    Route::get('users/history/remove', 'AdminController@users_history_remove')->name('users.history.remove');
    
    //  Admin Users Wishlist - admin

    Route::get('/users/wishlist', 'AdminController@users_wishlist')->name('users.wishlist');

    Route::get('/users/wishlist/remove', 'AdminController@users_wishlist_remove')->name('users.wishlist.remove');

    Route::post('/users/subscriptions/disable', 'AdminController@users_auto_subscription_disable')->name('subscriptions.cancel');

    Route::get('/users/subscriptions/enable', 'AdminController@users_auto_subscription_enable')->name('subscriptions.enable');

    //  Admin User Methods Ends

    //  Admin moderators Methods begins

    Route::get('/moderators', 'AdminController@moderators_index')->name('moderators.index');

    Route::get('/moderators/create', 'AdminController@moderators_create')->name('moderators.create');

    Route::get('/moderators/edit', 'AdminController@moderators_edit')->name('moderators.edit');

    Route::post('/moderators/create', 'AdminController@moderators_save')->name('moderators.save');

    Route::get('/moderators/delete', 'AdminController@moderators_delete')->name('moderators.delete');

    Route::get('/moderators/view', 'AdminController@moderators_view')->name('moderators.view');

    Route::get('/moderators/videos','AdminController@moderators_videos')->name('moderators.videos.index');

    Route::get('moderators/status/change', 'AdminController@moderators_status_change')->name('moderators.status.change');


    Route::get('/moderator/videos/{id}','AdminController@moderator_videos')->name('moderator.videos.list');


    Route::get('moderators/redeems', 'AdminController@moderators_redeem_requests')->name('moderators.redeems');

    Route::any('/moderators/payout/invoice', 'AdminController@moderators_redeems_payout_invoice')->name('moderators.payout.invoice');

     Route::post('moderators/payout/direct', 'AdminController@moderators_redeems_payout_direct')->name('moderators.payout.direct');

    Route::any('/moderators/payout/response', 'AdminController@moderators_redeems_payout_response')->name('moderators.payout.response');

    // Redeem Pay from Paypal

    Route::get('moderator/redeem-pay', 'RedeemPaymentController@redeem_pay')->name('moderator.redeem_pay');

    Route::get('moderator/redeem-pay-status', 'RedeemPaymentController@redeem_pay_status')->name('moderator.redeem_pay_status');
    
    // Admin moderators Methods ends


    // Admin Categories Methods begins

    Route::get('/categories', 'AdminController@categories_index')->name('categories.index');

    Route::get('/categories/create', 'AdminController@categories_create')->name('categories.create');

    Route::get('/categories/edit', 'AdminController@categories_edit')->name('categories.edit');

    Route::post('/categories/create', 'AdminController@categories_save')->name('categories.save');

    Route::get('/categories/delete', 'AdminController@categories_delete')->name('categories.delete');

    Route::get('/categories/view', 'AdminController@categories_view')->name('categories.view');

    Route::get('categories/status/change', 'AdminController@categories_status_change')->name('categories.status.change');

    // USER HOME PAGE DISPLAY
    
    Route::get('categories/home/status', 'AdminController@categories_home_status')->name('categories.home.status');

    // Admin Categories Methods ends

    // Admin Sub Categories Methods begins

    Route::get('/sub_categories', 'AdminController@sub_categories_index')->name('sub_categories.index');

    Route::get('/sub_categories/create', 'AdminController@sub_categories_create')->name('sub_categories.create');

    Route::get('/sub_categories/edit', 'AdminController@sub_categories_edit')->name('sub_categories.edit');

    Route::post('/sub_categories/create', 'AdminController@sub_categories_save')->name('sub_categories.save');

    Route::get('/sub_categories/delete', 'AdminController@sub_categories_delete')->name('sub_categories.delete');

    Route::get('/sub_categories/view', 'AdminController@sub_categories_view')->name('sub_categories.view');

    Route::get('sub_categories/status/change', 'AdminController@sub_categories_status_change')->name('sub_categories.status.change');

    // Admin Sub Categories Methods ends

    // Admin Genres Methods begins

    Route::get('/genres', 'AdminController@genres_index')->name('genres.index');

    Route::get('/genres/create', 'AdminController@genres_create')->name('genres.create');

    Route::get('/genres/edit', 'AdminController@genres_edit')->name('genres.edit');

    Route::post('/genres/create', 'AdminController@genres_save')->name('genres.save');

    Route::get('/genres/delete', 'AdminController@genres_delete')->name('genres.delete');

    Route::get('/genres/view', 'AdminController@genres_view')->name('genres.view');

    Route::get('genres/status/change', 'AdminController@genres_status_change')->name('genres.status.change');

    Route::post('genres/position/change', 'AdminController@genre_position_change')->name('genres.position.change');

    // Admin Genres Methods ends
    
    // Admin Cast & crews Methods begin   

    Route::get('/cast-crews/index', 'AdminController@cast_crews_index')->name('cast_crews.index');

    Route::get('/cast-crews/create', 'AdminController@cast_crews_create')->name('cast_crews.create');

    Route::get('/cast-crews/edit', 'AdminController@cast_crews_edit')->name('cast_crews.edit');

    Route::post('/cast-crews/save', 'AdminController@cast_crews_save')->name('cast_crews.save');
    
    Route::get('/cast-crews/view', 'AdminController@cast_crews_view')->name('cast_crews.view');

    Route::get('/cast-crews/delete', 'AdminController@cast_crews_delete')->name('cast_crews.delete');

    Route::get('/cast_crews/status/change', 'AdminController@cast_crews_status_change')->name('cast_crews.status.change');

    // Admin Cast & crews Methods ends


    // Videos Methods begins

    Route::get('/videos', 'AdminController@admin_videos_index')->name('videos');
    
    Route::get('/videos/create', 'AdminController@admin_videos_create')->name('videos.create');

    Route::get('/videos/edit/', 'AdminController@admin_videos_edit')->name('videos.edit');

    Route::post('/videos/save', 'AdminController@admin_videos_save')->name('videos.save');

    Route::get('/videos/view', 'AdminController@admin_videos_view')->name('view.video');

    Route::get('/gif/generation', 'AdminController@gif_generator')->name('gif_generator');

    Route::get('/videos/delete', 'AdminController@admin_videos_delete')->name('delete.video');

    Route::get('/videos/approve', 'AdminController@admin_videos_status_approve')->name('videos.approve');

    Route::get('/video/decline/', 'AdminController@admin_videos_status_decline')->name('videos.decline');

    Route::get('/video/publish-video', 'AdminController@admin_videos_publish')->name('video.publish-video');

    Route::post('/video/change/position', 'AdminController@admin_videos_change_position')->name('save.video.position');

    Route::post('/videos/ppv/add/{id}', 'AdminController@admin_videos_ppv_add')->name('save.video-payment');

    Route::get('/videos/ppv/remove/', 'AdminController@admin_videos_ppv_remove')->name('remove_pay_per_view');


    // Video compression status

    Route::get('/videos/compression/complete','AdminController@admin_videos_compression_complete')->name('compress.status');

    // Videos original section

    Route::get('videos/originals/status', 'AdminController@admin_videos_original_status')->name('admin_videos.originals.status');


    // Banner videos

    Route::post('videos/banner/set', 'AdminController@admin_videos_banner_add')->name('banner.set');

    Route::get('videos/banner/remove', 'AdminController@admin_videos_banner_remove')->name('banner.remove');


    // Slider Videos (Not usinh)

    Route::get('/videos/slider', 'AdminController@admin_videos_slider_status')->name('slider.video');

    // Banner Videos ( NOT USING)

    Route::get('/banner/videos', 'AdminController@banner_videos')->name('banner.videos');

    // Route::get('/add/banner/video', 'AdminController@add_banner_video')->name('add.banner.video');

    // Route::get('/change/banner/video/{id}', 'AdminController@change_banner_video')->name('change.video');

    
    Route::get('/spam-videos', 'AdminController@admin_videos_spams')->name('spam-videos');

    Route::get('/spam-videos/user-reports/', 'AdminController@admin_videos_spams_user_reports')->name('spam-videos.user-reports');

    // Admin videos section end


    // New Coupons Methods begins

    Route::get('/coupons/create','AdminController@coupons_create')->name('coupons.create');

    Route::get('/coupons/edit','AdminController@coupons_edit')->name('coupons.edit');

    Route::post('/coupons/save','AdminController@coupons_save')->name('coupons.save');

    Route::get('/coupons/index','AdminController@coupons_index')->name('coupons.index');

    Route::get('/coupons/view','AdminController@coupons_view')->name('coupons.view');

    Route::get('/coupons/delete','AdminController@coupons_delete')->name('coupons.delete');

    Route::get('/coupons/status','AdminController@coupons_status_change')->name('coupons.status');

    // New Coupons Methods ends

    // Custom Push

    Route::get('/custom/push', 'AdminController@custom_push')->name('push');

    Route::post('/custom/push', 'AdminController@custom_push_save')->name('send.push');

    // Admin email Methods start

    Route::get('/email/form','AdminController@mailcamp_create')->name('mailcamp.create');

    Route::post('/email/form/action','AdminController@email_send_process')->name('email.success');

    // Admin email Methods ends


    // Admin templates Methods begins

    Route::get('/templates', 'AdminController@templates_index')->name('templates.index');

    Route::get('templates/create/', 'AdminController@templates_create')->name('templates.create');

    Route::get('templates/edit/', 'AdminController@templates_edit')->name('templates.edit');

    Route::post('templates/save/', 'AdminController@templates_save')->name('templates.save');

    Route::get('templates/view/', 'AdminController@templates_view')->name('templates.view');

    // Admin templates Methods ends

    Route::get('help' , 'AdminController@help')->name('help');


    // Exports tables

    Route::get('/users/export/', 'AdminExportController@users_export')->name('users.export');

    Route::get('/moderators/export/', 'AdminExportController@moderators_export')->name('moderators.export');

    Route::get('/videos/export/', 'AdminExportController@videos_export')->name('videos.export');

    Route::get('/subscription/payment/export/', 'AdminExportController@subscription_export')->name('subscription.export');

    Route::get('/payperview/payment/export/', 'AdminExportController@payperview_export')->name('payperview.export');

    // Exports tables methods ends

    Route::get('/videos/download/status', 'AdminController@admin_videos_download_status')->name('admin_videos.download_status');
});

Route::group(['middleware' => ['AdminMiddleware' , 'admin'] , 'prefix' => 'admin'  , 'as' => 'admin.'], function() {

    // Admins CRUD Operations

    Route::get('admins/create', 'AdminController@admins_create')->name('admins.create');

    Route::get('admins/edit', 'AdminController@admins_edit')->name('admins.edit');

    Route::get('admins/view', 'AdminController@admins_view')->name('admins.view');

    Route::get('admins/status', 'AdminController@admins_status')->name('admins.status');

    Route::get('admins/index', 'AdminController@admins_index')->name('admins.list');

    Route::get('admins/delete', 'AdminController@admins_delete')->name('admins.delete');

    Route::post('admins/save', 'AdminController@admins_save')->name('admins.save');

    // Languages

    Route::get('/languages/index', 'LanguageController@languages_index')->name('languages.index'); 

    Route::get('/languages/download/', 'LanguageController@languages_download')->name('languages.download'); 

    Route::get('/languages/create', 'LanguageController@languages_create')->name('languages.create');
    
    Route::get('/languages/edit', 'LanguageController@languages_edit')->name('languages.edit');

    Route::get('/languages/status', 'LanguageController@languages_status_change')->name('languages.status');   

    Route::post('/languages/save', 'LanguageController@languages_save')->name('languages.save');

    Route::get('/languages/delete', 'LanguageController@languages_delete')->name('languages.delete');

    Route::get('/languages/set_default', 'LanguageController@languages_set_default')->name('languages.set_default');

    /** | * | * | * | * | * | * | REVENUES SECTION START | * | * | * | * | * | * | */

    Route::get('revenue/dashboard', 'AdminController@revenue_dashboard')->name('revenue.dashboard');

    // New User Payment details
    
    Route::get('user/payments' , 'AdminController@user_payments')->name('user.payments');

    Route::get('ajax/subscription/payments', 'AdminController@ajax_subscription_payments')->name('ajax.user-payments');

    // Video payments

    Route::get('user/video-payments' , 'AdminController@video_payments')->name('user.video-payments');

    Route::get('ajax/video/payments','AdminController@ajax_video_payments')->name('ajax.video-payments');


    /** | * | * | * | * | * | * | REVENUES SECTION END | * | * | * | * | * | * | */


    /** * * * * * * * * * * * * * Subscriptions section start * * * * * * * * * * * * * */

    // New subscriptions Methods begins

    Route::get('/subscriptions', 'AdminController@subscriptions_index')->name('subscriptions.index');

    Route::get('/subscription/save', 'AdminController@users_subscriptions_save')->name('users.subscriptions.save');

    Route::get('/subscriptions/create', 'AdminController@subscriptions_create')->name('subscriptions.create');

    Route::get('/subscriptions/edit', 'AdminController@subscriptions_edit')->name('subscriptions.edit');

    Route::post('/subscriptions/create', 'AdminController@subscriptions_save')->name('subscriptions.save');

    Route::get('/subscriptions/view', 'AdminController@subscriptions_view')->name('subscriptions.view');

    Route::get('/subscriptions/delete', 'AdminController@subscriptions_delete')->name('subscriptions.delete');

    Route::get('/subscriptions/status', 'AdminController@subscriptions_status_change')->name('subscriptions.status.change');

    Route::get('/subscriptions/popular/status', 'AdminController@subscriptions_popular_status')->name('subscriptions.popular.status');

    Route::get('/subscriptions/users', 'AdminController@subscriptions_users')->name('subscriptions.users');

    // New Subscriptions Methods ends

    /** * * * * * * * * * * * * * Subscriptions section end * * * * * * * * * * * * * */


    // Admin account Methods begins
    
    Route::get('/profile', 'AdminController@profile')->name('profile');

    Route::post('/profile/save', 'AdminController@profile_save')->name('save.profile');

    Route::post('/change/password', 'AdminController@change_password')->name('change.password');

    // Admin account Methods ends

    // Admin Settings Methods begins

    Route::get('settings' , 'AdminController@settings')->name('settings');
    
    Route::post('settings' , 'AdminController@settings_save')->name('settings.save');

    Route::post('common-settings_save' , 'AdminController@common_settings_save')->name('common-settings.save');

    Route::post('video-settings_save' , 'AdminController@video_settings_save')->name('video-settings.save');

    // Home page setting url

    Route::get('homepage/settings','AdminController@home_page_settings')->name('homepage.settings');

    // Admin Settings Methods ends


    // Admin Pages Methods begin

    Route::get('/pages', 'AdminController@pages_index')->name('pages.index');

    Route::get('/pages/create', 'AdminController@pages_create')->name('pages.create');

    Route::get('/pages/edit', 'AdminController@pages_edit')->name('pages.edit');

    Route::post('/pages/create', 'AdminController@pages_save')->name('pages.save');

    Route::get('/pages/view', 'AdminController@pages_view')->name('pages.view');

    Route::get('/pages/delete', 'AdminController@pages_delete')->name('pages.delete');
    
    // New Pages Methods ends


    // Sub Admins CRUD Operations

    Route::get('sub_admins/index', 'AdminController@sub_admins_index')->name('sub_admins.index');

    Route::get('sub_admins/create', 'AdminController@sub_admins_create')->name('sub_admins.create');

    Route::get('sub_admins/edit', 'AdminController@sub_admins_edit')->name('sub_admins.edit');

    Route::get('sub_admins/view', 'AdminController@sub_admins_view')->name('sub_admins.view');

    Route::get('sub_admins/status', 'AdminController@sub_admins_status')->name('sub_admins.status');

    Route::get('sub_admins/delete', 'AdminController@sub_admins_delete')->name('sub_admins.delete');

    Route::post('sub_admins/save', 'AdminController@sub_admins_save')->name('sub_admins.save');

    //ios control settings

    // Get ios control page
    Route::get('/ios-control','AdminController@ios_control')->name('ios_control');

    //Save the ios control status
    Route::post('/ios-control/save','AdminController@ios_control_save')->name('ios_control.save');



});

Route::group(['middleware' => ['SubAdminMiddleware', 'admin'], 'prefix' => 'subadmin', 'as' => 'subadmin.'], function () {

    Route::get('/', 'SubAdminController@dashboard')->name('dashboard');

    Route::get('subadmin/profile', 'SubAdminController@profile')->name('profile');

});

Route::get('/user/searchall' , 'ApplicationController@search_video')->name('search');

Route::any('/user/search' , 'ApplicationController@search_all')->name('search-all');

// Categories and single video 

Route::get('categories', 'UserController@all_categories')->name('user.categories');

Route::get('category/{id}', 'UserController@category_videos')->name('user.category');

Route::get('subcategory/{id}', 'UserController@sub_category_videos')->name('user.sub-category');

Route::get('genre/{id}', 'UserController@genre_videos')->name('user.genre');

Route::get('video/{id}', 'UserController@single_video')->name('user.single');


// Social Login

Route::post('/social', array('as' => 'SocialLogin' , 'uses' => 'SocialAuthController@redirect'));

Route::get('/callback/{provider}', 'SocialAuthController@callback');

Route::get('/user_session_language/{lang}', 'ApplicationController@set_session_language')->name('user_session_language');


Route::group(['middleware' => 'cors'], function(){

    Route::get('login', 'Auth\AuthController@showLoginForm')->name('user.login.form');

    Route::post('login', 'Auth\AuthController@login')->name('user.login.post');

    Route::get('logout', 'Auth\AuthController@logout')->name('user.logout');

    // Registration Routes...
    Route::get('register', 'Auth\AuthController@showRegistrationForm')->name('user.register.form');

    Route::post('register', 'Auth\AuthController@register')->name('user.register.post');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');

    Route::post('password/email', 'Auth\PasswordController@sendResetLinkEmail');

    Route::post('password/reset', 'Auth\PasswordController@reset');

    Route::get('profile', 'UserController@profile')->name('user.profile');

    Route::get('update/profile', 'UserController@update_profile')->name('user.update.profile');

    Route::post('update/profile', 'UserController@profile_save')->name('user.profile.save');

    Route::get('/profile/password', 'UserController@profile_change_password')->name('user.change.password');

    Route::post('/profile/password', 'UserController@profile_save_password')->name('user.profile.password');

    // Delete Account

    Route::get('/delete/account', 'UserController@delete_account')->name('user.delete.account');

    Route::post('/delete/account', 'UserController@delete_account_process')->name('user.delete.account.process');


    Route::get('history', 'UserController@history')->name('user.history');

    Route::get('deleteHistory', 'UserController@history_delete')->name('user.delete.history');

    Route::post('addHistory', 'UserController@history_add')->name('user.add.history');

    // Report Spam Video

    Route::post('markSpamVideo', 'UserController@save_report_video')->name('user.add.spam_video');

    Route::get('unMarkSpamVideo/{id}', 'UserController@remove_report_video')->name('user.remove.report_video');

    Route::get('spamVideos', 'UserController@spam_videos')->name('user.spam-videos');

    Route::get('pay-per-videos', 'UserController@payper_videos')->name('user.pay-per-videos');

    // Wishlist

    Route::post('addWishlist', 'UserController@wishlist_add')->name('user.add.wishlist');

    Route::get('deleteWishlist', 'UserController@wishlist_delete')->name('user.delete.wishlist');

    Route::get('wishlist', 'UserController@wishlist')->name('user.wishlist');

    // Comments

    Route::post('addComment', 'UserController@add_comment')->name('user.add.comment');

    Route::get('comments', 'UserController@comments')->name('user.comments');
    
    // Paypal Payment
   // Route::get('/paypal/{id}','PaypalController@pay')->name('paypal');

        // Paypal Payment
    Route::get('paypal/{id}/{user_id}/{coupon_code?}','PaypalController@pay')->name('paypal');

    Route::get('/user/payment/status','PaypalController@getPaymentStatus')->name('paypalstatus');

    Route::get('/videoPaypal/{id}/{user_id}/{coupon_code?}','PaypalController@videoSubscriptionPay')->name('videoPaypal');

    Route::get('/user/payment/video-status','PaypalController@getVideoPaymentStatus')->name('videoPaypalstatus');

    Route::get('/trending', 'UserController@trending')->name('user.trending');

});

Route::group(['prefix' => 'moderator' , 'as' => 'moderator.'], function(){


    Route::get('login', 'Auth\ModeratorAuthController@showLoginForm')->name('login');

    Route::post('login', 'Auth\ModeratorAuthController@login')->name('login.post');

    Route::get('logout', 'Auth\ModeratorAuthController@logout')->name('logout');

    // Registration Routes...
    Route::get('register', 'Auth\ModeratorAuthController@showRegistrationForm');

    Route::post('register', 'Auth\ModeratorAuthController@register');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\ModeratorPasswordController@showResetForm');

    Route::post('password/email', 'Auth\ModeratorPasswordController@sendResetLinkEmail');

    Route::post('password/reset', 'Auth\ModeratorPasswordController@reset');

    Route::get('/', 'NewModeratorController@dashboard')->name('dashboard');

    // Account

    Route::get('/profile', 'NewModeratorController@profile')->name('profile');

    Route::post('/profile/save', 'ModeratorController@profile_process')->name('save.profile');

    Route::post('/change/password', 'ModeratorController@change_password')->name('change.password');

    // Videos Management

    Route::get('/videos', 'NewModeratorController@admin_videos_index')->name('videos');

    Route::get('/videos/create', 'NewModeratorController@admin_videos_create')->name('videos.create');

    Route::get('/videos/edit/{id}', 'NewModeratorController@admin_videos_edit')->name('videos.edit');

    Route::post('/videos/save', 'NewModeratorController@admin_videos_save')->name('videos.save');

    Route::get('/videos/view', 'NewModeratorController@admin_videos_view')->name('videos.view');

    // PPV 

    Route::post('/videos/add_ppv', 'NewModeratorController@admin_videos_ppv_add')->name('videos.add_ppv');

    Route::get('/videos/remove_ppv/', 'NewModeratorController@admin_videos_ppv_remove')->name('videos.remove_ppv');

    // Revenues sections

    Route::get('revenues', 'NewModeratorController@revenues')->name('revenues');

    Route::get('revenues/ppv_payments' , 'NewModeratorController@revenues_ppv_payments')->name('revenues.ppv_payments');

    // Redeems

    Route::get('redeems/', 'NewModeratorController@redeems')->name('redeems');

    Route::get('redeems/send_request', 'NewModeratorController@redeems_request_send')->name('redeems.send_request');

    Route::get('redeems/cancel', 'NewModeratorController@redeems_request_cancel')->name('redeems.cancel_request');

});


Route::group(['prefix' => 'userApi', 'middleware' => 'cors' , 'as' => 'userapi.'], function(){

    Route::post('/register','UserApiController@register');
    
    Route::post('/login','UserApiController@login');

    Route::get('/userDetails','UserApiController@user_details');

    Route::post('/updateProfile', 'UserApiController@update_profile');

    Route::post('/forgotpassword', 'UserApiController@forgot_password');

    Route::post('/changePassword', 'UserApiController@change_password');

    Route::get('/tokenRenew', 'UserApiController@token_renew');

    Route::post('/deleteAccount', 'UserApiController@delete_account');

    Route::post('/settings', 'UserApiController@settings');


    // Categories And SubCategories

    Route::post('/categories' , 'UserApiController@get_categories');

    Route::post('/subCategories' , 'UserApiController@get_sub_categories');


    // Videos and home

    Route::post('/home' , 'UserApiController@home');
    
    Route::post('/common' , 'UserApiController@common');

    Route::post('/categoryVideos' , 'UserApiController@get_category_videos');

    Route::post('/subCategoryVideos' , 'UserApiController@get_sub_category_videos');

    Route::post('/singleVideo' , 'UserApiController@single_video');

    
  //  Route::post('/apiSearchVideo' , 'UserApiController@api_search_video')->name('api-search-video');

    Route::post('/searchVideo' , 'UserApiController@search_video')->name('search-video');

    Route::post('/test_search_video' , 'UserApiController@test_search_video');


    // Rating and Reviews

    Route::post('/userRating', 'UserApiController@user_rating'); // @TODO - Not used for future use

    // Wish List

    Route::post('/addWishlist', 'UserApiController@wishlist_add');

    Route::post('/getWishlist', 'UserApiController@wishlist_index');

    Route::post('/deleteWishlist', 'UserApiController@wishlist_delete');

    // History

    Route::post('/addHistory', 'UserApiController@history_add');

    Route::post('getHistory', 'UserApiController@history_index');

    Route::post('/deleteHistory', 'UserApiController@history_delete');

    Route::get('/clearHistory', 'UserApiController@clear_history');

    Route::post('/details', 'UserApiController@details');

    Route::post('/active-categories', 'UserApiController@getCategories');

    Route::post('/browse', 'UserApiController@browse');

    Route::post('/active-profiles', 'UserApiController@activeProfiles');

    Route::post('/add-profile', 'UserApiController@addProfile');

    Route::post('/view-sub-profile','UserApiController@view_sub_profile');

    Route::post('/edit-sub-profile','UserApiController@edit_sub_profile');

    Route::post('/delete-sub-profile', 'UserApiController@delete_sub_profile');

    Route::post('/active_plan', 'UserApiController@active_plan');

    Route::post('/subscription_index', 'UserApiController@subscription_index');

    Route::post('/zero_plan', 'UserApiController@zero_plan');

    Route::get('/site_settings' , 'UserApiController@site_settings');

    Route::post('/allPages', 'UserApiController@allPages');

    Route::get('/getPage/{id}', 'UserApiController@getPage');

    Route::get('check_social', 'UserApiController@check_social');

    Route::post('/get-subscription', 'UserApiController@last_subscription');

    Route::post('/genre-video', 'UserApiController@genre_video');

    Route::post('/genre-list', 'UserApiController@genre_list');

    Route::get('/searchall' , 'UserApiController@searchAll');

    Route::post('/notifications', 'UserApiController@notifications');

    Route::post('/red-notifications', 'UserApiController@red_notifications');

    Route::post('subscribed_plans', 'UserApiController@subscribed_plans');


    Route::post('stripe_payment_video', 'UserApiController@stripe_payment_video');

    Route::post('card_details', 'UserApiController@card_details');

    Route::post('payment_card_add', 'UserApiController@payment_card_add');

    Route::post('default_card', 'UserApiController@default_card');

    Route::post('delete_card', 'UserApiController@delete_card');

    Route::post('subscription_plans', 'UserApiController@subscription_plans');

    Route::post('subscribedPlans', 'UserApiController@subscribedPlans');

    Route::post('/stripe_payment', 'UserApiController@stripe_payment');
    
    Route::post('pay_now', 'UserApiController@pay_now');

    Route::post('/like_video', 'UserApiController@likeVideo');

    Route::post('/dis_like_video', 'UserApiController@disLikeVideo');

    Route::post('/add_spam', 'UserApiController@add_spam');

    Route::get('/spam-reasons', 'UserApiController@reasons');

    Route::post('remove_spam', 'UserApiController@remove_spam');

    Route::post('spam_videos', 'UserApiController@spam_videos');

    Route::post('stripe_ppv', 'UserApiController@stripe_ppv');

    Route::post('ppv_end', 'UserApiController@ppv_end');

    Route::post('paypal_ppv', 'UserApiController@paypal_ppv');

    Route::post('keyBasedDetails', 'UserApiController@keyBasedDetails');

    Route::post('plan_detail', 'UserApiController@plan_detail');

    Route::post('logout', 'UserApiController@logout');

    Route::post('check_token_valid', 'UserApiController@check_token_valid');

    Route::post('ppv_list', 'UserApiController@ppv_list');


    // Continue watching Video

    Route::post('continue/videos', 'UserApiController@continue_watching_videos');

    Route::any('save/watching/video', 'UserApiController@save_continue_watching_video');

    Route::post('/oncomplete/video', 'UserApiController@on_complete_video');

    // Enable / Disable Notifications

    Route::post('/email/notification', 'UserApiController@email_notification');


    Route::post('coupon/apply/vidoes','UserApiController@coupon_apply_videos');

    // Genres

    Route::post('genres/videos', 'UserApiController@genres_videos');

    Route::post('/apply/coupon/subscription', 'UserApiController@apply_coupon_subscription');

    Route::post('/apply/coupon/ppv', 'UserApiController@apply_coupon_ppv');

    Route::post('/cancel/subscription', 'UserApiController@autorenewal_cancel');

    Route::post('/autorenewal/enable', 'UserApiController@autorenewal_enable');

    Route::post('/pay_ppv', 'UserApiController@pay_ppv');

    // Cast Crews

    Route::post('cast_crews/videos', 'UserApiController@cast_crews_videos');

    // Kids videos
    Route::post('/kids/videos', 'UserApiController@kids_videos');

    // Downloads

    Route::post('/video/download', 'UserApiController@video_dowload_status');

    Route::post('downloaded/videos', 'UserApiController@downloaded_videos');

    // Notification count

    Route::post('notification/count', 'UserApiController@notifications_count');


    // NEW CONTROLLER API's 

    Route::post('test' , 'V4UserApiController@test');

    // HOME RELEATED API START

    Route::post('home_first_section' , 'V4UserApiController@home_first_section');

    Route::post('home_second_section' , 'V4UserApiController@home_second_section');

    Route::post('see_all', 'V4UserApiController@see_all_section');

    // HOME RELEATED API END


    // SECTIONS API START

    Route::post('new-releases' , 'V4UserApiController@section_new_releases')->name('section_new_releases');

    Route::post('trending' , 'V4UserApiController@section_trending')->name('section_trending');

    Route::post('continue_watching_videos' , 'V4UserApiController@section_continue_watching_videos')->name('section_continue_watching_videos');

    Route::post('suggestions' , 'V4UserApiController@section_suggestions')->name('section_suggestions');

    Route::post('originals' , 'V4UserApiController@section_originals')->name('section_originals');

    Route::post('sub_category_videos' , 'V4UserApiController@sub_category_videos')->name('sub_category_videos');
    
    Route::post('genre_videos' , 'V4UserApiController@genre_videos')->name('genre_videos');

    // SECTIONS API END

    // SINGLE VIDEO API START

    Route::post('videos/view' , 'V4UserApiController@admin_videos_view')->name('admin_videos_view');

    Route::post('videos/view/second' , 'V4UserApiController@admin_videos_view_second')->name('admin_videos_view_second');

    // SINGLE VIDEO API END

    Route::post('notification/settings', 'V4UserApiController@notification_settings'); // 22

    Route::post('continue_watching_videos/save', 'V4UserApiController@continue_watching_videos_save'); // 22

    Route::post('sub_profiles/delete', 'V4UserApiController@sub_profiles_delete'); // 22

    // WISHLIST

    Route::post('/wishlists', 'V4UserApiController@wishlist_index')->name('section_wishlists');

    Route::post('/wishlists/list', 'V4UserApiController@wishlist_index');

    Route::post('/wishlists/operations', 'V4UserApiController@wishlist_operations');


    // Spam Videos

    // Route::post('spam_videos', 'V4UserApiController@spam_videos'); // 22

    Route::post('spam_videos/list', 'V4UserApiController@spam_videos'); // 22

    Route::post('spam_videos/add', 'V4UserApiController@spam_videos_add'); // 22

    Route::post('spam_videos/remove', 'V4UserApiController@spam_videos_remove'); // 22


    // =================== branch v4.0-ios ================

    Route::post('/v4/register','V4UserApiController@register');

    Route::post('/v4/login','V4UserApiController@login');

    Route::post('/profile','V4UserApiController@profile');

    Route::post('/update_profile', 'V4UserApiController@profile_update'); // 2


    Route::post('/videos/like', 'V4UserApiController@videos_like');

    Route::post('/videos/dis_like', 'V4UserApiController@videos_dislike');


    // Route::post('/videos/history', 'V4UserApiController@history_index');

    Route::post('/history/list', 'V4UserApiController@history_index');

    Route::post('/videos/history/delete', 'V4UserApiController@history_delete');


    Route::get('pages/list' , 'ApplicationController@static_pages_api');

    Route::post('v4/categories/list' , 'V4UserApiController@categories_list');

    Route::post('v4/cast_crews/videos', 'V4UserApiController@cast_crews_videos');

    Route::post('v4/subscriptions_payment', 'V4UserApiController@subscriptions_payment');

    Route::post('v4/ppv_payment', 'V4UserApiController@ppv_payment');

});