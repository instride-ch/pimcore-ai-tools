# UIkit AI-Helper Component

### Requirements

- UIkit with activated dropdown, modal and spinner components

### Load JavaScript

Add the pimcore-ai-tools.js (vendor/instride/pimcore-ai-tools/src/PimcoreAiToolsBundle/Resources/assets/js/) to your project:

If you use webpack, include a new entry

```javascript
const paths = {
  ...
  pimcoreAiTools: resolve(__dirname, './vendor/instride/pimcore-ai-tools/src/PimcoreAiToolsBundle/Resources/assets'),
  ...
};
... 
    .addEntry('js/pimcore-ai-tools', `${paths.pimcoreAiTools}/js/pimcore-ai-tools.js`)
```

and include it in your template (webpack encore):

```html
{{- encore_entry_script_tags('js/pimcore-ai-tools') }}
```

### Usage

```html
<a data-uk-ai-helper="
    promptType: text_creation;
    textareaId: #my-textarea;
    aiToolsId: myNewAiTextarea;
    spinnerId: #my-uikit-spinner;"
   href="#">
    {{ 'pimcore_ai_tools.form.text_create'|trans }}
</a>
```

#### Required Options

- **promptType**: Type of prompt ("text_creation", "text_optimization" or "text_correction")
- **textareaId**: ID of textarea field
- **aiToolsId**: ID/Name for admin configuration module (has to be registered -> see below)
- **spinnerId**: ID of UIkit spinner element, which gets activated while prompt is requested

#### Example Usage

See [Example usage](../src/PimcoreAiToolsBundle/Resources/views/form/uikit_3_layout.html.twig)

### Register "frontend" items

Create an entry for each "aiToolsId":

```yarn
pimcore_ai_tools:
    frontend:
        - myNewAiTextarea
```

### Synchronize items & change prompt/provider

- Open up the "AI Configuration" module (under Settings)
- Go to "Frontend" and click on "Synchronize frontend fields" ![Synchronize frontend fields](images/frontend-synchronize.png)
- Your new field should now be available in the list

*Note: The bundle creates all three prompt types (creation, optimization, correction) automatically, even if you only use one of them* 
