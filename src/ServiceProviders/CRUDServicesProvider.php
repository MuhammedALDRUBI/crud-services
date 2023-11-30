<?php

namespace CRUDServices\ServiceProviders;

use CRUDServices\ConfigManagers\ConfigManager;
use Illuminate\Support\ServiceProvider;

class CRUDServicesProvider extends ServiceProvider
{

    protected function configPublishHandling() : void
    {
        $configManager = ConfigManager::Singleton();
        $this->publishes(
            [$configManager->getConfigFilePath() => config_path( $configManager->getConfigFileFullName() ) ] ,
            $configManager->getConfigFileName()
        );
    }

    public function boot()
    {
        $this->configPublishHandling();

    }

}
