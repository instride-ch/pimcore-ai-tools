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
                closable:true,
                items: [this.getRowEditor()]
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

    getRowEditor: function () {

        var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
        this.store = pimcore.helpers.grid.buildDefaultStore(
            // Routing.generate('pimcore_ai_settings_object_configuration'),
            '/admin/pimcore-ai/object-configuration',
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
                        var proxy = this.store.getProxy();
                        proxy.extraParams.filter = input.getValue();
                        this.store.load();
                    }
                }.bind(this)
            }
        });

        this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.store);

        var typesColumns = [
            {text: t("pimcore_ai_className"), flex: 100, sortable: true, dataIndex: 'className', editable: false},
            {text: t("pimcore_ai_fieldName"), flex: 100, sortable: true, dataIndex: 'fieldName', editable: false},
            {text: t("pimcore_ai_type"), flex: 100, sortable: true, dataIndex: 'type', editable: false, editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_prompt"), flex: 300, sortable: true, dataIndex: 'prompt', editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_options"), flex: 300, sortable: true, dataIndex: 'options', editor: new Ext.form.TextField({})},
            {text: t("pimcore_ai_provider"), flex: 100, sortable: true, dataIndex: 'provider', editor: new Ext.form.TextField({})},
            {
                xtype: 'actioncolumn',
                menuText: t('delete'),
                width: 30,
                items: [{
                    tooltip: t('delete'),
                    icon: "/bundles/pimcoreadmin/img/flat-color-icons/delete.svg",
                    handler: function (grid, rowIndex) {
                        let data = grid.getStore().getAt(rowIndex);
                        pimcore.helpers.deleteConfirm(t('pimcore_ai'), data.data.id, function () {
                            grid.getStore().removeAt(rowIndex);
                            this.updateRows();
                        }.bind(this));

                    }.bind(this)
                }]
            }
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

        this.grid = Ext.create('Ext.grid.Panel', {
            autoScroll: true,
            store: this.store,
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
                    rowupdated: this.updateRows.bind(this),
                    refresh: this.updateRows.bind(this)
                }
            }
        });

        this.store.on("update", this.updateRows.bind(this));
        this.grid.on("viewready", this.updateRows.bind(this));

        this.store.load();
        console.log(this.store.data.items);

        return this.grid;
    },

    updateRows: function () {

        var rows = Ext.get(this.grid.getEl().dom).query(".x-grid-row");

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
