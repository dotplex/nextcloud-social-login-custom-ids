<?php
namespace OCA\SocialLogin\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\IAppConfig;

class CreateDisabledUserPerProvider implements IRepairStep
{
    private $appName = 'dotplex_sociallogin';

    public function __construct(
        private IAppConfig $appConfig
    ) {}

    public function getName()
    {
        return 'Split create disabled users option per provider';
    }

    public function run(IOutput $output)
    {
        if (version_compare($this->appConfig->getValueString($this->appName, 'installed_version'), '16.6.0') >= 0) { // dotplex versions are upstream +10
            return;
        }
        if ($this->appConfig->getValueBool($this->appName, 'create_disabled_users')) {
            $configKey = 'oauth_providers';
            $providers = $this->appConfig->getValueArray($this->appName, $configKey);
            foreach ($providers as &$prov) {
                $prov['createDisabledUsers'] = true;
            }
            $this->appConfig->setValueArray($this->appName, $configKey, $providers);
            $configKey = 'custom_providers';
            $providers = $this->appConfig->getValueArray($this->appName, $configKey);
            foreach ($providers as &$provGroup) {
                foreach ($provGroup as &$prov) {
                    $prov['createDisabledUsers'] = true;
                }
            }
            $this->appConfig->setValueArray($this->appName, $configKey, $providers);
        }
        $this->appConfig->deleteKey($this->appName, 'create_disabled_users');
    }
}
