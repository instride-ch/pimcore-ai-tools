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

pimcore.registerNS("pimcore.bundle.pimcore_ai.settings");

pimcore.bundle.pimcore_ai.settings = Class.create({

    initialize: function () {
        this.getTabPanel();
    },

    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.setActiveItem("pimcore_pimcore_ai");
    },

    getTabPanel: function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "pimcore_pimcore_ai",
                iconCls: "pimcore_ai_nav_icon",
                title: t("pimcore_ai_configuration"),
                border: false,
                autoScroll: true,
                flex: 1,
                layout: {
                    type: 'vbox',
                    align: 'stretch',
                },
                closable: true,
                items: [Ext.create('Ext.tab.Panel', {
                    items: [
                        this.getDefaultsEditor(),
                        this.getEditableRowEditor(),
                        this.getObjectRowEditor(),
                    ]
                })]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("pimcore_pimcore_ai");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("bundle_pimcore_ai");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getDefaultsEditor: function () {
        var textProviderStore = new Ext.data.Store({
            fields: ['value', 'name'],
            data: [
                {"value": "openAi", "name": "OpenAi"},
            ],
        });

        this.defaultsForm = Ext.create('Ext.form.Panel', {
            title: t("pimcore_ai_defaults_configuration"),
            url: '/admin/pimcore-ai/settings/save-defaults',
            bodyPadding: 20,
            labelWidth: 150,
            autoScroll: true,
            flex: 1,
            layout: {
                type: 'container',
            },
            defaults: {anchor: '100%', labelWidth: 150},
            items: [{
                fieldLabel: t("pimcore_ai_defaults_text_provider"),
                name: 'textProvider',
                xtype: 'combobox',
                store: textProviderStore,
                displayField: 'name',
                valueField: 'value',
            },{
                xtype: 'fieldset',
                title: t("pimcore_ai_defaults_text_modules"),
                layout: 'anchor',
                defaults: {anchor: '100%', labelWidth: 150},
                items: [{
                    fieldLabel: t("pimcore_ai_defaults_text_module_create"),
                    xtype: 'textareafield',
                    name: 'textModuleCreate',
                },{
                    fieldLabel: t("pimcore_ai_defaults_text_module_optimize"),
                    xtype: 'textareafield',
                    name: 'textModuleOptimize',
                },{
                    fieldLabel: t("pimcore_ai_defaults_text_module_correct"),
                    xtype: 'textareafield',
                    name: 'textModuleCorrect',
                }],
            },{
                xtype: 'fieldset',
                title: t("pimcore_ai_defaults_text_objects"),
                layout: 'anchor',
                defaults: {anchor: '100%', labelWidth: 150},
                items: [{
                    fieldLabel: t("pimcore_ai_defaults_text_object_create"),
                    xtype: 'textareafield',
                    name: 'textObjectCreate',
                },{
                    fieldLabel: t("pimcore_ai_defaults_text_object_optimize"),
                    xtype: 'textareafield',
                    name: 'textObjectOptimize',
                },{
                    fieldLabel: t("pimcore_ai_defaults_text_object_correct"),
                    xtype: 'textareafield',
                    name: 'textObjectCorrect',
                }],
            }],
            buttons: [{
                text: t("pimcore_ai_defaults_form_submit"),
                formBind: true,
                handler: function() {
                    var form = this.up('form').getForm();

                    if (form.isValid()) {
                        form.submit({
                            success: function(form, action) {
                               Ext.Msg.alert(t("pimcore_ai_defaults_form_success"), t("pimcore_ai_defaults_form_success_text"));
                            },
                            failure: function(form, action) {
                                Ext.Msg.alert(t("pimcore_ai_defaults_form_failure"), t("pimcore_ai_defaults_form_failure_text"));
                            }
                        });
                    }
                }
            }],
        });

        this.defaultsForm.getForm().load({
            url: '/admin/pimcore-ai/settings/load-defaults',
        });

        return this.defaultsForm;
    },

    getEditableRowEditor: function () {
        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        this.editableStore = pimcore.helpers.grid.buildDefaultStore(
            '/admin/pimcore-ai/settings/editable-configuration',
            [
                'id',
                'editableId',
                'type',
                {name: 'prompt', allowBlank: true},
                {name: 'options', allowBlank: true},
                {name: 'provider', allowBlank: true}
            ],
            itemsPerPage,
            { storeId: 'pimcoreAiEditableStore' }
        );

        this.editableFilterField = Ext.create("Ext.form.TextField", {
            width: 200,
            style: "margin: 0 10px 0 0;",
            enableKeyEvents: true,
            listeners: {
                "keydown" : function (field, key) {
                    if (key.getKey() == key.ENTER) {
                        var input = field;
                        var proxy = this.editableStore.getProxy();
                        proxy.extraParams.filter = input.getValue();
                        this.editableStore.load();
                    }
                }.bind(this)
            }
        });

        this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.editableStore);

        var typesColumns = [
            {text: t("pimcore_ai_editableId"), flex: 1, sortable: true, dataIndex: 'editableId', editable: false},
            {text: t("pimcore_ai_type"), flex: 1, sortable: true, dataIndex: 'type', editable: false},
            {text: t("pimcore_ai_prompt"), flex: 3, sortable: true, dataIndex: 'prompt',
                editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_options"), flex: 3, sortable: true, dataIndex: 'options',
                editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_provider"), flex: 1, sortable: true, dataIndex: 'provider',
                emptyCellText: t("pimcore_ai_provider_default"), editor: new Ext.form.TextField({})},
        ];

        this.editableRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            clicksToMoveEditor: 1,
        });

        var updateEditablesButton = Ext.create('Ext.Button', {
            text: t("pimcore_ai_sync_editables"),
            scale: 'medium',
            iconCls: 'pimcore_icon_update',
            handler: this.updateEditables.bind(this)
        });

        var toolbar = Ext.create('Ext.Toolbar', {
            cls: 'pimcore_main_toolbar',
            items: [
                {
                    text: t("filter") + "/" + t("search"),
                    xtype: "tbtext",
                    style: "margin: 0 10px 0 0;"
                },
                this.editableFilterField,
                '->',
                updateEditablesButton,
            ]
        });

        this.editableGrid = Ext.create('Ext.grid.Panel', {
            title: t("pimcore_ai_editable_configuration"),
            autoScroll: true,
            store: this.editableStore,
            columns: {
                items: typesColumns,
                defaults: {
                    renderer: Ext.util.Format.htmlEncode
                },
            },
            selModel: Ext.create('Ext.selection.RowModel', {}),
            plugins: [
                this.editableRowEditing
            ],
            trackMouseOver: true,
            columnLines: true,
            bbar: this.pagingtoolbar,
            bodyCls: "pimcore_editable_grid",
            stripeRows: true,
            tbar: toolbar,
            flex: 1,
            viewConfig: {
                forceFit: true,
                listeners: {
                    rowupdated: this.updateEditableRows.bind(this),
                    refresh: this.updateEditableRows.bind(this)
                }
            }
        });

        this.editableStore.on("update", this.updateEditableRows.bind(this));
        this.editableGrid.on("viewready", this.updateEditableRows.bind(this));

        this.editableStore.load();

        return this.editableGrid;
    },

    updateEditableRows: function () {
        var rows = Ext.get(this.editableGrid.getEl().dom).query(".x-grid-row");

        for (var i = 0; i < rows.length; i++) {

            let dd = new Ext.dd.DropZone(rows[i], {
                ddGroup: "element",

                getTargetFromEvent: function(e) {
                    return this.getEl();
                },
            });
        }
    },

    updateEditables: function () {
        Ext.Ajax.request({
            url: '/admin/pimcore-ai/settings/sync-editables',
            method: 'POST',
            success: function(){
                this.editableStore.sync();
            }
        })
    },

    getObjectRowEditor: function () {

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        this.objectStore = pimcore.helpers.grid.buildDefaultStore(
            '/admin/pimcore-ai/settings/object-configuration',
            [
                'id',
                'className',
                'fieldName',
                'type',
                {name: 'prompt', allowBlank: true},
                {name: 'options', allowBlank: true},
                {name: 'provider', allowBlank: true}
            ],
            itemsPerPage,
            { storeId: 'pimcoreAiObjectStore' }
        );

        this.objectFilterField = Ext.create("Ext.form.TextField", {
            width: 200,
            style: "margin: 0 10px 0 0;",
            enableKeyEvents: true,
            listeners: {
                "keydown" : function (field, key) {
                    if (key.getKey() == key.ENTER) {
                        var input = field;
                        var proxy = this.objectStore.getProxy();
                        proxy.extraParams.filter = input.getValue();
                        this.objectStore.load();
                    }
                }.bind(this)
            }
        });

        this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.objectStore);

        var providerStore = new Ext.data.Store({
            fields: ['value', 'name'],
            data: [
                {"value": "", "name": t("pimcore_ai_provider_default")},
                {"value": "openAi", "name": "OpenAi"},
            ],
        });
        var providerEditor = new Ext.form.ComboBox({
            store: providerStore,
            displayField: 'name',
            valueField: 'value',
            emptyText: t("pimcore_ai_provider_default"),
        });

        var typesColumns = [
            {text: t("pimcore_ai_className"), flex: 1, sortable: true, dataIndex: 'className', editable: false},
            {text: t("pimcore_ai_fieldName"), flex: 1, sortable: true, dataIndex: 'fieldName', editable: false},
            {text: t("pimcore_ai_type"), flex: 1, sortable: true, dataIndex: 'type', editable: false,
                renderer: (value) => {return t(`pimcore_ai_type_${value}`)}},
            {text: t("pimcore_ai_prompt"), flex: 3, sortable: true, dataIndex: 'prompt',
                emptyCellText: t("pimcore_ai_prompt_default"), editor: Ext.form.field.TextArea()},
            {text: t("pimcore_ai_options"), flex: 3, sortable: true, dataIndex: 'options',
                emptyCellText: t("pimcore_ai_options_default"), editor: Ext.form.field.TextArea()},
            {text: t("pimcore_ai_provider"), flex: 1, sortable: true, dataIndex: 'provider',
                emptyCellText: t("pimcore_ai_provider_default"), editor: providerEditor},
        ];

        this.objectRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            clicksToMoveEditor: 1,
        });

        var updateObjectsButton = Ext.create('Ext.Button', {
            text: t("pimcore_ai_sync_objects"),
            scale: 'medium',
            iconCls: 'pimcore_icon_update',
            handler: this.updateObjects.bind(this)
        });

        var toolbar = Ext.create('Ext.Toolbar', {
            cls: 'pimcore_main_toolbar',
            items: [
                {
                    text: t("filter") + "/" + t("search"),
                    xtype: "tbtext",
                    style: "margin: 0 10px 0 0;"
                },
                this.objectFilterField,
                '->',
                updateObjectsButton,
            ]
        });

        this.objectGrid = Ext.create('Ext.grid.Panel', {
            title: t("pimcore_ai_object_configuration"),
            autoScroll: true,
            store: this.objectStore,
            columns: {
                items: typesColumns,
                defaults: {
                    renderer: Ext.util.Format.htmlEncode
                },
            },
            selModel: Ext.create('Ext.selection.RowModel', {}),
            plugins: [
                this.objectRowEditing
            ],
            trackMouseOver: true,
            columnLines: true,
            bbar: this.pagingtoolbar,
            bodyCls: "pimcore_editable_grid",
            stripeRows: true,
            tbar: toolbar,
            flex: 1,
            viewConfig: {
                forceFit: true,
                listeners: {
                    rowupdated: this.updateObjectRows.bind(this),
                    refresh: this.updateObjectRows.bind(this)
                }
            }
        });

        this.objectStore.on("update", this.updateObjectRows.bind(this));
        this.objectGrid.on("viewready", this.updateObjectRows.bind(this));

        this.objectStore.load();

        return this.objectGrid;
    },

    updateObjectRows: function () {
        var rows = Ext.get(this.objectGrid.getEl().dom).query(".x-grid-row");

        for (var i = 0; i < rows.length; i++) {

            let dd = new Ext.dd.DropZone(rows[i], {
                ddGroup: "element",

                getTargetFromEvent: function(e) {
                    return this.getEl();
                },
            });
        }
    },

    updateObjects: function () {
        Ext.Ajax.request({
            url: '/admin/pimcore-ai/settings/sync-objects',
            method: 'POST',
            success: function(){
                Ext.getStore('pimcoreAiObjectStore').reload();
            }
        })
    },
});
