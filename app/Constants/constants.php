<?php

//Demo User

if(!defined('DEMO_USER')) define('DEMO_USER', 'user@streamview.com');

if(!defined('NO')) define('NO', 0);

if(!defined('YES')) define('YES', 1);

if(!defined('OFF')) define('OFF', 0);

if(!defined('ON')) define('ON', 1);


if(!defined('APPROVED')) define('APPROVED', 1);
if(!defined('DECLINED')) define('DECLINED', 0);


// Login By types

if(!defined('MANUAL')) define('MANUAL', "manual");


// Approve /Decline cast

if(!defined('CAST_APPROVED')) define('CAST_APPROVED', 1);

if(!defined('CAST_DECLINED')) define('CAST_DECLINED', 0);

// REDEEMS

if(!defined('REDEEM_OPTION_ENABLED')) define('REDEEM_OPTION_ENABLED', 1);

if(!defined('REDEEM_OPTION_DISABLED')) define('REDEEM_OPTION_DISABLED', 0);

// Redeeem Request Status

if(!defined('REDEEM_REQUEST_SENT')) define('REDEEM_REQUEST_SENT', 0);
if(!defined('REDEEM_REQUEST_PROCESSING')) define('REDEEM_REQUEST_PROCESSING', 1);
if(!defined('REDEEM_REQUEST_PAID')) define('REDEEM_REQUEST_PAID', 2);
if(!defined('REDEEM_REQUEST_CANCEL')) define('REDEEM_REQUEST_CANCEL', 3);

if(!defined('REPORT_VIDEO_KEY')) define('REPORT_VIDEO_KEY', 'REPORT_VIDEO');
if (!defined('IMAGE_RESOLUTIONS_KEY')) define('IMAGE_RESOLUTIONS_KEY', 'IMAGE_RESOLUTIONS');
if (!defined('VIDEO_RESOLUTIONS_KEY')) define('VIDEO_RESOLUTIONS_KEY', 'VIDEO_RESOLUTIONS');
if(!defined('DELETE_STATUS')) define('DELETE_STATUS', -1);

// User Type
if(!defined('NORMAL_USER')) define('NORMAL_USER', 1);
if(!defined('PAID_USER')) define('PAID_USER', 2);
if(!defined('BOTH_USERS')) define('BOTH_USERS', 3);

if (!defined('PAID_STATUS')) define('PAID_STATUS', 1);

// Subscription Type
if(!defined('ONE_TIME_PAYMENT')) define('ONE_TIME_PAYMENT', 1);
if(!defined('RECURRING_PAYMENT')) define('RECURRING_PAYMENT', 2);

// FFMPEG Status

if(!defined('FFMPEG_INSTALLED')) define('FFMPEG_INSTALLED', 1);
if(!defined('FFMPEG_NOT_INSTALLED')) define('FFMPEG_NOT_INSTALLED', 0);


if(!defined('BANNER_VIDEO')) define('BANNER_VIDEO', 1);
if(!defined('BANNER_VIDEO_REMOVED')) define('BANNER_VIDEO_REMOVED', 0);

 
// REQUEST STATE

if(!defined('REQUEST_STEP_1')) define('REQUEST_STEP_1', 1);
if(!defined('REQUEST_STEP_2')) define('REQUEST_STEP_2', 2);
if(!defined('REQUEST_STEP_3')) define('REQUEST_STEP_3', 3);
if(!defined('REQUEST_STEP_FINAL')) define('REQUEST_STEP_FINAL', 4);

// publish status
if(!defined('PUBLISH_NOW')) define('PUBLISH_NOW', 1);
if(!defined('PUBLISH_LATER')) define('PUBLISH_LATER', 2);

// PPV Status

if(!defined('PPV_ENABLED')) define('PPV_ENABLED', 1);

if(!defined('PPV_DISABLED')) define('PPV_DISABLED', 0);


// Compress Status

if (!defined('COMPRESS_ENABLED')) define('COMPRESS_ENABLED', 1);

if (!defined('COMPRESS_DISABLED')) define('COMPRESS_DISABLED', 0);

// Published Status

if (!defined('VIDEO_PUBLISHED')) define('VIDEO_PUBLISHED', 1);

if (!defined('VIDEO_NOT_YET_PUBLISHED')) define('VIDEO_NOT_YET_PUBLISHED', 0);

// Video Status

if (!defined('VIDEO_APPROVED')) define('VIDEO_APPROVED', 1);

if (!defined('VIDEO_DECLINED')) define('VIDEO_DECLINED', 0);


// sub category Status

if (!defined('CATEGORY_APPROVED')) define('CATEGORY_APPROVED', 1);

if (!defined('CATEGORY_DECLINED')) define('CATEGORY_DECLINED', 0);

// sub category Status

if (!defined('SUB_CATEGORY_APPROVED')) define('SUB_CATEGORY_APPROVED', 1);

if (!defined('SUB_CATEGORY_DECLINED')) define('SUB_CATEGORY_DECLINED', 0);

// Overall Compress Status

if (!defined('COMPRESS_NOT_YET_STARTED')) define('COMPRESS_NOT_YET_STARTED', 0);

if (!defined('COMPRESS_TRAILER_VIDEO_PROCESSING')) define('COMPRESS_TRAILER_VIDEO_PROCESSING', 1);

if (!defined('COMPRESS_TRAILER_VIDEO_COMPLETED')) define('COMPRESS_TRAILER_VIDEO_COMPLETED', 2);

if (!defined('COMPRESS_MAIN_VIDEO_PROCESSING')) define('COMPRESS_MAIN_VIDEO_PROCESSING', 3);

if (!defined('COMPRESS_MAIN_VIDEO_COMPLETED')) define('COMPRESS_MAIN_VIDEO_COMPLETED', 4);

if (!defined('OVERALL_COMPRESS_COMPLETED')) define('OVERALL_COMPRESS_COMPLETED', 5);

// Compression Not happened

if(!defined('COMPRESSION_NOT_HAPPEN')) define('COMPRESSION_NOT_HAPPEN', 6);

// Main & Trailer Compress Status

if (!defined('COMPRESS_SENT_QUEUE')) define('COMPRESS_SENT_QUEUE', 1);

if (!defined('COMPRESS_PROCESSING')) define('COMPRESS_PROCESSING', 2);

if (!defined('COMPRESS_COMPLETED')) define('COMPRESS_COMPLETED', 3);

if (!defined('DO_COMPRESS')) define('DO_COMPRESS', 1);

if (!defined('DO_NOT_COMPRESS')) define('DO_NOT_COMPRESS', 0);



if(!defined('USER')) define('USER', 0);

if(!defined('Moderator')) define('Moderator',1);

if(!defined('NONE')) define('NONE', 0);

if(!defined('MAIN_VIDEO')) define('MAIN_VIDEO', 1);
if(!defined('TRAILER_VIDEO')) define('TRAILER_VIDEO', 2);


if(!defined('DEFAULT_TRUE')) define('DEFAULT_TRUE', 1);
if(!defined('DEFAULT_FALSE')) define('DEFAULT_FALSE', 0);

if(!defined('ADMIN')) define('ADMIN', 'admin');
if(!defined('SUBADMIN')) define('SUBADMIN', 'sub_admin');
if(!defined('MODERATOR')) define('MODERATOR', 'moderator');

if(!defined('VIDEO_TYPE_UPLOAD')) define('VIDEO_TYPE_UPLOAD', 1);
if(!defined('VIDEO_TYPE_YOUTUBE')) define('VIDEO_TYPE_YOUTUBE', 2);
if(!defined('VIDEO_TYPE_OTHER')) define('VIDEO_TYPE_OTHER', 3);


if(!defined('VIDEO_UPLOAD_TYPE_s3')) define('VIDEO_UPLOAD_TYPE_s3', 1);
if(!defined('VIDEO_UPLOAD_TYPE_DIRECT')) define('VIDEO_UPLOAD_TYPE_DIRECT', 2);

if(!defined('NO_INSTALL')) define('NO_INSTALL' , 0);

if(!defined('SYSTEM_CHECK')) define('SYSTEM_CHECK' , 1);

if(!defined('INSTALL_COMPLETE')) define('INSTALL_COMPLETE' , 2);



// Payment Constants
if(!defined('COD')) define('COD',   'cod');
if(!defined('PAYPAL')) define('PAYPAL', 'paypal');
if(!defined('CARD')) define('CARD',  'card');


if(!defined('RATINGS')) define('RATINGS', '0,1,2,3,4,5');

if(!defined('DEVICE_ANDROID')) define('DEVICE_ANDROID', 'android');
if(!defined('DEVICE_IOS')) define('DEVICE_IOS', 'ios');
if(!defined('DEVICE_WEB')) define('DEVICE_WEB', 'web');

if(!defined('USERS')) define('USERS',0);
if(!defined('MODERATORS')) define('MODERATORS',1);
if(!defined('CUSTOM_USERS')) define('CUSTOM_USERS',2);

if(!defined('ALL_USER')) define('ALL_USER', 'ALL_USER');
if(!defined('NORMAL_USERS')) define('NORMAL_USERS', 'NORMAL_USERS');
if(!defined('PAID_USERS')) define('PAID_USERS','PAID_USERS' );
if(!defined('SELECT_USERS')) define('SELECT_USERS', 'SELECT_USERS');

if(!defined('ALL_MODERATOR')) define('ALL_MODERATOR', 'ALL_MODERATOR');

if(!defined('SELECT_MODERATOR')) define('SELECT_MODERATOR','SELECT_MODERATOR');


if(!defined('WISHLIST_EMPTY')) define('WISHLIST_EMPTY' , 0);
if(!defined('WISHLIST_ADDED')) define('WISHLIST_ADDED' , 1);
if(!defined('WISHLIST_REMOVED')) define('WISHLIST_REMOVED' , 2);

if(!defined('RECENTLY_ADDED')) define('RECENTLY_ADDED' , 'recent');
if(!defined('TRENDING')) define('TRENDING' , 'trending');
if(!defined('KIDS')) define('KIDS' , 'kids');
if(!defined('SUGGESTIONS')) define('SUGGESTIONS' , 'suggestion');
if(!defined('WISHLIST')) define('WISHLIST' , 'wishlist');
if(!defined('WATCHLIST')) define('WATCHLIST' , 'watchlist');
if(!defined('BANNER')) define('BANNER' , 'banner');
if(!defined('CONTINUE_WATCHING')) define('CONTINUE_WATCHING', 'continue');
if(!defined('BANNER_VIDEO')) define('BANNER_VIDEO', 1);
if(!defined('BANNER_VIDEO_REMOVED')) define('BANNER_VIDEO_REMOVED', 0);


// Template Types

if(!defined('USER_WELCOME')) define('USER_WELCOME', 'user_welcome');
if(!defined('ADMIN_USER_WELCOME')) define('ADMIN_USER_WELCOME', 'admin_user_welcome');
if(!defined('FORGOT_PASSWORD')) define('FORGOT_PASSWORD', 'forgot_password');
if(!defined('MODERATOR_WELCOME')) define('MODERATOR_WELCOME', 'moderator_welcome');
if(!defined('PAYMENT_EXPIRED')) define('PAYMENT_EXPIRED', 'payment_expired');
if(!defined('PAYMENT_GOING_TO_EXPIRY')) define('PAYMENT_GOING_TO_EXPIRY', 'payment_going_to_expiry');
if(!defined('NEW_VIDEO')) define('NEW_VIDEO', 'new_video');
if(!defined('EDIT_VIDEO')) define('EDIT_VIDEO', 'edit_video');
if(!defined('AUTOMATIC_RENEWAL')) define('AUTOMATIC_RENEWAL', 'automatic_renewal');
if(!defined('MODERATOR_UPDATE_MAIL')) define('MODERATOR_UPDATE_MAIL', 'moderator_update_mail');

if(!defined('WEB')) define('WEB' , 1);

//coupon

// Coupons status
if(!defined('COUPON_ACTIVE')) define('COUPON_ACTIVE',1);
if(!defined('COUPON_INACTIVE')) define('COUPON_INACTIVE', 0);


// Coupons applied status
if(!defined('COUPON_APPLIED')) define('COUPON_APPLIED',1);
if(!defined('COUPON_NOT_APPLIED')) define('COUPON_NOT_APPLIED', 0);

if(!defined('PERCENTAGE')) define('PERCENTAGE',0);

if(!defined('ABSOULTE')) define('ABSOULTE',1);

if(!defined('FREE_PLAN')) define('FREE_PLAN','free plan');

// AUTORENEWAL STATUS

if(!defined('AUTORENEWAL_ENABLED')) define('AUTORENEWAL_ENABLED',0);

if(!defined('AUTORENEWAL_CANCELLED')) define('AUTORENEWAL_CANCELLED',1);

// Subscribed user status

if(!defined('SUBSCRIBED_USER')) define('SUBSCRIBED_USER', 1);

if(!defined('NON_SUBSCRIBED_USER')) define('NON_SUBSCRIBED_USER', 0);


// watched status

if(!defined('NOT_YET_WATCHED')) define('NOT_YET_WATCHED', 0);

if(!defined('WATCHED')) define('WATCHED', 1);

// USER STATUS FOR EMAIL & Login


if(!defined('USER_APPROVED')) define('USER_APPROVED', 1);

if(!defined('USER_DECLINED')) define('USER_DECLINED', 0);

if(!defined('USER_EMAIL_VERIFIED')) define('USER_EMAIL_VERIFIED', 1);

if(!defined('USER_EMAIL_NOT_VERIFIED')) define('USER_EMAIL_NOT_VERIFIED', 0);

// PUSH NOTIFICATION

if(!defined('PUSH_TO_ALL')) define('PUSH_TO_ALL', 'all');

if(!defined('PUSH_TO_ANDROID')) define('PUSH_TO_ANDROID', 'android');

if(!defined('PUSH_TO_IOS')) define('PUSH_TO_IOS', 'ios');


// Admin status

if(!defined('ADMIN_APPROVE_STATUS')) define('ADMIN_APPROVE_STATUS', 1);

if(!defined('ADMiN_DECLINE_STATUS')) define('ADMiN_DECLINE_STATUS', 0);



// Kids section status

if(!defined('KIDS_SECTION_YES')) define('KIDS_SECTION_YES', 1);

if(!defined('KIDS_SECTION_NO')) define('KIDS_SECTION_NO', 0);


// Signout from all devices

if(!defined('SIGNOUT_ALL_DEVICE_ENABLE')) define('SIGNOUT_ALL_DEVICE_ENABLE', 1);
if(!defined('SIGNOUT_ALL_DEVICE_DISABLE')) define('SIGNOUT_ALL_DEVICE_DISABLE', 0);


// Download status

if(!defined('DOWNLOAD_INITIATE')) define('DOWNLOAD_INITIATE', 'download_initiate');

if(!defined('DOWNLOAD_PROGRESSING')) define('DOWNLOAD_PROGRESSING', 'download_progressing');

if(!defined('DOWNLOAD_PAUSE')) define('DOWNLOAD_PAUSE', 'download_pause');

if(!defined('DOWNLOAD_COMPLETE')) define('DOWNLOAD_COMPLETE', 'download_complete');

if(!defined('DOWNLOAD_CANCEL')) define('DOWNLOAD_CANCEL', 'download_cancel');


if(!defined('DOWNLOAD_INITIATE_STAUTS')) define('DOWNLOAD_INITIATE_STAUTS', 1);

if(!defined('DOWNLOAD_PROGRESSING_STAUTS')) define('DOWNLOAD_PROGRESSING_STAUTS', 2);

if(!defined('DOWNLOAD_PAUSE_STAUTS')) define('DOWNLOAD_PAUSE_STAUTS', 3);

if(!defined('DOWNLOAD_COMPLETE_STAUTS')) define('DOWNLOAD_COMPLETE_STAUTS', 4);

if(!defined('DOWNLOAD_CANCEL_STAUTS')) define('DOWNLOAD_CANCEL_STAUTS', 5);

if(!defined('DOWNLOAD_DELETE_STAUTS')) define('DOWNLOAD_DELETE_STAUTS', 6);

// DOWNLOAD BUTTON STATUS

if(!defined('DOWNLOAD_BTN_DONT_SHOW')) define('DOWNLOAD_BTN_DONT_SHOW', 0);

if(!defined('DOWNLOAD_BTN_SHOW')) define('DOWNLOAD_BTN_SHOW', 1);

if(!defined('DOWNLOAD_BTN_ONPROGRESS')) define('DOWNLOAD_BTN_ONPROGRESS', 2);

if(!defined('DOWNLOAD_BTN_COMPLETED')) define('DOWNLOAD_BTN_COMPLETED', 3);

if(!defined('DOWNLOAD_BTN_USER_NEEDS_TO_SUBSCRIBE')) define('DOWNLOAD_BTN_USER_NEEDS_TO_SUBSCRIBE', 4);

if(!defined('DOWNLOAD_BTN_USER_NEEDS_PAY_FOR_VIDEO')) define('DOWNLOAD_BTN_USER_NEEDS_PAY_FOR_VIDEO', 5);


if(!defined('DOWNLOAD_ON')) define('DOWNLOAD_ON', 1);

if(!defined('DOWNLOAD_OFF')) define('DOWNLOAD_OFF', 0);



// Download Status

if(!defined('VIDEO_DOWNLOADED')) define('VIDEO_DOWNLOADED', 1);

if(!defined('VIDEO_NOT_DOWNLOADED')) define('VIDEO_NOT_DOWNLOADED', 0);


// These constants are used identify the home page api types http://prntscr.com/mahza1

if(!defined('API_PAGE_TYPE_HOME')) define('API_PAGE_TYPE_HOME', 'HOME');

if(!defined('API_PAGE_TYPE_SERIES')) define('API_PAGE_TYPE_SERIES', "SERIES");

if(!defined('API_PAGE_TYPE_FLIMS')) define('API_PAGE_TYPE_FLIMS', "FLIMS");

if(!defined('API_PAGE_TYPE_KIDS')) define('API_PAGE_TYPE_KIDS', "KIDS");

if(!defined('API_PAGE_TYPE_CATEGORY')) define('API_PAGE_TYPE_CATEGORY', "CATEGORY");

if(!defined('API_PAGE_TYPE_SUB_CATEGORY')) define('API_PAGE_TYPE_SUB_CATEGORY', "SUB_CATEGORY");

if(!defined('API_PAGE_TYPE_GENRE')) define('API_PAGE_TYPE_GENRE', "GENRE");


// URL TYPE FOR SEE ALL URLS

if(!defined('URL_TYPE_WISHLIST')) define('URL_TYPE_WISHLIST', 'URL_TYPE_WISHLIST');

if(!defined('URL_TYPE_NEW_RELEASE')) define('URL_TYPE_NEW_RELEASE', 'URL_TYPE_NEW_RELEASE');

if(!defined('URL_TYPE_CONTINUE_WATCHING')) define('URL_TYPE_CONTINUE_WATCHING', 'URL_TYPE_CONTINUE_WATCHING');

if(!defined('URL_TYPE_TRENDING')) define('URL_TYPE_TRENDING', 'URL_TYPE_TRENDING');

if(!defined('URL_TYPE_SUGGESTION')) define('URL_TYPE_SUGGESTION', 'URL_TYPE_SUGGESTION');

if(!defined('URL_TYPE_ORIGINAL')) define('URL_TYPE_ORIGINAL', 'URL_TYPE_ORIGINAL');

if(!defined('URL_TYPE_CATEGORY')) define('URL_TYPE_CATEGORY', 'URL_TYPE_CATEGORY');

if(!defined('URL_TYPE_SUB_CATEGORY')) define('URL_TYPE_SUB_CATEGORY', 'URL_TYPE_SUB_CATEGORY');

if(!defined('URL_TYPE_GENRE')) define('URL_TYPE_GENRE', 'URL_TYPE_GENRE');

if(!defined('URL_TYPE_CAST_CREW')) define('URL_TYPE_CAST_CREW', 'URL_TYPE_CAST_CREW');

// PPV 
if(!defined('PPV_PAGE_TYPE_NONE')) define('PPV_PAGE_TYPE_NONE', 0);

if(!defined('PPV_PAGE_TYPE_INVOICE')) define('PPV_PAGE_TYPE_INVOICE', 1);

if(!defined('PPV_PAGE_TYPE_CHOOSE_SUB_OR_PPV')) define('PPV_PAGE_TYPE_CHOOSE_SUB_OR_PPV', 2);

if(!defined('SUBSCRIPTION')) define('SUBSCRIPTION', 'SUBSCRIPTION');

if(!defined('PPV')) define('PPV', 'PPV');


// Notification settings

if(!defined('EMAIL_NOTIFICATION')) define('EMAIL_NOTIFICATION', 'email');

if(!defined('PUSH_NOTIFICATION')) define('PUSH_NOTIFICATION', 'push');
	

if(!defined('DEFAULT_SUB_PROFILE')) define('DEFAULT_SUB_PROFILE', 1);


// new admin constant for MANUAL to be replaced by LOGIN_BY_MANUAL
if(!defined('LOGIN_BY_MANUAL')) define('LOGIN_BY_MANUAL', "manual");

if(!defined('ORIGINAL_VIDEO_YES')) define('ORIGINAL_VIDEO_YES', 1);
if(!defined('ORIGINAL_VIDEO_NO')) define('ORIGINAL_VIDEO_NO', 0);

if(!defined('DEFAULT_SUB_ACCOUNTS')) define('DEFAULT_SUB_ACCOUNTS', 1 );

if(!defined('USER_ACTIVATED')) define('USER_ACTIVATED', 1);
if(!defined('USER_DEACTIVATED')) define('USER_DEACTIVATED', 0);

if(!defined('MODERATOR_ACTIVATED')) define('MODERATOR_ACTIVATED', 1);
if(!defined('MODERATOR_DEACTIVATED')) define('MODERATOR_DEACTIVATED', 0);

if (!defined('GENRE_APPROVED')) define('GENRE_APPROVED', 1);
if (!defined('GENRE_DECLINED')) define('GENRE_DECLINED', 0);

if (!defined('DISABLED_DOWNLOAD')) define('DISABLED_DOWNLOAD', 0);

if (!defined('ENABLED_DOWNLOAD')) define('ENABLED_DOWNLOAD', 1);
