document.addEventListener(pimcore.events.postOpenObject, (event) => {
  const object = event.detail.object;

  if (!object || !object.properties || object.properties.type !== "object") {
    return;
  }

  const languages = pimcore.settings.websiteLanguages;
  const objectId = object.id;
  const className = object.data.general.className;
  const localizedFields = object.data.data.localizedfields?.data || {};
  let menuItems = [];

  languages.forEach(lang => {
    const langCode = lang.split("_")[0];
    const flagIconCls = "pimcore_icon_language_" + langCode.toLowerCase();

    let fieldsToTranslate = [];

    if (!localizedFields[lang]) {
      console.warn(`No localized data for ${lang}, assuming all fields need translation.`);
      fieldsToTranslate = Object.keys(object.data.data);
    } else {
      Object.keys(localizedFields[lang]).forEach(field => {
        if (!localizedFields[lang][field]) {
          fieldsToTranslate.push(field);
        }
      });
    }

    if (fieldsToTranslate.length > 0) {
      menuItems.push({
        text: `${t("pimcore_ai_tools_object_translate_to")} ${lang}`,
        iconCls: flagIconCls,
        handler: function () {
          const url = `/pimcore-ai-tools/translate/${objectId}/${className}/${fieldsToTranslate.join("_")}/${lang}`;

          fetch(url, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
          })
            .then(response => response.json())
            .then(data => {
              forceReloadObject(objectId);
            })
            .catch(error => {
              console.error("Translation error:", error);
            });
        },
      });
    }
  });

  if (menuItems.length > 0) {
    menuItems.unshift({
      text: t("pimcore_ai_tools_object_translate_all"),
      iconCls: "pimcore_icon_world",
      handler: function () {
        // TODO: Backend für "Alle Sprachen übersetzen" implementieren
        // `translateAllAction` Methode im TranslationController ergänzen.

        // const url = `/pimcore-ai-tools/translate-all/${objectId}/${className}`;
        //
        // fetch(url, {
        //   method: "POST",
        //   headers: {
        //     "Content-Type": "application/json",
        //   },
        // })
        //   .then(response => response.json())
        //   .then(data => {
        //     pimcore.helpers.openObject(objectId, "object");
        //   })
        //   .catch(error => {
        //     console.error("Translation error:", error);
        //   });
      },
    });
  }

  const languageMenu = new Ext.menu.Menu({
    items: menuItems,
  });

  const dropdownButton = new Ext.button.Button({
    text: t("pimcore_ai_tools_object_translate"),
    iconCls: "pimcore_icon_dropdown",
    menu: languageMenu,
  });

  if (object.toolbar && menuItems.length > 0) {
    object.toolbar.add(dropdownButton);
    pimcore.layout.refresh();
  }

  function forceReloadObject(objectId) {
    const tabPanel = Ext.getCmp("pimcore_panel_tabs");
    const tabId = "object_" + objectId;
    const existingTab = Ext.getCmp(tabId);

    if (existingTab) {
      tabPanel.remove(existingTab);
    }

    pimcore.helpers.openObject(objectId, "object");
  }
});
