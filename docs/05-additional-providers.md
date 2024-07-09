# Add custom provider

### Add class 

extend Instride\Bundle\PimcoreAiToolsBundle\Provider\AbstractProvider
and implement Instride\Bundle\PimcoreAiToolsBundle\Provider\TextProviderInterface

```php
class MyProvider extends AbstractProvider implements TextProviderInterface
{
    ...
    public function getText(array $options): string
    {
        // $options['prompt'] contains the composed prompt 
        return $options['prompt'];
    }
    ...
```

### Add service definition

```yaml
services:
    # My Provider
    pimcore_ai_tools.provider.my_provider:
        class: src\Provider\MyProvider
        tags:
            - { name: 'pimcore_ai_tools.provider', key: 'my_provider' }
```

extend provider locator arguments

```yaml
services:
    pimcore_ai_tools.provider_locator:
        class: Symfony\Component\DependencyInjection\ServiceLocator
        arguments:
            -
                OpenAi: '@pimcore_ai_tools.provider.open_ai'
                MyProvider: '@pimcore_ai_tools.provider.my_provider'
```
