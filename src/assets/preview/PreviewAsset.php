<?php
/**
 * SEOMate for Craft CMS 3.x
 *
 * @link      https://vaersaagod.no
 * @copyright Copyright (c) 2019 Værsågod
 */

namespace vaersaagod\seomate\assets\preview;

use Craft;
use craft\helpers\Json;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

use vaersaagod\seomate\SEOMate;

/**
 * @author    Værsågod
 * @package   NmrCpModule
 * @since     1.0.0
 */
class PreviewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@vaersaagod/seomate/assets/preview/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Preview.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $previewEnabled = SEOMate::$plugin->getSettings()->previewEnabled;

            if (!$this->shouldPreview($previewEnabled)) {
                return;
            }

            $previewAction = 'seomate/preview';
            if (\version_compare(Craft::$app->getVersion(), '3.1', '>=')) {
                $previewAction = Craft::$app->getSecurity()->hashData($previewAction);
            }

            $config = [
                'previewAction' => $previewAction,
            ];
            $configJson = Json::encode($config, JSON_UNESCAPED_UNICODE);
            $js = <<<JS
                    window.Craft.SEOMatePlugin = {$configJson};
JS;
            $view->registerJs($js, View::POS_HEAD);
        }
    }

    /**
     * Checks if we should enable live seo preview
     *
     * @param bool|string|array $previewEnabled
     * @return bool
     */
    private function shouldPreview($previewEnabled): bool
    {
        if ($previewEnabled === false) {
            return false;
        }

        $segments = Craft::$app->getRequest()->getSegments();
        $currentSourceHandle = \in_array($segments[0] ?? null, ['entries', 'categories']) ? $segments[1] ?? null : null;
        if (!$currentSourceHandle) {
            return false;
        }

        if ($previewEnabled !== true) {
            if (!\is_array($previewEnabled)) {
                $previewEnabled = \explode(',', $previewEnabled);
            }
            if (!\in_array($currentSourceHandle, $previewEnabled, true)) {
                return false;
            }
        }

        return true;
    }

}
