if (FRONTEND_FRAMEWORK === 'tailwind') {
  import('./components/ai-helper-tailwind.js').then((TailwindHelperModule) => {
    const TailwindHelper = TailwindHelperModule.default;
    TailwindHelper.init();
  });
} else {
  import('uikit').then((UIkit) => {
    import('./components/ai-helper-uikit.js').then((AiHelper) => {
      UIkit.component('ai-helper-uikit', AiHelper.default || AiHelper);
    });
  });
}
