pimcore.registerNS("pimcore.document.editables.ai_wysiwyg");
pimcore.document.editables.ai_wysiwyg = Class.create(pimcore.document.editables.wysiwyg, {
    getType: function () {
        return "ai_wysiwyg";
    },

    render: function () {
        this.setupWrapper();

        this.textarea = document.createElement("div");
        this.textarea.setAttribute("contenteditable","true");

        Ext.get(this.id).appendChild(this.textarea);
        Ext.get(this.id).insertHtml("beforeEnd",'<div class="pimcore_editable_droptarget"></div>');

        this.textarea.id = this.id + "_textarea";
        this.textarea.innerHTML = this.data;

        let textareaHeight = 100;
        if (this.config.height) {
            textareaHeight = this.config.height;
        }
        if (this.config.placeholder) {
            this.textarea.setAttribute('data-placeholder', this.config["placeholder"]);
        }

        let inactiveContainerWidth = this.config.width + "px";
        if (typeof this.config.width == "string" && this.config.width.indexOf("%") >= 0) {
            inactiveContainerWidth = this.config.width;
        }

        Ext.get(this.textarea).addCls("pimcore_wysiwyg");
        Ext.get(this.textarea).addCls("pimcore_ai_tools_wysiwyg");
        Ext.get(this.textarea).applyStyles(
          "width: " + inactiveContainerWidth + "; min-height: " + textareaHeight + "px;"
        );

        if (this.startWysiwygEditor()) {
            // register at global DnD manager
            if (typeof dndManager !== 'undefined') {
                dndManager.addDropTarget(
                  Ext.get(this.id),
                  this.onNodeOver.bind(this),
                  this.onNodeDrop.bind(this)
                );
            }
        }

        // Add AI button
        Ext.create('Ext.button.Split', {
            text: t('pimcore_ai_tools_button'),
            iconCls: 'pimcore_ai_tools_icon_ai_wysiwyg_white',
            renderTo: Ext.get(this.textarea).parent(),
            cls: 'pimcore_ai_tools_button_menu_container',
            menu: new Ext.menu.Menu({
                items: [
                    {
                        text: t('pimcore_ai_tools_button_text_creation'),
                        iconCls: 'pimcore_ai_tools_icon_ai_wysiwyg_white',
                        handler: this.startTextCreation.bind(this)
                    },
                    {
                        text: t('pimcore_ai_tools_button_text_correction'),
                        iconCls: 'pimcore_ai_tools_icon_ai_wysiwyg_white',
                        handler: this.startTextCorrection.bind(this)
                    },
                    {
                        text: t('pimcore_ai_tools_button_text_optimization'),
                        iconCls: 'pimcore_ai_tools_icon_ai_wysiwyg_white',
                        handler: this.startTextOptimization.bind(this)
                    }
                ]
            })
        });
    },

    startTextCreation: function() {
        if (!this.validateInput(this.getValue())) return;

        pimcore.helpers.loadingShow();

        Ext.Ajax.request({
            url: '/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.id,
                text: this.getValue(),
                areabrick: this.config.areabrick,
                editable: this.realName,
                type: 'editable',
                promptType: 'text_creation',
            },
            success: function(response) {
                pimcore.helpers.loadingHide();
                let data = JSON.parse(response.responseText);
                let text = data.result;
                let newId = data.id;

                this.openCreationWindow(newId, text);
            }.bind(this)
        });
    },

    openCreationWindow: function(editableId, mainText) {
        const context = this;
        const creationWindow = Ext.create('Ext.window.Window', {
            title: t("pimcore_ai_tools_prompt_change_confirm_title"),
            modal: true,
            width: 600,
            bodyPadding: 10,
            layout: { type: 'vbox', align: 'stretch' },
            items: [
                {
                    xtype: 'textarea',
                    itemId: 'mainTextArea',
                    fieldLabel: t('pimcore_ai_tools_prompt_change_confirm_intro'),
                    labelAlign: 'top',
                    width: '100%',
                    height: 150,
                    value: mainText,
                    margin: '0 0 10 0'
                },
                {
                    xtype: 'box',
                    html: '<strong>' + t("pimcore_ai_tools_prompt_change_confirm_message") + '</strong>',
                    margin: '0 0 10 0'
                },
                {
                    xtype: 'container',
                    layout: 'hbox',
                    items: [
                        {
                            xtype: 'button',
                            text: t('Yes'),
                            iconCls: 'pimcore_icon_apply',
                            margin: '0 10 0 0',
                            handler: function() {
                                const mainTextArea = creationWindow.down('#mainTextArea');
                                const finalText = mainTextArea.getValue();

                                const editable = document.querySelector('#' + editableId + ' .pimcore_ai_tools_wysiwyg');
                                if (editable) {
                                    editable.innerHTML = finalText;
                                    editable.dispatchEvent(new Event('focus'));
                                }
                                creationWindow.close();
                            }
                        },
                        {
                            xtype: 'button',
                            text: t('No'),
                            iconCls: 'pimcore_icon_delete',
                            handler: function() {
                                creationWindow.close();
                            }
                        }
                    ]
                },
                {
                    xtype: 'textarea',
                    itemId: 'refinementField',
                    fieldLabel: t('pimcore_ai_tools_prompt_refine_placeholder'),
                    labelAlign: 'top',
                    width: '100%',
                    height: 100,
                    margin: '10 0 10 0'
                },
                {
                    xtype: 'button',
                    text: t('pimcore_ai_tools_prompt_refine_button'),
                    iconCls: 'pimcore_icon_refine',
                    margin: '10 0 0 0',
                    handler: function() {
                        const mainTextArea = creationWindow.down('#mainTextArea');
                        const refinementField = creationWindow.down('#refinementField');
                        const currentMainText = mainTextArea.getValue();
                        const refineText = refinementField.getValue();

                        context.sendRefinementRequest(editableId, currentMainText, refineText, creationWindow);
                    }
                }
            ]
        });

        creationWindow.show();
    },

    sendRefinementRequest: function(editableId, currentMainText, refineText, oldWindow) {
        if (!this.validateInput(refineText)) return;

        pimcore.helpers.loadingShow();
        const combined = currentMainText + "\n" + refineText;

        Ext.Ajax.request({
            url: '/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: editableId,
                text: combined,
                areabrick: this.config.areabrick,
                editable: this.realName,
                type: 'editable',
                promptType: 'text_refinement',
            },
            success: function(response) {
                pimcore.helpers.loadingHide();
                let data = JSON.parse(response.responseText);
                let refinedText = data.result;
                let newId = data.id || editableId;

                oldWindow.close();
                this.openCreationWindow(newId, refinedText);
            }.bind(this)
        });
    },

    startTextCorrection: function() {
        if (!this.validateInput(this.getValue())) return;

        pimcore.helpers.loadingShow();

        Ext.Ajax.request({
            url: '/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.id,
                text: this.getValue(),
                areabrick: this.config.areabrick,
                editable: this.realName,
                type: 'editable',
                promptType: 'text_correction',
            },
            success: function(response) {
                pimcore.helpers.loadingHide();
                let data = JSON.parse(response.responseText);
                let text = data.result;
                let newId = data.id;

                Ext.Msg.confirm(
                  t("pimcore_ai_tools_prompt_change_confirm_title"),
                  t("pimcore_ai_tools_prompt_change_confirm_intro") +
                  ":<br><br><strong>" + text + "</strong><br><br>" +
                  t("pimcore_ai_tools_prompt_change_confirm_message"),
                  function(btn) {
                      if (btn === 'yes') {
                          let editable = document.querySelector('#' + newId + ' .pimcore_ai_tools_wysiwyg');
                          if (editable) {
                              editable.innerHTML = text;
                              editable.dispatchEvent(new Event('focus'));
                          }
                      }
                  }
                );
            }.bind(this)
        });
    },

    startTextOptimization: function() {
        if (!this.validateInput(this.getValue())) return;

        pimcore.helpers.loadingShow();

        Ext.Ajax.request({
            url: '/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.id,
                text: this.getValue(),
                areabrick: this.config.areabrick,
                editable: this.realName,
                type: 'editable',
                promptType: 'text_optimization',
            },
            success: function(response) {
                pimcore.helpers.loadingHide();
                let data = JSON.parse(response.responseText);
                let text = data.result;
                let newId = data.id;

                Ext.Msg.confirm(
                  t("pimcore_ai_tools_prompt_change_confirm_title"),
                  t("pimcore_ai_tools_prompt_change_confirm_intro") +
                  ":<br><br><strong>" + text + "</strong><br><br>" +
                  t("pimcore_ai_tools_prompt_change_confirm_message"),
                  function(btn) {
                      if (btn === 'yes') {
                          let editable = document.querySelector('#' + newId + ' .pimcore_ai_tools_wysiwyg');
                          if (editable) {
                              editable.innerHTML = text;
                              editable.dispatchEvent(new Event('focus'));
                          }
                      }
                  }
                );
            }.bind(this)
        });
    },

    validateInput: function($prompt) {
        if (!$prompt) {
            Ext.Msg.alert(
              t("pimcore_ai_tools_prompt_warning_title"),
              t("pimcore_ai_tools_prompt_empty_warning_message")
            );

            return false;
        }

        return true;
    }
});
