# Installation & Bundle Configuration

### Install with Composer

```
composer require instride/pimcore-ai-tools:^1.0
```

### Loading the Bundle

Register the bundle in your `config/bundles.php` file to enable it.

```php
<?php

return [
    // ...
    Instride\Bundle\PimcoreAiToolsBundle\PimcoreAiToolsBundle::class => ['all' => true],
];
```

### Configure Providers

Change the OpenAI API key via yaml configuration or use the default environment variable "OPEN_AI_API_KEY".

```yaml
pimcore_ai_tools:
    providers:
        open_ai:
            api_key: '%env(OPEN_AI_API_KEY)%'
```
