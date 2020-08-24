<?php

class LoymaxWebApp_configs
{
    public $loymax_navigation_items_defaults;
    public $loymax_config_data_defaults;
    public $loymax_components;
    public $loymax_new_in_version;
    public $loymax_deleted_in_version;

    public function __construct() {
        $this->loymax_navigation_items_defaults = array(
            array(
                'authed_only' => false,
                'not_authed_only' => true,
                'href' => 'login',
                'name' => 'Вход',
                'component' => 'authentication',
            ),
            array(
                'authed_only' => false,
                'not_authed_only' => true,
                'href' => 'registration',
                'name' => 'Регистрация',
                'component' => 'registration',
            ),
            array(
                'authed_only' => false,
                'not_authed_only' => false,
                'href' => 'announcement',
                'name' => 'Новости',
                'component' => 'announcement',
            ),
            array(
                'authed_only' => false,
                'not_authed_only' => false,
                'href' => 'offers',
                'name' => 'Акции',
                'component' => 'offers',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'balance',
                'name' => 'Баланс',
                'component' => 'balance',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'history',
                'name' => 'История операций',
                'component' => 'history',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'cards',
                'name' => 'Карты',
                'component' => 'cards',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'personal-offers',
                'name' => 'Персональные предложения',
                'component' => 'personalOffers',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'personal-goods',
                'name' => 'Персональные товары',
                'component' => 'personalGoods',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'favorite-goods',
                'name' => 'Любимые вкусы',
                'component' => 'favoriteGoods',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'message',
                'name' => 'Уведомления',
                'component' => 'message',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'feedback',
                'name' => 'Чат',
                'component' => 'feedback',
            ),
            array(
                'authed_only' => false,
                'not_authed_only' => false,
                'href' => 'anonymous-feedback',
                'name' => 'Обратная связь',
                'component' => 'support',
            ),
            array(
                'authed_only' => false,
                'not_authed_only' => false,
                'href' => 'merchants',
                'name' => 'Магазины',
                'component' => 'merchant',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'personal',
                'name' => 'Личные данные',
            ),
            array(
                'authed_only' => true,
                'not_authed_only' => false,
                'href' => 'logout',
                'name' => 'Выход',
            ),
        );

        $this->loymax_config_data_defaults = array(
            'apiHost' => array(
                'name' => 'Loymax API',
                'value' => 'https://demo.loymax.tech/publicapi/',
                'type' => 'text',
                'isOption' => false,
            ),
            'oAuthClientId' => array(
                'name' => 'oAuthClientId',
                'value' => '',
                'type' => 'text',
                'isOption' => false,
            ),
            'imagesPath' => array(
                'name' => 'Path to image files',
                'value' => wp_make_link_relative( dirname( plugin_dir_url( __FILE__ ) ) ) . '/public/images',
                'type' => 'text',
                'isOption' => false,
            ),
            'reCaptchaSiteKey' => array(
                'name' => 'reCAPTCHA site key (public key)',
                'value' => '',
                'type' => 'text',
                'isOption' => false,
            ),
            'acceptTenderOfferByCheck' => array(
                'name' => 'Acceptance of a contract offer at the initial stage of registration (instead of a separate registration step)',
                'value' => 'true',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'authorizeOnRegistrationComplete' => array(
                'name' => 'Authorization of the LP Member in the Personal Account after successful completion of registration',
                'value' => 'true',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'forceRegistrationStartOnLoginAttempt' => array(
                'name' => 'Begin the registration process of an unregistered customer when trying to log in to the Personal Account',
                'value' => 'true',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'referralRegistration' => array(
                'name' => "Field for entering a friend's card number upon registration",
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'setRegistrationSmsCodeAsPassword' => array(
                'name' => 'Setting a verification code sent via SMS as a password to access the Personal Account',
                'value' => 'true',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'shareAuthLoginToRegistration' => array(
                'name' => 'Login autocomplete on the registration page with the login entered during a failed login attempt',
                'value' => 'true',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'authentication',
            ),
            'requestUserAttributes' => array(
                'name' => 'Ask for user attributes',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
            ),
            'offerHtmlFileId' => array(
                'name' => 'GUID of the contract offer html file',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'registration',
            ),
            'offerPdfFileId' => array(
                'name' => 'GUID of the contract offer pdf file',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'registration',
            ),
            'opdAgreementFileId' => array(
                'name' => 'GUID of pdf consent file for personal data processing',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'registration',
            ),
            'userStatusAttributeName' => array(
                'name' => 'The logical name of the Member status attribute in the database',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
            ),
            'userPurchasesAmountAttributeName' => array(
                'name' => 'The logical name of the purchases amount attribute of the Member in the database',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
            ),
            'supportEmail' => array(
                'name' => 'Technical support email',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'support',
            ),
            'redirectUrlOnEmailConfirmForRegistration' => array(
                'name' => 'Personal Account page for moving the Member after confirming the email by link upon registration',
                'value' => '/#/registration',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnEmailConfirmForSettings' => array(
                'name' => 'Personal Account page for moving the Member after confirming the email by link when changing the email',
                'value' => '/#/contacts',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnEmailConfirmWithoutToken' => array(
                'name' => 'Personal Account page for moving the Member after confirming the email by the link, if the token is out of date',
                'value' => '/#/login',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnLogin' => array(
                'name' => 'Personal Account page for moving the Member after successful authorization',
                'value' => '/#/history',
                'type' => 'text',
                'isOption' => true,
                'component' => 'authentication',
            ),
            'redirectUrlOnLogout' => array(
                'name' => 'Personal Account page for moving the Member after logout of the Personal Account',
                'value' => '/#/login',
                'type' => 'text',
                'isOption' => true,
                'component' => 'authentication',
            ),
            'redirectUrlOnRegistrationComplete' => array(
                'name' => 'Personal Account page for moving the Member upon successful completion of registration',
                'value' => "/#/history",
                'type' => 'text',
                'isOption' => true,
                'component' => 'registration',
            ),
            'redirectUrlOnResetPasswordEmailConfirm' => array(
                'name' => 'Personal Account page for moving the Member upon confirmation of password recovery using the link from the email',
                'value' => '/#/reset-password',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnSocialAuthFail' => array(
                'name' => 'Personal Account page for moving the Member in case of a failed authorization attempt via social network',
                'value' => '/#/registration',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnSocialAuthSuccess' => array(
                'name' => 'Personal Account page for moving the Member upon successful authorization via social network',
                'value' => '/#/history',
                'type' => 'text',
                'isOption' => true,
            ),
            'redirectUrlOnSocialBinding' => array(
                'name' => 'Personal Account page for moving the Member upon successful completion of linking social network',
                'value' => '/#/contacts',
                'type' => 'text',
                'isOption' => true,
            ),
            'personalGoodsApprovalMessage' => array(
                'name' => 'The text of the message on successful confirmation of personal products selection',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'personalGoods',
            ),
            'obsoleteBrowserDetection' => array(
                'name' => 'Notification when using an outdated browser (Internet Explorer)',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
            ),
            'favoriteGoodsImagesUrl' => array(
                'name' => 'The address from which images of your favorite products will be loaded',
                'value' => '',
                'type' => 'text',
                'isOption' => true,
                'component' => 'favoriteGoods',
            ),
            'requestUnreadMessage' => array(
                'name' => 'Mark unread messages before reading them',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'message',
            ),
            'requestUserStatus' => array(
                'name' => 'Display Member status in the Personal Account',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
            ),
            'requestPersonalOffers' => array(
                'name' => 'In the absence of personal offers, close access for the Member to the relevant section of the Personal Account',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'personalOffers',
            ),
            'requestPersonalGoods' => array(
                'name' => 'In the absence of personal products, close access for the Member to the relevant section of the Personal Account',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'personalGoods',
            ),
            'enableAppleWalletCards' => array(
                'name' => 'Adding a card to Apple Wallet',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'cards',
            ),
            'enableGoogleWalletCards' => array(
                'name' => 'Adding a card to Google Pay',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'cards',
            ),
            'authenticationIdentifiers' => array(
                'name' => 'Types of identifiers for authorization in the Personal Account',
                'value' => 'phone',
                'type' => 'text',
                'isOption' => true,
                'component' => 'authentication',
            ),
            'resetPasswordIdentifiers' => array(
                'name' => 'Types of identifiers for password reset in the Personal Account',
                'value' => 'phone',
                'type' => 'text',
                'isOption' => true,
                'component' => 'authentication',
            ),
            'registrationIdentifiers' => array(
                'name' => 'Types of identifiers for registration in the Personal Account',
                'value' => 'phone',
                'type' => 'text',
                'isOption' => true,
                'component' => 'registration',
            ),
            'subscriptionTypes' => array(
                'name' => 'Types of subscriptions that can be managed via Personal Account',
                'value' => 'Advertisement',
                'type' => 'text',
                'isOption' => true,
            ),
            'notificationTypes' => array(
                'name' => 'Types of notifiers used to manage subscriptions via Personal Account',
                'value' => 'smsNotification,emailNotification',
                'type' => 'text',
                'isOption' => true,
            ),
            'forceEmailStep' => array(
                'name' => 'Skip email linking step',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'registration',
            ),
            'cardNumberGraphicalCode' => array(
                'name' => 'Display barcodes and QR codes of cards',
                'value' => 'bar,qr',
                'type' => 'text',
                'isOption' => true,
                'component' => 'cards',
            ),
            'loadMoreButton' => array(
                'name' => 'Load all history elements on one page (when the flag is disabled, history elements are displayed page by page)',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'history',
            ),
            'filterByRegion' => array(
                'name' => 'Filtering points of sale by region',
                'value' => 'false',
                'type' => 'bool',
                'isOption' => true,
                'component' => 'merchant',
            ),
            'space' => array(
                'name' => 'The logical name of the ad slot',
                'value' => 'offerSpace',
                'type' => 'text',
                'isOption' => true,
                'component' => 'announcement',
            ),
            'cvcCodeCardsRegistration' => array (
            	'name' => 'Ability to add a card with cvc code',
            	'value' => 'false',
	            'type' => 'bool',
	            'isOption' => true,
                'component' => 'cards',
	        ),
        );

        $this->loymax_components = array(
            'authentication' => array(
                'name' => 'Authorization',
                'selected' => true,
                'menu_order' => 0,
                'description' => 'The module is intended for authorization of Loyalty Program Members in the Personal Account',
            ),
            'registration' => array(
                'name' => 'Registration',
                'selected' => true,
                'menu_order' => 1,
                'description' => 'The module is intended for registration of customers in the Loyalty Program',
            ),
            'cards' => array(
                'name' => 'Cards',
                'selected' => true,
                'menu_order' => 2,
                'description' => 'The module provides the Loyalty Program Member with information on cards and accounts and access to operations with them',
            ),
            'history' => array(
                'name' => 'History',
                'selected' => true,
                'menu_order' => 3,
                'description' => 'The module is intended for viewing operations conducted on the cards of the Loyalty Program Member',
            ),
            'merchant' => array(
                'name' => 'Points of sale',
                'selected' => true,
                'menu_order' => 4,
                'description' => 'The module is intended for displaying on the map the points of sale participating in the Loyalty Program',
            ),
            'announcement' => array(
                'name' => 'Advertisement',
                'selected' => true,
                'menu_order' => 5,
                'description' => 'The module is intended for displaying advertisement',
            ),
            'personalOffers' => array(
                'name' => 'Personal offers',
                'selected' => false,
                'menu_order' => 6,
                'description' => 'The module is intended for displaying personal offers of the Loyalty Program Member',
                'isPaid' => true,
            ),
            'personalGoods' => array(
                'name' => 'Personal products',
                'selected' => false,
                'menu_order' => 7,
                'description' => 'The module is intended for displaying and selecting personal products of the Loyalty Program Member',
                'isPaid' => true,
            ),
            'favoriteGoods' => array(
                'name' => 'Favorite tastes',
                'selected' => false,
                'menu_order' => 8,
                'description' => 'The module is intended for displaying and selecting favorite products of the Loyalty Program Member',
                'isPaid' => true,
            ),
            'support' => array(
                'name' => 'Support service',
                'selected' => false,
                'menu_order' => 9,
                'description' => 'The module is intended for sending messages to technical support email',
            ),
            'offers' => array(
                'name' => 'Offers',
                'selected' => false,
                'menu_order' => 10,
                'description' => 'The module is intended for displaying Loyalty Program offers',
            ),
            'message' => array(
                'name' => 'Notifications',
                'selected' => false,
                'menu_order' => 11,
                'description' => 'The module is intended for displaying notifications sent to the Member as part of the Loyalty Program',
            ),
            'balance' => array(
                'name' => 'Detailed balance',
                'selected' => false,
                'menu_order' => 12,
                'description' => 'The module is intended for displaying detailed balance of the Loyalty Program Member',
            ),
            'feedback' => array(
                'name' => 'Chat',
                'selected' => false,
                'menu_order' => 13,
                'description' => 'The module is intended for messaging with technical support in the form of chat',
                'isPaid' => true,
            ),
        );

        $this->loymax_new_in_version = array(
            '2.2' => array(
                'userStatusAttributeName',
                'userPurchasesAmountAttributeName',
            ),
            '2.3' => array(
                'personalGoodsApprovalMessage',
                'obsoleteBrowserDetection',
                'favoriteGoodsImagesUrl',
                'requestUnreadMessage',
            ),
            '2.4' => array(
                'requestUserStatus',
                'requestPersonalOffers',
                'requestPersonalGoods',
            ),
            '2.5' => array(
                'enableAppleWalletCards',
                'enableGoogleWalletCards',
                'authenticationIdentifiers',
                'resetPasswordIdentifiers',
                'registrationIdentifiers',
            ),
            '2.6' => array(
                'reCaptchaSiteKey',
            ),
            '2.8' => array(
                'cvcCodeCardsRegistration',
            ),
            '2.9' => array(
                'oAuthClientId',
            ),
            '3' => array(
                'forceEmailStep',
                'cardNumberGraphicalCode',
                'loadMoreButton',
                'filterByRegion',
                'subscriptionTypes',
                'notificationTypes',
                'space',
            ),
        );

        $this->loymax_deleted_in_version = array(
            '2.2' => [
                'locales'
            ],
            '2.5' => [
                'enableWalletCards'
            ],
            '2.8' => [
                'reCaptchaType',
                'enableReCaptchaInAuthentication',
                'enableReCaptchaInRegistration',
                'enableReCaptchaInResetPassword',
            ],
        );
    }
}
