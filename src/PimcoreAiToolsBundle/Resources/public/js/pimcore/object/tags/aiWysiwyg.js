/**
 * instride.
 *
 * LICENSE
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that is distributed with this source code.
 *
 * @copyright Copyright (c) 2024 instride AG (https://instride.ch)
 */

pimcore.registerNS("pimcore.object.tags.aiWysiwyg");
/**
 * @private
 */
pimcore.object.tags.aiWysiwyg = Class.create(pimcore.object.tags.wysiwyg, {

    type: "aiWysiwyg",

    initialize: function (data, fieldConfig) {
        this.data = "";
        if (data) {
            this.data = data;
        }
        this.fieldConfig = fieldConfig;
        this.editableDivId = "object_ai_wysiwyg_" + uniqid();
        this.dirty = false;
    },

    getLayout: function () {
        var iconCls = null;
        if(this.fieldConfig.noteditable == false) {
            iconCls = "pimcore_ai_tools_icon_ai_wysiwyg";
        }

        var html = '<div class="pimcore_editable_wysiwyg pimcore_editable_ai_wysiwyg" id="' + this.editableDivId + '" contenteditable="true">' + this.data + '</div>';
        var pConf = {
            iconCls: iconCls,
            title: this.fieldConfig.title,
            html: html,
            border: true,
            bodyStyle: 'background: #fff',
            style: "margin-bottom: 10px",
            manageHeight: false,
            cls: "object_field object_field_type_" + this.type
        };

        if(this.fieldConfig.width) {
            pConf["width"] = this.fieldConfig.width;
        }

        if(this.fieldConfig.height) {
            pConf["height"] = this.fieldConfig.height;
            pConf["autoScroll"] = true;
        } else {
            pConf["autoHeight"] = true;
            pConf["autoScroll"] = true;
        }

        this.component = new Ext.Panel(pConf);
    },

    startWysiwygEditor: function () {
        if(this.ddWysiwyg) {
            return;
        }

        const initializeWysiwyg = new CustomEvent(pimcore.events.initializeWysiwyg, {
            detail: {
                config: this.fieldConfig,
                context: "object"
            },
            cancelable: true
        });
        const initIsAllowed = document.dispatchEvent(initializeWysiwyg);
        if(!initIsAllowed) {
            return;
        }

        const createWysiwyg = new CustomEvent(pimcore.events.createWysiwyg, {
            detail: {
                textarea: this.editableDivId,
                context: "object",
            },
            cancelable: true
        });
        const createIsAllowed = document.dispatchEvent(createWysiwyg);
        if(!createIsAllowed) {
            return;
        }

        document.addEventListener(pimcore.events.changeWysiwyg, function (e) {
            if (this.editableDivId === e.detail.e.target.id) {
                this.setValue(e.detail.data);
            }
        }.bind(this));

        if (!parent.pimcore.wysiwyg.editors.length) {
            Ext.get(this.editableDivId).dom.addEventListener("keyup", (e) => {
                this.setValue(Ext.get(this.editableDivId).dom.innerText);
            });
        }

        // add drop zone, use the parent panel here (container), otherwise this can cause problems when specifying a fixed height on the wysiwyg
        this.ddWysiwyg = new Ext.dd.DropZone(Ext.get(this.editableDivId).parent(), {
            ddGroup: "element",

            getTargetFromEvent: function(e) {
                return this.getEl();
            },

            onNodeOver : function(target, dd, e, data) {
                if (data.records.length === 1 && this.dndAllowed(data.records[0].data)) {
                    return Ext.dd.DropZone.prototype.dropAllowed;
                }
            }.bind(this),

            onNodeDrop : this.onNodeDrop.bind(this)
        });

        // Add AI button
        Ext.create('Ext.button.Split', {
            text: t('pimcore_ai_tools_button'),
            iconCls: 'pimcore_ai_tools_icon_ai_wysiwyg_white',
            renderTo: Ext.get(this.editableDivId).parent().parent().parent().parent(),
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
        var loadingMask = new Ext.LoadMask(this.component, {msg: t("pimcore_ai_tools_prompt_loading_mask_text")});
        loadingMask.show();
        Ext.Ajax.request({
            url: '/admin/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.editableDivId,
                text: this.getValue(),
                class: this.object.data.general.className,
                field: this.fieldConfig.name,
                type: 'object',
                promptType: 'text_creation',
            },
            success: function(response){
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><br><strong>" + text
                        + "</strong><br><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.getElementById(id);
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                        loadingMask.hide();
                    }
                );
            }
        });
    },

    startTextCorrection: function() {
        var loadingMask = new Ext.LoadMask(this.component, {msg: t("pimcore_ai_tools_prompt_loading_mask_text")});
        loadingMask.show();
        Ext.Ajax.request({
            url: '/admin/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.editableDivId,
                text: this.getValue(),
                class: this.object.data.general.className,
                field: this.fieldConfig.name,
                type: 'object',
                promptType: 'text_correction',
            },
            success: function(response){
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><strong>" + text
                        + "</strong><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.getElementById(id);
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                        loadingMask.hide();
                    }
                );
            }
        });
    },

    startTextOptimization: function() {
        var loadingMask = new Ext.LoadMask(this.component, {msg: t("pimcore_ai_tools_prompt_loading_mask_text")});
        loadingMask.show();
        Ext.Ajax.request({
            url: '/admin/pimcore-ai-tools/prompts/text',
            method: 'POST',
            params: {
                id: this.editableDivId,
                text: this.getValue(),
                class: this.object.data.general.className,
                field: this.fieldConfig.name,
                type: 'object',
                promptType: 'text_optimization',
            },
            success: function(response){
                var text = JSON.parse(response.responseText).result;
                var id = JSON.parse(response.responseText).id;

                Ext.Msg.confirm(
                    t("pimcore_ai_tools_prompt_change_confirm_title"),
                    t("pimcore_ai_tools_prompt_change_confirm_intro") + ":<br><strong>" + text
                        + "</strong><br>" + t("pimcore_ai_tools_prompt_change_confirm_message"),
                    function(btn) {
                        if (btn === 'yes') {
                            var editable = document.getElementById(id);
                            editable.innerHTML = text;
                            editable.dispatchEvent(new Event('focus'));
                        }
                        loadingMask.hide();
                    }
                );
            }
        });
    }
});
