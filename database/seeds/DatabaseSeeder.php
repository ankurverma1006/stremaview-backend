<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(MobileRegisterSeeder::class);
        $this->call(EmailVerificationSeeder::class);
        $this->call(SubscriptionSeeder::class);
        $this->call(AddedLanguageControlKeyInSettingsTable::class);
        $this->call(AppLinkSeeder::class);
        $this->call(AddedSliderKeys::class);
        $this->call(ScriptSettingSeeder::class);
        $this->call(VideoSettingsSeeder::class);
        $this->call(AdminDemoLoginSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(AddedMaxsizekeysInSettings::class);
        $this->call(SubProfileSeeder::class);
        $this->call(AddedStripeKeyInSettings::class);
        $this->call(AddedViewCountInSettingsTable::class);
        $this->call(AddedRedeemOptionInSettings::class);
        $this->call(AddSocialLinksSeeder::class);
        $this->call(TokenExpirySeeder::class);
        $this->call(PageDemoSeeder::class);
        $this->call(ModeratorSeeder::class);
        $this->call(MailGunSeeder::class);
        $this->call(CopyRightSeeder::class);
        $this->call(SecureVideoSeeder::class);
        $this->call(SocketUrlSettings::class);
        $this->call(EmailNotificationSeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(AddedCustomUsersCount::class);
        $this->call(IosPaymentStatusSeeder::class);
        $this->call(PageCountSeeder::class);
        $this->call(AutomaticRenewalSeeder::class);
        $this->call(FilenamePrefixSeeder::class);
        $this->call(ModeratorEmailUpdateSeeder::class);
        $this->call(FfmpegSeeder::class);
        $this->call(HomePageSeeder::class);
        $this->call(SEOSeeder::class);
        $this->call(MailGunEmailCheckSeeder::class);
        $this->call(NotificationSeeder::class);
        $this->call(PushNotificationSettingsSeeder::class);
        $this->call(V4Seeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(SocialEmailSuffix::class);
    }
}

