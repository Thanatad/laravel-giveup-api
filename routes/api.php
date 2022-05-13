<?php

use Illuminate\Support\Facades\Route;

Route::post('oauth/token', 'Api\Auth\AuthController@index');

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return Artisan::output();
});

Route::get('cron/donate-notification', 'Api\CronDonateNotificationController@index');

Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
], function () {
    Route::get('auth/guest', 'Api\Auth\GAuthController@guest');
    Route::post('auth/general', 'Api\Auth\GAuthController@general');
    Route::post('auth/otpsend', 'Api\Auth\PhoneAuthController@otpSend');
    Route::post('auth/phone', 'Api\Auth\PhoneAuthController@phoneAuth');
    Route::post('auth/social', 'Api\Auth\SocialAuthController@socialAuth');
    Route::post('auth/refresh-token', 'Api\Auth\PhoneAuthController@refresh');
    Route::post('upload', 'Api\FileUploadController@index');
});

Route::group([
    'prefix' => 'v1',
    'as' => 'api.',
    'middleware' => ['auth:api', 'role:0'],
], function () {

    Route::get('auth/information', 'Api\UserController@information');
    Route::get('chat/conversation/messages', 'Api\ChatConversationsController@fetchMessages');
    Route::get('chat/conversation/user/{id}', 'Api\ChatConversationsController@hasConversation');
    Route::get('province', 'Api\ProvinceController@provence');
    Route::get('province/amphure', 'Api\ProvinceController@amphure');
    Route::get('province/district', 'Api\ProvinceController@district');
    Route::get('province/geographie', 'Api\ProvinceController@geographie');
    Route::get('post/account/{id}/like', 'Api\PostLikeController@accountLike');
    Route::get('follow/account/{id}/follower', 'Api\FollowerController@accountFollower');
    Route::get('share-log/account/{id}/share', 'Api\ShareLogController@accountShare');

    Route::post('auth/logout', 'Api\Auth\PhoneAuthController@logout');
    Route::post('account/follow', 'Api\FollowerController@followRequest');
    Route::post('account/unfollow', 'Api\FollowerController@followDenied');
    Route::post('account/attention', 'Api\AccountController@attentionCreate');
    Route::post('post/comment/reply', 'Api\PostCommentController@replyStore');
    Route::post('chat/message/send', 'Api\ChatMessageController@storeMessage');

    Route::put('chat/message/all/{id}/seen', 'Api\ChatMessageController@MessageSeenAll');
    Route::put('chat/message/{id}/seen', 'Api\ChatMessageController@MessageSeen');
    Route::put('post/donate/{id}', 'Api\PostController@donateUpdate');
    Route::put('follow/approve/{id}', 'Api\FollowerController@approve');
    Route::put('post/donate/reason/read/{id}', 'Api\PostDonateReasonController@reasonReaded');
    Route::put('notification/read/{id}', 'Api\NotificationController@notificationReaded');
    Route::put('notification/type/{id}/read', 'Api\NotificationController@notificationReadedAll');
    Route::put('story/{id}/seen', 'Api\StoryController@seen');
    Route::put('user-setting/users', 'Api\UserSettingController@usersUpdate');

    Route::resource('otp', 'Api\OtpController');
    Route::resource('account', 'Api\AccountController');
    Route::resource('follow', 'Api\FollowerController');
    Route::resource('post/donate/reason', 'Api\PostDonateReasonController');
    Route::resource('post/comment', 'Api\PostCommentController');
    Route::resource('post/like', 'Api\PostLikeController');
    Route::resource('post/recommend', 'Api\PostRecommendController');
    Route::resource('post', 'Api\PostController');
    Route::resource('attention', 'Api\AttentionController');

    Route::resource('objcategory', 'Api\ObjectCategoryController');
    Route::resource('user-setting', 'Api\UserSettingController');
    Route::resource('story', 'Api\StoryController');
    Route::resource('story-log', 'Api\StoryLogController');
    Route::resource('chat/messages', 'Api\ChatMessageController');
    Route::resource('chat/conversation', 'Api\ChatConversationsController');
    Route::resource('notification', 'Api\NotificationController');
    Route::resource('duration', 'Api\DurationController');
    Route::resource('share-log', 'Api\ShareLogController');
    Route::resource('thankpoint', 'Api\ThankPointController');
    Route::resource('banner', 'Api\BannerController');
    Route::resource('invite', 'Api\InviteLogController');
    Route::resource('report/post', 'Api\ReportPostController');
    Route::resource('report', 'Api\ReportController');
});
