<?php
/**
 * Translator Pro plugin for Craft CMS 3.x
 *
 * The Translator Pro is an easy to use and powerful solution to provide multilinguality for your Craft website, Craft Commerce and other plugins. With this plugin, you can easily manage the required translations in multiple languages.
 *
 * @link      https://www.infanion.com/
 * @copyright Copyright (c) 2021 Infanion
 */

namespace ip\translatorpro;

use ip\translatorpro\services\TranslatorProService as TranslatorProServiceService;
use ip\translatorpro\twigextensions\TranslatorProTwigExtension;
use ip\translatorpro\utilities\TranslatorProUtility as TranslatorProUtilityUtility;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use ip\translatorpro\helpers\ConfigHelper;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Infanion
 * @package   TranslatorPro
 * @since     1.0.0
 *
 * @property  TranslatorProServiceService $translatorProService
 */
class TranslatorPro extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * TranslatorPro::$plugin
     *
     * @var TranslatorPro
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = false;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * TranslatorPro::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $config = ConfigHelper::translatorproConfig();

        // Add in our Twig extensions
        Craft::$app->view->registerTwigExtension(new TranslatorProTwigExtension());

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['api/translator-pro/get'] = 'translator-pro/default/fetch';
                $event->rules['api/translator-pro/insert'] = 'translator-pro/default/insert';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['translator-pro'] = 'translator-pro/cp/index';
                $event->rules['translator-pro/edit'] = 'translator-pro/cp/edit';
                $event->rules['POST translator-pro/save'] = 'translator-pro/cp/save';
            }
        );

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = TranslatorProUtilityUtility::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'translator-pro',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        $is_pluginInstalled = Craft::$app->plugins->isPluginInstalled('translator-pro');
        $is_pluginEnabled = Craft::$app->plugins->isPluginEnabled('translator-pro');
        if($is_pluginInstalled == 1 && $is_pluginEnabled == 1){
            Craft::$app->set('i18n', $config);
        }

        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    $configs = ConfigHelper::craftConfig();
                    Craft::$app->set('i18n', $configs);
                }
            }
        );
    }

     /**
     * @inheritdoc
     */
    public function getCpNavItem()
    {
        $ret = parent::getCpNavItem();
        $ret['label'] = Craft::t('translator-pro', 'Translator Pro');

        return $ret;
    }

    // Protected Methods
    // =========================================================================

}
