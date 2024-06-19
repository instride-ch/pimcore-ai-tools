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
                layout: "fit",
                closable: true,
                items: [this.getObjectRowEditor()]
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

    getObjectRowEditor: function () {

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        this.objectStore = pimcore.helpers.grid.buildDefaultStore(
            '/admin/pimcore-ai/settings/object-configuration',
            [
                'id', 'className', 'fieldName', 'type', {name: 'prompt', allowBlank: true},
                {name: 'options', allowBlank: true}, {name: 'provider', allowBlank: true}
            ],
            itemsPerPage,
        );

        this.filterField = Ext.create("Ext.form.TextField", {
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

        var typesColumns = [
            {text: t("pimcore_ai_className"), flex: 100, sortable: true, dataIndex: 'className', editable: false},
            {text: t("pimcore_ai_fieldName"), flex: 100, sortable: true, dataIndex: 'fieldName', editable: false},
            {text: t("pimcore_ai_type"), flex: 100, sortable: true, dataIndex: 'type', editable: false, editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_prompt"), flex: 300, sortable: true, dataIndex: 'prompt', editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_options"), flex: 300, sortable: true, dataIndex: 'options', editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_provider"), flex: 100, sortable: true, dataIndex: 'provider', editor: new Ext.form.TextField({})},
        ];

        this.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1,
            clicksToMoveEditor: 1,
        });

        var toolbar = Ext.create('Ext.Toolbar', {
            cls: 'pimcore_main_toolbar',
            items: [
                {
                    text: t("filter") + "/" + t("search"),
                    xtype: "tbtext",
                    style: "margin: 0 10px 0 0;"
                },
                this.filterField
            ]
        });

        this.objectGrid = Ext.create('Ext.grid.Panel', {
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
                this.rowEditing
            ],

            trackMouseOver: true,
            columnLines: true,
            bbar: this.pagingtoolbar,
            bodyCls: "pimcore_editable_grid",
            stripeRows: true,
            tbar: toolbar,
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
});
