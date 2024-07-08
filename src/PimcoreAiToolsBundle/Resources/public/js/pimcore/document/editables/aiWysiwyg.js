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
        Ext.get(this.textarea).applyStyles("width: " + inactiveContainerWidth  + "; min-height: " + textareaHeight
            + "px;");

        if(this.startWysiwygEditor()) {
            // register at global DnD manager
            if (typeof dndManager !== 'undefined') {
                dndManager.addDropTarget(Ext.get(this.id), this.onNodeOver.bind(this), this.onNodeDrop.bind(this));
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
            success: function(response){
                pimcore.helpers.loadingHide();
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><br><strong>" + text
                        + "</strong><br><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.querySelector('#' + id + ' .pimcore_ai_tools_wysiwyg');
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                    }
                );
            }
        });
    },

    startTextCorrection: function() {
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
            success: function(response){
                pimcore.helpers.loadingHide();
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><br><strong>" + text
                        + "</strong><br><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.querySelector('#' + id + ' .pimcore_ai_tools_wysiwyg');
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                    }
                );
            }
        });
    },

    startTextOptimization: function() {
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
            success: function(response){
                pimcore.helpers.loadingHide();
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><br><strong>" + text
                        + "</strong><br><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.querySelector('#' + id + ' .pimcore_ai_tools_wysiwyg');
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                    }
                );
            }
        });
    }
});
