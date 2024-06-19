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
pimcore.object.tags.aiWysiwyg = Class.create(pimcore.object.tags.abstract, {

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

    getGridColumnConfig: function (field) {
        var renderer = function (key, value, metaData, record) {
            this.applyPermissionStyle(key, value, metaData, record);

            try {
                if (record.data.inheritedFields && record.data.inheritedFields[key] && record.data.inheritedFields[key].inherited == true) {
                    metaData.tdCls += " grid_value_inherited";
                }
            } catch (e) {
                console.log(e);
            }
            return value;

        }.bind(this, field.key);

        return {
            text: t(field.label), sortable: true, dataIndex: field.key, renderer: renderer,
            getEditor: this.getWindowCellEditor.bind(this, field)
        };
    },

    getGridColumnFilter: function (field) {
        return {type: 'string', dataIndex: field.key};
    },

    getLayout: function () {
        var iconCls = null;
        if(this.fieldConfig.noteditable == false) {
            iconCls = "pimcore_ai_icon_ai_wysiwyg";
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

    getLayoutShow: function () {
        this.getLayout();
        this.component.on("afterrender", function() {
            Ext.get(this.editableDivId).dom.setAttribute("contenteditable", "false");
        }.bind(this));
        this.component.disable();
        return this.component;
    },

    getLayoutEdit: function () {
        this.getLayout();
        this.component.on("afterlayout", this.startWysiwygEditor.bind(this));

        if(this.ddWysiwyg) {
            this.component.on("beforedestroy", function () {
                const beforeDestroyWysiwyg = new CustomEvent(pimcore.events.beforeDestroyWysiwyg, {
                    detail: {
                        context: "object",
                    },
                });
                document.dispatchEvent(beforeDestroyWysiwyg);
            }.bind(this));
        }

        return this.component;
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
            text: t('pimcore_ai_button'),
            iconCls: 'pimcore_ai_icon_ai_wysiwyg',
            renderTo: Ext.get(this.editableDivId).parent().parent().parent().parent(),
            cls: 'pimcore_ai_button_menu_container',
            menu: new Ext.menu.Menu({
               items: [
                   {
                       text: t('pimcore_ai_button_text_create'),
                       iconCls: 'pimcore_ai_icon_ai_wysiwyg',
                       handler: this.startTextCreation.bind(this)
                   },
                   {
                       text: t('pimcore_ai_button_text_correction'),
                       iconCls: 'pimcore_ai_icon_ai_wysiwyg',
                       handler: this.startTextCorrection.bind(this)
                   },
                   {
                       text: t('pimcore_ai_button_text_optimize'),
                       iconCls: 'pimcore_ai_icon_ai_wysiwyg',
                       handler: this.startTextOptimization.bind(this)
                   }
               ]
            })
        });
    },

    onNodeDrop: function (target, dd, e, data) {
        if (!pimcore.helpers.dragAndDropValidateSingleItem(data) || !this.dndAllowed(data.records[0].data) || this.inherited) {
            return false;
        }

        const onDropWysiwyg = new CustomEvent(pimcore.events.onDropWysiwyg, {
            detail: {
                target: target,
                dd: dd,
                e: e,
                data: data,
                context: "object",
            },
        });

        document.dispatchEvent(onDropWysiwyg);
    },

    dndAllowed: function(data) {

        if (data.elementType == "document" && (data.type=="page"
            || data.type=="hardlink" || data.type=="link")){
            return true;
        } else if (data.elementType=="asset" && data.type != "folder"){
            return true;
        } else if (data.elementType=="object" && data.type != "folder"){
            return true;
        }

        return false;
    },

    getValue: function () {
        return this.data;
    },

    setValue: function (value) {
        this.dirty = true;
        this.data = value;
    },

    getName: function () {
        return this.fieldConfig.name;
    },

    isDirty: function() {
        if(!this.isRendered()) {
            return false;
        }

        return this.dirty;
    },

    getWindowCellEditor: function (field, record) {
        return new pimcore.element.helpers.gridCellEditor({
                fieldInfo: field,
                elementType: "object"
            }
        );
    },

    getCellEditValue: function () {
        return this.getValue();
    },

    startTextCreation: function() {
        console.log('startTextCreation');
    },

    startTextCorrection: function() {
        Ext.Ajax.request({
            url: '/admin/pimcore-ai/text-correction',
            method: 'POST',
            params: {
                text: this.data
            },
            success: function(response){
                var text = response.responseText;
                console.log(text);
            }
        });
    },

    startTextOptimization: function() {
        console.log('startTextOptimization');
    }
});
