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

Route::get('/upload-video' , 'UIController@upload_video');

Route::get('/video-notification' , 'UIController@video_notification');

/***********************  UI Routes *********************/

/*********************** TEST ROUTES *********************/

Route::get('/test' , 'SampleController@test');

Route::post('/test' , 'SampleController@get_image')->name('sample');

Route::get('/export' , 'SampleController@sample_export');

Route::get('/compress' , 'SampleController@compress_image_upload')->name('compress.image');

Route::get('/compress/i' , 'SampleController@compress_image_check');

Route::post('/compress/image' , 'SampleController@getImageThumbnail');

Route::get('/sendpush' , 'SampleController@send_push_notification');

/*********************** TEST ROUTES *********************/


// Route::get('/upload' , 'ApplicationController@subscriptions')->name('subscriptions.index');

Route::get('/generate/index' , 'ApplicationController@generate_index');


/***********************  UI Routes *********************/

Route::get('/upload-video' , 'AdminController@upload_video');

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


Route::group(['prefix' => 'admin'  , 'as' => 'admin.'], function() {

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

});

Route::get('/admin/check_role', 'AdminController@check_role');

Route::group(['middleware' => ['AdminMiddleware' , 'admin'] , 'prefix' => 'admin'  , 'as' => 'admin.'], function() {

    
    // Spam Videos
    
    Route::get('/spam-videos', 'AdminController@spam_videos')->name('spam-videos');

    Route::get('/spam-videos/user-reports/{id}', 'AdminController@spam_videos_user_reports')->name('spam-videos.user-reports');

    // Redeem Pay from Paypal

    Route::get('moderator/redeem-pay', 'RedeemPaymentController@redeem_pay')->name('moderator.redeem_pay');

    Route::get('moderator/redeem-pay-status', 'RedeemPaymentController@redeem_pay_status')->name('moderator.redeem_pay_status');
    // Videos

    Route::get('/videos', 'AdminController@videos')->name('videos');

    Route::get('/moderator/videos/{id}','AdminController@moderator_videos')->name('moderator.videos.list');

    // New Video Upload Code

    Route::get('/videos/create', 'AdminController@admin_videos_create')->name('videos.create');

    Route::get('/videos/edit/{id}', 'AdminController@admin_videos_edit')->name('videos.edit');

    Route::post('/videos/save', 'AdminController@admin_videos_save')->name('videos.save');

    Route::get('/view/video', 'AdminController@view_video')->name('view.video');

    Route::get('/gif/generation', 'AdminController@gif_generator')->name('gif_generator');

    Route::post('/save_video_payment/{id}', 'AdminController@save_video_payment')->name('save.video-payment');

    Route::get('/delete/video/{id}', 'AdminController@delete_video')->name('delete.video');

    Route::get('/video/approve/{id}', 'AdminController@approve_video')->name('video.approve');

    Route::get('/video/publish-video/{id}', 'AdminController@publish_video')->name('video.publish-video');

    Route::get('/video/decline/{id}', 'AdminController@decline_video')->name('video.decline');

    Route::post('/video/change/position', 'AdminController@video_position')->name('save.video.position');

    // Slider Videos

    Route::get('/slider/video/{id}', 'AdminController@slider_video')->name('slider.video');

    // Banner Videos

    Route::get('/banner/videos', 'AdminController@banner_videos')->name('banner.videos');

    Route::get('/add/banner/video', 'AdminController@add_banner_video')->name('add.banner.video');

    Route::get('/change/banner/video/{id}', 'AdminController@change_banner_video')->name('change.video');
    
    // User Payment details
    // Route::get('user/payments' , 'AdminController@user_payments')->name('user.payments');

    // Ajax User payments

    Route::get('ajax/subscription/payments', 'AdminController@ajax_subscription_payments')->name('ajax.user-payments');

    // Route::get('user/video-payments' , 'AdminController@video_payments')->name('user.video-payments');

    // Ajax Video payments
    Route::get('ajax/video/payments','AdminController@ajax_video_payments')->name('ajax.video-payments');

    // Route::get('revenue/system', 'AdminController@revenue_system')->name('revenue.system');

    Route::get('/remove_payper_view/{id}', 'AdminController@remove_payper_view')->name('remove_pay_per_view');

    // // Settings

    // Route::get('settings' , 'AdminController@settings')->name('settings');

    // Route::post('save_common_settings' , 'AdminController@save_common_settings')->name('save.common-settings');

    // Route::get('payment/settings' , 'AdminController@payment_settings')->name('payment.settings');

    // Route::get('theme/settings' , 'AdminController@theme_settings')->name('theme.settings');
    
    // Route::post('settings' , 'AdminController@settings_process')->name('save.settings');

    // Route::get('settings/email' , 'AdminController@email_settings')->name('email.settings');

    // Route::post('settings/email' , 'AdminController@email_settings_process')->name('email.settings.save');

    // Route::post('settings/mobile' , 'AdminController@mobile_settings_save')->name('mobile.settings.save');

    // Route::post('settings/other','AdminController@other_settings')->name('other.settings.save');

    // Route::get('help' , 'AdminController@help')->name('help');

    // // Home page setting url
    // Route::get('homepage/settings','AdminController@home_page_settings')->name('homepage.settings');


    // // Pages

    // Route::get('/pages', 'AdminController@pages')->name('pages.index');

    // Route::get('/pages/edit/{id}', 'AdminController@page_edit')->name('pages.edit');

    // Route::get('/pages/view', 'AdminController@page_view')->name('pages.view');

    // Route::get('/pages/create', 'AdminController@page_create')->name('pages.create');

    // Route::post('/pages/create', 'AdminController@page_save')->name('pages.save');

    // Route::get('/pages/delete/{id}', 'AdminController@page_delete')->name('pages.delete');
    
    // // Custom Push

    // Route::get('/custom/push', 'AdminController@custom_push')->name('push');

    // Route::post('/custom/push', 'AdminController@custom_push_process')->name('send.push');

    // subscriptions

    // Route::get('/subscriptions', 'AdminController@subscriptions')->name('subscriptions.index');

    // Route::get('/user_subscriptions/{id}', 'AdminController@user_subscriptions')->name('subscriptions.plans');

    // Route::get('/subscription/save/{s_id}/u_id/{u_id}', 'AdminController@user_subscription_save')->name('subscription.save');

    // Route::get('/subscriptions/create', 'AdminController@subscription_create')->name('subscriptions.create');

    // Route::get('/subscriptions/edit/{id}', 'AdminController@subscription_edit')->name('subscriptions.edit');

    // Route::post('/subscriptions/create', 'AdminController@subscription_save')->name('subscriptions.save');

    // Route::get('/subscriptions/delete/{id}', 'AdminController@subscription_delete')->name('subscriptions.delete');

    // Route::get('/subscriptions/view/{id}', 'AdminController@subscription_view')->name('subscriptions.view');

    // Route::get('/subscriptions/status/{id}', 'AdminController@subscription_status')->name('subscriptions.status');

    // Route::get('/subscriptions/popular/status/{id}', 'AdminController@subscription_popular_status')->name('subscriptions.popular.status');

    // Route::get('/subscriptions/users/{id}', 'AdminController@subscription_users')->name('subscriptions.users');

    // Coupons

    // Get the add coupon forms
    // Route::get('/coupons/add','AdminController@coupon_create')->name('add.coupons');

    // // Get the edit coupon forms
    // Route::get('/coupons/edit/{id}','AdminController@coupon_edit')->name('edit.coupons');

    // // Save the coupon details
    // Route::post('/coupons/save','AdminController@coupon_save')->name('save.coupon');

    // // Get the list of coupon details
    // Route::get('/coupons/list','AdminController@coupon_index')->name('coupon.list');

    // //Get the particular coupon details
    // Route::get('/coupons/view/{id}','AdminController@coupon_view')->name('coupon.view');

    // // Delete the coupon details
    // Route::get('/coupons/delete/{id}','AdminController@coupon_delete')->name('delete.coupon');

    // //Coupon approve and decline status
    // Route::get('/coupon/status','AdminController@coupon_status_change')->name('coupon.status');

    // //mail form
    // Route::get('/email/form','AdminController@create_mailcamp')->name('add.mailcamp');

    // Route::post('/email/form/action','AdminController@email_send_process')->name('email.success');


    // Email Templates,

    // Route::get('/create/template', 'AdminController@create_template')->name('create.template');

    // Route::get('/edit/template', 'AdminController@edit_template')->name('edit.template');

    // Route::post('/save/template', 'AdminController@save_template')->name('save.template');

    // Route::get('/view/template', 'AdminController@view_template')->name('view.template');

    // Route::get('/templates', 'AdminController@templates')->name('templates');

    // Cancel Subscription

    // Route::post('/user/subscription/pause', 'AdminController@user_subscription_pause')->name('cancel.subscription');

    // Route::get('/user/subscription/enable', 'AdminController@user_subscription_enable')->name('enable.subscription');

    // Cast & crews

    // Route::get('/cast-crews/add', 'AdminController@cast_crews_add')->name('cast_crews.add');

    // Route::get('/cast-crews/edit', 'AdminController@cast_crews_edit')->name('cast_crews.edit');

    // Route::post('/cast-crews/save', 'AdminController@cast_crews_save')->name('cast_crews.save');

    // Route::get('/cast-crews/delete', 'AdminController@cast_crews_delete')->name('cast_crews.delete');

    // Route::get('/cast-crews/index', 'AdminController@cast_crews_index')->name('cast_crews.index');

    // Route::get('/cast-crews/view', 'AdminController@cast_crews_view')->name('cast_crews.view');

    // Route::get('/cast_crews/status', 'AdminController@cast_crews_status')->name('cast_crews.status');


    // Languages
    Route::get('/languages/index', 'LanguageController@languages_index')->name('languages.index'); 

    Route::get('/languages/download/{folder}', 'LanguageController@languages_download')->name('languages.download'); 

    Route::get('/languages/create', 'LanguageController@languages_create')->name('languages.create');
    
    Route::get('/languages/edit/{id}', 'LanguageController@languages_edit')->name('languages.edit');

    Route::get('/languages/status/{id}', 'LanguageController@languages_status')->name('languages.status');   

    Route::post('/languages/save', 'LanguageController@languages_save')->name('languages.save');

    Route::get('/languages/delete/{id}', 'LanguageController@languages_delete')->name('languages.delete');

    Route::get('/languages/set_default_language/{name}', 'LanguageController@set_default_language')->name('languages.set_default_language');

    // Exports tables

    Route::get('/users/export/', 'AdminExportController@users_export')->name('users.export');

    Route::get('/moderators/export/', 'AdminExportController@moderators_export')->name('moderators.export');

    Route::get('/videos/export/', 'AdminExportController@videos_export')->name('videos.export');

    Route::get('/subscription/payment/export/', 'AdminExportController@subscription_export')->name('subscription.export');

    Route::get('/payperview/payment/export/', 'AdminExportController@payperview_export')->name('payperview.export');

    // Exports tables methods ends


    // Video compression status

    Route::get('/videos/compression/complete','AdminController@videos_compression_complete')->name('compress.status');

    // Banner Image upload
    Route::post('videos/banner/set', 'AdminController@videos_set_banner')->name('banner.set');

    Route::get('videos/banner/remove', 'AdminController@videos_remove_banner')->name('banner.remove');

    // Admins CRUD Operations

    Route::get('admins/create', 'AdminController@admins_create')->name('admins.create');

    Route::get('admins/edit', 'AdminController@admins_edit')->name('admins.edit');

    Route::get('admins/view', 'AdminController@admins_view')->name('admins.view');

    Route::get('admins/status', 'AdminController@admins_status')->name('admins.status');

    Route::get('admins/index', 'AdminController@admins_index')->name('admins.list');

    Route::get('admins/delete', 'AdminController@admins_delete')->name('admins.delete');

    Route::post('admins/save', 'AdminController@admins_save')->name('admins.save');

    /*=========== New Routes - branch v4.0-admin-coderevamp    ==============*/

    Route::get('/', 'NewAdminController@dashboard')->name('dashboard');

    Route::get('revenue/dashboard', 'NewAdminController@revenue_dashboard')->name('revenue.dashboard');

    //  New Admin User Methods starts

    Route::get('/users', 'NewAdminController@users_index')->name('users.index');

    Route::get('users/create', 'NewAdminController@users_create')->name('users.create');

    Route::get('users/edit', 'NewAdminController@users_edit')->name('users.edit');

    Route::post('users/create', 'NewAdminController@users_save')->name('users.save');

    Route::get('/users/view', 'NewAdminController@users_view')->name('users.view');

    Route::get('users/delete', 'NewAdminController@users_delete')->name('users.delete');

    Route::get('users/status/change', 'NewAdminController@users_status_change')->name('users.status.change');
    
    Route::get('/users/verify', 'NewAdminController@users_verify_status')->name('users.verify');

    Route::get('/users/upgrade', 'NewAdminController@users_upgrade')->name('users.upgrade');

    Route::any('users/upgrade-disable', 'NewAdminController@users_upgrade_disable')->name('users.upgrade.disable');

    Route::get('/users/subprofiles', 'NewAdminController@users_sub_profiles')->name('users.subprofiles');

    Route::get('/user/clear-login', 'NewAdminController@users_clear_login')->name('users.clear-login');

    //  New Admin Users History - admin

    Route::get('/users/history', 'NewAdminController@users_history')->name('users.history');

    Route::get('users/history/remove', 'NewAdminController@users_history_remove')->name('users.history.remove');
    
    //  New Admin Users Wishlist - admin

    Route::get('/users/wishlist', 'NewAdminController@users_wishlist')->name('users.wishlist');

    Route::get('/users/wishlist/remove', 'NewAdminController@users_wishlist_remove')->name('users.wishlist.remove');

    Route::post('/users/subscriptions/disable', 'NewAdminController@users_auto_subscription_disable')->name('subscriptions.cancel');

    Route::get('/users/subscriptions/enable', 'NewAdminController@users_auto_subscription_enable')->name('subscriptions.enable');

    //  New Admin User Methods Ends

    //  New Admin moderators Methods begins

    Route::get('/moderators', 'NewAdminController@moderators_index')->name('moderators.index');

    Route::get('/moderators/create', 'NewAdminController@moderators_create')->name('moderators.create');

    Route::get('/moderators/edit', 'NewAdminController@moderators_edit')->name('moderators.edit');

    Route::post('/moderators/create', 'NewAdminController@moderators_save')->name('moderators.save');

    Route::get('/moderators/delete', 'NewAdminController@moderators_delete')->name('moderators.delete');

    Route::get('/moderators/view', 'NewAdminController@moderators_view')->name('moderators.view');

    Route::get('/moderators/videos','NewAdminController@moderators_videos')->name('moderators.videos.index');

    Route::get('moderators/status/change', 'NewAdminController@moderators_status_change')->name('moderators.status.change');

    Route::get('moderators/redeems', 'NewAdminController@moderators_redeem_requests')->name('moderators.redeems');

    Route::any('/moderators/payout/invoice', 'NewAdminController@moderators_redeems_payout_invoice')->name('moderators.payout.invoice');

     Route::post('moderators/payout/direct', 'NewAdminController@moderators_redeems_payout_direct')->name('moderators.payout.direct');

    Route::any('/moderators/payout/response', 'NewAdminController@moderators_redeems_payout_response')->name('moderators.payout.response');
    
    // New Admin moderators Methods ends

    // New Admin Categories Methods begins

    Route::get('/categories', 'NewAdminController@categories_index')->name('categories.index');

    Route::get('/categories/create', 'NewAdminController@categories_create')->name('categories.create');

    Route::get('/categories/edit', 'NewAdminController@categories_edit')->name('categories.edit');

    Route::post('/categories/create', 'NewAdminController@categories_save')->name('categories.save');

    Route::get('/categories/delete', 'NewAdminController@categories_delete')->name('categories.delete');

    Route::get('/categories/view', 'NewAdminController@categories_view')->name('categories.view');

    Route::get('categories/status/change', 'NewAdminController@categories_status_change')->name('categories.status.change');

    // New Admin Categories Methods ends

    // New Admin Sub Categories Methods begins

    Route::get('/sub_categories', 'NewAdminController@sub_categories_index')->name('sub_categories.index');

    Route::get('/sub_categories/create', 'NewAdminController@sub_categories_create')->name('sub_categories.create');

    Route::get('/sub_categories/edit', 'NewAdminController@sub_categories_edit')->name('sub_categories.edit');

    Route::post('/sub_categories/create', 'NewAdminController@sub_categories_save')->name('sub_categories.save');

    Route::get('/sub_categories/delete', 'NewAdminController@sub_categories_delete')->name('sub_categories.delete');

    Route::get('/sub_categories/view', 'NewAdminController@sub_categories_view')->name('sub_categories.view');

    Route::get('sub_categories/status/change', 'NewAdminController@sub_categories_status_change')->name('sub_categories.status.change');

    // New Admin Sub Categories Methods ends

    // New Admin Genres Methods begins

    Route::get('/genres', 'NewAdminController@genres_index')->name('genres.index');

    Route::get('/genres/create', 'NewAdminController@genres_create')->name('genres.create');

    Route::get('/genres/edit', 'NewAdminController@genres_edit')->name('genres.edit');

    Route::post('/genres/create', 'NewAdminController@genres_save')->name('genres.save');

    Route::get('/genres/delete', 'NewAdminController@genres_delete')->name('genres.delete');

    Route::get('/genres/view', 'NewAdminController@genres_view')->name('genres.view');

    Route::get('genres/status/change', 'NewAdminController@genres_status_change')->name('genres.status.change');

    Route::post('genres/position/change', 'NewAdminController@genre_position_change')->name('genres.position.change');

    // New Admin Genres Methods ends
    
    // New Admin Cast & crews Methods begin   

    Route::get('/cast-crews/index', 'NewAdminController@cast_crews_index')->name('cast_crews.index');

    Route::get('/cast-crews/create', 'NewAdminController@cast_crews_create')->name('cast_crews.create');

    Route::get('/cast-crews/edit', 'NewAdminController@cast_crews_edit')->name('cast_crews.edit');

    Route::post('/cast-crews/save', 'NewAdminController@cast_crews_save')->name('cast_crews.save');
    
    Route::get('/cast-crews/view', 'NewAdminController@cast_crews_view')->name('cast_crews.view');

    Route::get('/cast-crews/delete', 'NewAdminController@cast_crews_delete')->name('cast_crews.delete');

    Route::get('/cast_crews/status/change', 'NewAdminController@cast_crews_status_change')->name('cast_crews.status.change');
    // New Admin Cast & crews Methods ends

    // New Admin Pages Methods begin

    Route::get('/pages', 'NewAdminController@pages_index')->name('pages.index');

    Route::get('/pages/create', 'NewAdminController@pages_create')->name('pages.create');

    Route::get('/pages/edit', 'NewAdminController@pages_edit')->name('pages.edit');

    Route::post('/pages/create', 'NewAdminController@pages_save')->name('pages.save');

    Route::get('/pages/view', 'NewAdminController@pages_view')->name('pages.view');

    Route::get('/pages/delete', 'NewAdminController@pages_delete')->name('pages.delete');
    
    // New Pages Methods ends

    // New subscriptions Methods begins
    Route::get('/subscriptions', 'NewAdminController@subscriptions_index')->name('subscriptions.index');

    Route::get('/user_subscriptions', 'NewAdminController@users_subscriptions')->name('subscriptions.plans');

    Route::get('/subscription/save', 'NewAdminController@users_subscriptions_save')->name('users.subscriptions.save');

    Route::get('/subscriptions/create', 'NewAdminController@subscriptions_create')->name('subscriptions.create');

    Route::get('/subscriptions/edit', 'NewAdminController@subscriptions_edit')->name('subscriptions.edit');

    Route::post('/subscriptions/create', 'NewAdminController@subscriptions_save')->name('subscriptions.save');

    Route::get('/subscriptions/view', 'NewAdminController@subscriptions_view')->name('subscriptions.view');

    Route::get('/subscriptions/delete', 'NewAdminController@subscriptions_delete')->name('subscriptions.delete');

    Route::get('/subscriptions/status', 'NewAdminController@subscriptions_status_change')->name('subscriptions.status.change');

    Route::get('/subscriptions/popular/status', 'NewAdminController@subscriptions_popular_status')->name('subscriptions.popular.status');

    Route::get('/subscriptions/users', 'NewAdminController@subscriptions_users')->name('subscriptions.users');

    // New Subscriptions Methods ends

    // New Coupons Methods begins

    Route::get('/coupons/create','NewAdminController@coupons_create')->name('coupons.create');

    Route::get('/coupons/edit','NewAdminController@coupons_edit')->name('coupons.edit');

    Route::post('/coupons/save','NewAdminController@coupons_save')->name('coupons.save');

    Route::get('/coupons/index','NewAdminController@coupons_index')->name('coupons.index');

    Route::get('/coupons/view','NewAdminController@coupons_view')->name('coupons.view');

    Route::get('/coupons/delete','NewAdminController@coupons_delete')->name('coupons.delete');

    Route::get('/coupons/status','NewAdminController@coupons_status_change')->name('coupons.status');

    // New Coupons Methods ends

    // New Admin account Methods begins
    
    Route::get('/profile', 'NewAdminController@profile')->name('profile');

    Route::post('/profile/save', 'NewAdminController@profile_save')->name('save.profile');

    Route::post('/change/password', 'NewAdminController@change_password')->name('change.password');

    // New Admin account Methods ends

    // New Admin Settings Methods begins

    Route::get('settings' , 'NewAdminController@settings')->name('settings');
    
    Route::post('settings' , 'NewAdminController@settings_save')->name('settings.save');

    Route::post('common-settings_save' , 'NewAdminController@common_settings_save')->name('common-settings.save');

    Route::post('video-settings_save' , 'NewAdminController@video_settings_save')->name('video-settings.save');

    // Home page setting url
    Route::get('homepage/settings','NewAdminController@home_page_settings')->name('homepage.settings');

    // New Admin Settings Methods ends

    // New User Payment details
    Route::get('user/payments' , 'NewAdminController@user_payments')->name('user.payments');

    Route::get('user/video-payments' , 'NewAdminController@video_payments')->name('user.video-payments');
    
    Route::get('help' , 'NewAdminController@help')->name('help');

    // Custom Push

    Route::get('/custom/push', 'NewAdminController@custom_push')->name('push');

    Route::post('/custom/push', 'NewAdminController@custom_push_save')->name('send.push');

    Route::get('/email/form','NewAdminController@mailcamp_create')->name('mailcamp.create');

    Route::post('/email/form/action','NewAdminController@email_send_process')->name('email.success');

    // New Admin email Methods ends

    // New Admin templates Methods begins

    Route::get('/templates', 'NewAdminController@templates_index')->name('templates.index');

    Route::get('templates/create/', 'NewAdminController@templates_create')->name('templates.create');
    Route::get('templates/edit/', 'NewAdminController@templates_edit')->name('templates.edit');

    Route::post('templates/save/', 'NewAdminController@templates_save')->name('templates.save');

    Route::get('templates/view/', 'NewAdminController@templates_view')->name('templates.view');

    // New Admin templates Methods ends


    // New Admins CRUD Operations
    Route::get('admins/index', 'NewAdminController@admins_index')->name('admins.index');

    Route::get('admins/create','NewAdminController@admins_create')->name('admins.create');

    Route::get('admins/edit', 'NewAdminController@admins_edit')->name('admins.edit');
   
    Route::post('admins/save', 'NewAdminController@admins_save')->name('admins.save');

    Route::get('admins/view', 'NewAdminController@admins_view')->name('admins.view');

    Route::get('admins/status','NewAdminController@admins_status')->name('admins.status');

    Route::get('admins/delete','NewAdminController@admins_delete')->name('admins.delete');

    // Sub Admins CRUD Operations

    Route::get('sub_admins/index', 'NewAdminController@sub_admins_index')->name('sub_admins.index');

    Route::get('sub_admins/create', 'NewAdminController@sub_admins_create')->name('sub_admins.create');

    Route::get('sub_admins/edit', 'NewAdminController@sub_admins_edit')->name('sub_admins.edit');

    Route::get('sub_admins/view', 'NewAdminController@sub_admins_view')->name('sub_admins.view');

    Route::get('sub_admins/status', 'NewAdminController@sub_admins_status')->name('sub_admins.status');

    Route::get('sub_admins/delete', 'NewAdminController@sub_admins_delete')->name('sub_admins.delete');

    Route::post('sub_admins/save', 'NewAdminController@sub_admins_save')->name('sub_admins.save');

});

Route::group(['middleware' => ['SubAdminMiddleware'], 'prefix' => 'subadmin' , 'as' => 'subadmin.'], function () {

    Route::get('login', 'Auth\AdminAuthController@showLoginForm')->name('login');

    Route::post('login', 'Auth\AdminAuthController@login')->name('login.post');

    Route::get('logout', 'Auth\AdminAuthController@logout')->name('logout');

    // Registration Routes...
    Route::get('register', 'Auth\AdminAuthController@showRegistrationForm');

    Route::post('register', 'Auth\AdminAuthController@register');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\SubAdminPasswordController@showResetForm');

    Route::post('password/email', 'Auth\SubAdminPasswordController@sendResetLinkEmail');

    Route::post('password/reset', 'Auth\SubAdminPasswordController@reset');

    Route::get('/', 'NewAdminController@dashboard')->name('dashboard');

    Route::get('subadmin/profile', 'NewAdminController@profile')->name('profile');

});

Route::group(['middleware' => ['SubAdminMiddleware']], function () {

    Route::get('users', 'NewAdminController@users_index')->name('users.index');

    Route::get('users/create', 'NewAdminController@users_create')->name('users.create');

    Route::get('users/edit', 'NewAdminController@users_edit')->name('users.edit');

    Route::post('users/create', 'NewAdminController@users_save')->name('users.save');

    Route::get('users/view', 'NewAdminController@users_view')->name('users.view');

    Route::get('users/delete', 'NewAdminController@users_delete')->name('users.delete');

    Route::get('users/status/change', 'NewAdminController@users_status_change')->name('users.status.change');
    
    Route::get('users/verify', 'NewAdminController@users_verify_status')->name('users.verify');

    Route::get('users/upgrade', 'NewAdminController@users_upgrade')->name('users.upgrade');

    Route::any('users/upgrade-disable', 'NewAdminController@users_upgrade_disable')->name('users.upgrade.disable');

    Route::get('users/subprofiles', 'NewAdminController@users_sub_profiles')->name('users.subprofiles');

    Route::get('user/clear-login', 'NewAdminController@users_clear_login')->name('users.clear-login');

});

Route::get('/embed', 'UserController@embed_video')->name('embed_video');

Route::get('/g_embed', 'UserController@genre_embed_video')->name('genre_embed_video');

Route::get('/', 'UserController@index')->name('user.dashboard');

Route::get('/single', 'UserController@single_video');

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


Route::group(['prefix' => 'moderator'], function(){

    Route::get('login', 'Auth\ModeratorAuthController@showLoginForm')->name('moderator.login');

    Route::post('login', 'Auth\ModeratorAuthController@login')->name('moderator.login.post');

    Route::get('logout', 'Auth\ModeratorAuthController@logout')->name('moderator.logout');

    // Registration Routes...
    Route::get('register', 'Auth\ModeratorAuthController@showRegistrationForm');

    Route::post('register', 'Auth\ModeratorAuthController@register');

    // Password Reset Routes...
    Route::get('password/reset/{token?}', 'Auth\ModeratorPasswordController@showResetForm');

    Route::post('password/email', 'Auth\ModeratorPasswordController@sendResetLinkEmail');

    Route::post('password/reset', 'Auth\ModeratorPasswordController@reset');

    Route::get('/', 'ModeratorController@dashboard')->name('moderator.dashboard');

    Route::post('/save_video_payment/{id}', 'ModeratorController@save_video_payment')->name('moderator.save.video-payment');


    // Route::get('user/video-payments' , 'ModeratorController@video_payments')->name('moderator.user.video-payments');

    Route::get('/remove_payper_view/{id}', 'ModeratorController@remove_payper_view')->name('moderator.remove_pay_per_view');

    Route::get('revenues', 'ModeratorController@revenues')->name('moderator.revenues');

        // Redeems

    Route::get('redeems/', 'ModeratorController@redeems')->name('moderator.redeems');

    Route::get('send/redeem', 'ModeratorController@send_redeem_request')->name('moderator.redeems.send.request');

    Route::get('redeem/request/cancel/{id?}', 'ModeratorController@redeem_request_cancel')->name('moderator.redeems.request.cancel');

    Route::get('/profile', 'ModeratorController@profile')->name('moderator.profile');

	Route::post('/profile/save', 'ModeratorController@profile_process')->name('moderator.save.profile');

	Route::post('/change/password', 'ModeratorController@change_password')->name('moderator.change.password');

    // Categories

    Route::get('/categories', 'ModeratorController@categories')->name('moderator.categories');

    Route::get('/add/category', 'ModeratorController@add_category')->name('moderator.add.category');

    Route::get('/edit/category/{id}', 'ModeratorController@edit_category')->name('moderator.edit.category');

    Route::post('/add/category', 'ModeratorController@add_category_process')->name('moderator.save.category');

    Route::get('/delete/category', 'ModeratorController@delete_category')->name('moderator.delete.category');

    Route::get('/view/category/{id}', 'ModeratorController@view_category')->name('moderator.view.category');

    // Admin Sub Categories

    Route::get('/subCategories/{category}', 'ModeratorController@sub_categories')->name('moderator.sub_categories');

    Route::get('/add/subCategory/{category}', 'ModeratorController@add_sub_category')->name('moderator.add.sub_category');

    Route::get('/edit/subCategory/{category_id}/{sub_category_id}', 'ModeratorController@edit_sub_category')->name('moderator.edit.sub_category');

    Route::post('/add/subCategory', 'ModeratorController@add_sub_category_process')->name('moderator.save.sub_category');

    Route::get('/delete/subCategory/{id}', 'ModeratorController@delete_sub_category')->name('moderator.delete.sub_category');

    // Genre

    Route::post('/save/genre' , 'ModeratorController@save_genre')->name('moderator.save.genre');

    Route::get('/delete/genre/{id}', 'ModeratorController@delete_genre')->name('moderator.delete.genre');


      // New Video Upload Code

    Route::get('/videos/create', 'ModeratorController@admin_videos_create')->name('moderator.videos.create');

    Route::get('/videos/edit/{id}', 'ModeratorController@admin_videos_edit')->name('moderator.videos.edit');

    Route::post('/videos/save', 'ModeratorController@admin_videos_save')->name('moderator.videos.save');
    
    // Videos

    Route::get('/videos', 'ModeratorController@videos')->name('moderator.videos');

    Route::get('/add/video', 'ModeratorController@add_video')->name('moderator.add.video');

    Route::get('/edit/video/{id}', 'ModeratorController@edit_video')->name('moderator.edit.video');

    Route::post('/edit/video', 'ModeratorController@edit_video_process')->name('moderator.save.edit.video');

    Route::get('/view/video', 'ModeratorController@view_video')->name('moderator.view.video');

    Route::post('/add/video', 'ModeratorController@add_video_process')->name('moderator.save.video');

    Route::get('/delete/video', 'ModeratorController@delete_video')->name('moderator.delete.video');

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

    Route::post('test' , 'NewUserApiController@test');

    // HOME RELEATED API START

    Route::post('home_first_section' , 'NewUserApiController@home_first_section');

    Route::post('home_second_section' , 'NewUserApiController@home_second_section');

    Route::post('see_all', 'NewUserApiController@see_all_section');

    // HOME RELEATED API END


    // SECTIONS API START

    Route::post('new-releases' , 'NewUserApiController@section_new_releases')->name('section_new_releases');

    Route::post('trending' , 'NewUserApiController@section_trending')->name('section_trending');

    Route::post('continue_watching_videos' , 'NewUserApiController@section_continue_watching_videos')->name('section_continue_watching_videos');

    Route::post('suggestions' , 'NewUserApiController@section_suggestions')->name('section_suggestions');

    Route::post('originals' , 'NewUserApiController@section_originals')->name('section_originals');

    Route::post('sub_category_videos' , 'NewUserApiController@sub_category_videos')->name('sub_category_videos');
    
    Route::post('genre_videos' , 'NewUserApiController@genre_videos')->name('genre_videos');

    // SECTIONS API END

    // SINGLE VIDEO API START

    Route::post('videos/view' , 'NewUserApiController@admin_videos_view')->name('admin_videos_view');

    Route::post('videos/view/second' , 'NewUserApiController@admin_videos_view_second')->name('admin_videos_view_second');

    // SINGLE VIDEO API END

    Route::post('notification/settings', 'NewUserApiController@notification_settings'); // 22


    Route::post('continue_watching_videos/save', 'NewUserApiController@continue_watching_videos_save'); // 22

    Route::post('sub_profiles/delete', 'NewUserApiController@sub_profiles_delete'); // 22

    // WISHLIST

    Route::post('/wishlists', 'NewUserApiController@wishlist_index')->name('section_wishlists');

    Route::post('/wishlists/operations', 'NewUserApiController@wishlist_operations');


    // Spam Videos

    Route::post('spam_videos', 'NewUserApiController@spam_videos'); // 22

    Route::post('spam_videos/add', 'NewUserApiController@spam_videos_add'); // 22

    Route::post('spam_videos/remove', 'NewUserApiController@spam_videos_remove'); // 22

});