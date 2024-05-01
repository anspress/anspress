<?php
namespace PHPSTORM_META {
    // Ensure $serviceName maps to classes that extend BaseService
    override(
        \AnsPress\Core\Classes\Main::get(0),
        map([
            '' => \AnsPress\Core\Classes\BaseService::class
        ])
    );
}
