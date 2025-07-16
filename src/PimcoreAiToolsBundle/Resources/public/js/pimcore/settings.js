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

pimcore.registerNS("pimcore.bundle.pimcore_ai_tools.settings");

pimcore.bundle.pimcore_ai_tools.settings = Class.create({

  initialize: function () {
    this.initializeClassStores();
    this.initializeLanguageStores();
    this.initializeProviderStores();
    this.getTabPanel();
  },

  activate: function () {
    Ext.getCmp("pimcore_panel_tabs").setActiveItem("bundle_pimcore_ai_tools");
  },

  getTabPanel: function () {
    if (!this.panel) {
      this.panel = new Ext.Panel({
        id: "bundle_pimcore_ai_tools",
        iconCls: "pimcore_ai_tools_nav_icon",
        title: t("pimcore_ai_tools_configuration"),
        border: false,
        autoScroll: true,
        flex: 1,
        layout: 'fit',
        closable: true,
        items: [Ext.create('Ext.tab.Panel', {
          layout: 'fit',
          items: [
            this.getDefaultsEditor(),
            this.getEditableRowEditor(),
            {
              title: t("pimcore_ai_tools_object_configuration"),
              layout: 'fit',
              items: [Ext.create('Ext.tab.Panel', {
                items: [
                  {
                    title: t("pimcore_ai_tools_object_configuration"),
                    layout: 'fit',
                    items: [this.getObjectRowEditor()]
                  },
                  {
                    title: t("Translations"),
                    layout: 'fit',
                    items: [this.getTranslationObjectRowEditor()]
                  }
                ]
              })]
            },
            this.getFrontendRowEditor()
          ]
        })]
      });

      var tabPanel = Ext.getCmp("pimcore_panel_tabs");
      tabPanel.add(this.panel);
      tabPanel.setActiveItem("bundle_pimcore_ai_tools");
      this.panel.on("destroy", function () {
        pimcore.globalmanager.remove("bundle_pimcore_ai_tools");
      }.bind(this));

      pimcore.layout.refresh();
    }

    return this.panel;
  },

  getDefaultsEditor: function () {
    this.defaultsForm = Ext.create('Ext.form.Panel', {
      title: t("pimcore_ai_tools_defaults_configuration"),
      url: '/admin/pimcore-ai-tools/settings/save-defaults',
      bodyPadding: 20,
      labelWidth: 150,
      autoScroll: true,
      flex: 1,
      layout: {
        type: 'container',
      },
      defaults: {anchor: '100%', labelWidth: 150},
      items: [
        this.getTextProviderEditor({
          name: 'textProvider',
          listeners: {
            render: function (combo) {
              if (!combo.getValue() || combo.getValue().trim().length === 0) {
                combo.setRawValue(t("pimcore_ai_tools_provider_default"));
              }
            },
            change: function (combo, newValue) {
              if (!newValue || newValue.trim().length === 0) {
                combo.setRawValue(t("pimcore_ai_tools_provider_default"));
              }
            }
          }
        }),{
          xtype: 'fieldset',
          title: t("pimcore_ai_tools_defaults_text_editables"),
          layout: 'anchor',
          defaults: {anchor: '100%', labelWidth: 150},
          items: [{
            fieldLabel: t("pimcore_ai_tools_defaults_editable_text_creation"),
            xtype: 'textareafield',
            name: 'editableTextCreation',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_editable_text_optimization"),
            xtype: 'textareafield',
            name: 'editableTextOptimization',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_editable_text_correction"),
            xtype: 'textareafield',
            name: 'editableTextCorrection',
          }],
        },{
          xtype: 'fieldset',
          title: t("pimcore_ai_tools_defaults_text_objects"),
          layout: 'anchor',
          defaults: {anchor: '100%', labelWidth: 150},
          items: [{
            fieldLabel: t("pimcore_ai_tools_defaults_object_text_creation"),
            xtype: 'textareafield',
            name: 'objectTextCreation',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_object_text_optimization"),
            xtype: 'textareafield',
            name: 'objectTextOptimization',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_object_text_correction"),
            xtype: 'textareafield',
            name: 'objectTextCorrection',
          }],
        },{
          xtype: 'fieldset',
          title: t("pimcore_ai_tools_defaults_text_frontend"),
          layout: 'anchor',
          defaults: {anchor: '100%', labelWidth: 150},
          items: [{
            fieldLabel: t("pimcore_ai_tools_defaults_frontend_text_creation"),
            xtype: 'textareafield',
            name: 'frontendTextCreation',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_frontend_text_optimization"),
            xtype: 'textareafield',
            name: 'frontendTextOptimization',
          },{
            fieldLabel: t("pimcore_ai_tools_defaults_frontend_text_correction"),
            xtype: 'textareafield',
            name: 'frontendTextCorrection',
          }],
        }],
      buttons: [{
        text: t("pimcore_ai_tools_defaults_form_submit"),
        formBind: true,
        handler: function() {
          var form = this.up('form').getForm();

          if (form.isValid()) {
            form.submit({
              success: function(form, action) {
                Ext.Msg.alert(
                  t("pimcore_ai_tools_defaults_form_success"),
                  t("pimcore_ai_tools_defaults_form_success_text"));
              },
              failure: function(form, action) {
                Ext.Msg.alert(
                  t("pimcore_ai_tools_defaults_form_failure"),
                  t("pimcore_ai_tools_defaults_form_failure_text"));
              }
            });
          }
        }
      }],
    });

    this.defaultsForm.getForm().load({
      url: '/admin/pimcore-ai-tools/settings/load-defaults',
    });

    return this.defaultsForm;
  },

  getEditableRowEditor: function () {
    var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
    this.editableStore = pimcore.helpers.grid.buildDefaultStore(
      '/admin/pimcore-ai-tools/settings/editable-configuration',
      [
        'id',
        'areabrick',
        'editable',
        'type',
        {name: 'prompt', allowBlank: true},
        {name: 'options', allowBlank: true},
        {name: 'provider', allowBlank: true},
      ],
      itemsPerPage,
      { storeId: 'pimcoreAiToolsEditableStore' }
    );

    this.editableFilterField = Ext.create("Ext.form.TextField", {
      width: 200,
      style: "margin: 0 10px 0 0;",
      enableKeyEvents: true,
      listeners: {
        "keydown" : function (field, key) {
          if (key.getKey() === key.ENTER) {
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
      {text: t("pimcore_ai_tools_areabrick"), flex: 1, sortable: true, dataIndex: 'areabrick', editable: false},
      {text: t("pimcore_ai_tools_editable"), flex: 1, sortable: true, dataIndex: 'editable', editable: false},
      {text: t("pimcore_ai_tools_type"), flex: 1, sortable: true, dataIndex: 'type', editable: false,
        renderer: (value) => {return t(`pimcore_ai_tools_type_${value}`)}},
      {text: t("pimcore_ai_tools_prompt"), flex: 3, sortable: true, dataIndex: 'prompt',
        emptyCellText: t("pimcore_ai_tools_prompt_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_options"), flex: 3, sortable: true, dataIndex: 'options',
        emptyCellText: t("pimcore_ai_tools_options_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_provider"), flex: 1, sortable: true, dataIndex: 'provider',
        editor: this.getTextProviderEditor(),
        renderer: function(value) {
          return (value && value.trim().length !== 0) ? value.split("\\").pop() : t("pimcore_ai_tools_provider_default");
        }
      },
    ];

    this.editableRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
      clicksToEdit: 1,
      clicksToMoveEditor: 1,
    });

    var updateEditablesButton = Ext.create('Ext.Button', {
      text: t("pimcore_ai_tools_sync_editables"),
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
      title: t("pimcore_ai_tools_editable_configuration"),
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
      url: '/admin/pimcore-ai-tools/settings/sync-editables',
      method: 'POST',
      success: function(){
        Ext.getStore('pimcoreAiToolsEditableStore').reload();
      }
    })
  },

  getObjectRowEditor: function () {
    var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
    this.objectStore = pimcore.helpers.grid.buildDefaultStore(
      '/admin/pimcore-ai-tools/settings/object-configuration',
      [
        'id',
        'className',
        'fieldName',
        'type',
        {name: 'prompt', allowBlank: true},
        {name: 'options', allowBlank: true},
        {name: 'provider', allowBlank: true},
      ],
      itemsPerPage,
      { storeId: 'pimcoreAiToolsObjectStore' }
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

    var typesColumns = [
      {text: t("pimcore_ai_tools_className"), flex: 1, sortable: true, dataIndex: 'className', editable: false},
      {text: t("pimcore_ai_tools_fieldName"), flex: 1, sortable: true, dataIndex: 'fieldName', editable: false},
      {text: t("pimcore_ai_tools_type"), flex: 1, sortable: true, dataIndex: 'type', editable: false,
        renderer: (value) => {return t(`pimcore_ai_tools_type_${value}`)}},
      {text: t("pimcore_ai_tools_prompt"), flex: 3, sortable: true, dataIndex: 'prompt',
        emptyCellText: t("pimcore_ai_tools_prompt_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_options"), flex: 3, sortable: true, dataIndex: 'options',
        emptyCellText: t("pimcore_ai_tools_options_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_provider"), flex: 1, sortable: true, dataIndex: 'provider',
        editor: this.getTextProviderEditor(),
        renderer: function(value) {
          return (value && value.trim().length !== 0) ? value.split("\\").pop() : t("pimcore_ai_tools_provider_default");
        }
      },
    ];

    this.objectRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
      clicksToEdit: 1,
      clicksToMoveEditor: 1,
    });

    var updateObjectsButton = Ext.create('Ext.Button', {
      text: t("pimcore_ai_tools_sync_objects"),
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
      url: '/admin/pimcore-ai-tools/settings/sync-objects',
      method: 'POST',
      success: function(){
        Ext.getStore('pimcoreAiToolsObjectStore').reload();
      }
    })
  },

  getFrontendRowEditor: function () {
    var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
    this.frontendStore = pimcore.helpers.grid.buildDefaultStore(
      '/admin/pimcore-ai-tools/settings/frontend-configuration',
      [
        'id',
        'name',
        'type',
        {name: 'prompt', allowBlank: true},
        {name: 'options', allowBlank: true},
        {name: 'provider', allowBlank: true},
      ],
      itemsPerPage,
      { storeId: 'pimcoreAiToolsFrontendStore' }
    );

    this.frontendFilterField = Ext.create("Ext.form.TextField", {
      width: 200,
      style: "margin: 0 10px 0 0;",
      enableKeyEvents: true,
      listeners: {
        "keydown" : function (field, key) {
          if (key.getKey() == key.ENTER) {
            var input = field;
            var proxy = this.frontendStore.getProxy();
            proxy.extraParams.filter = input.getValue();
            this.frontendStore.load();
          }
        }.bind(this)
      }
    });

    this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.frontendStore);

    var typesColumns = [
      {text: t("pimcore_ai_tools_name"), flex: 1, sortable: true, dataIndex: 'name', editable: false},
      {text: t("pimcore_ai_tools_type"), flex: 1, sortable: true, dataIndex: 'type', editable: false,
        renderer: (value) => {return t(`pimcore_ai_tools_type_${value}`)}},
      {text: t("pimcore_ai_tools_prompt"), flex: 3, sortable: true, dataIndex: 'prompt',
        emptyCellText: t("pimcore_ai_tools_prompt_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_options"), flex: 3, sortable: true, dataIndex: 'options',
        emptyCellText: t("pimcore_ai_tools_options_default"), editor: Ext.form.field.TextArea()},
      {text: t("pimcore_ai_tools_provider"), flex: 1, sortable: true, dataIndex: 'provider',
        editor: this.getTextProviderEditor(),
        renderer: function(value) {
          return (value && value.trim().length !== 0) ? value.split("\\").pop() : t("pimcore_ai_tools_provider_default");
        }
      },
    ];

    this.frontendRowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
      clicksToEdit: 1,
      clicksToMoveEditor: 1,
    });

    var updateFrontendButton = Ext.create('Ext.Button', {
      text: t("pimcore_ai_tools_sync_frontend"),
      scale: 'medium',
      iconCls: 'pimcore_icon_update',
      handler: this.updateFrontend.bind(this)
    });

    var toolbar = Ext.create('Ext.Toolbar', {
      cls: 'pimcore_main_toolbar',
      items: [
        {
          text: t("filter") + "/" + t("search"),
          xtype: "tbtext",
          style: "margin: 0 10px 0 0;"
        },
        this.frontendFilterField,
        '->',
        updateFrontendButton,
      ]
    });

    this.frontendGrid = Ext.create('Ext.grid.Panel', {
      title: t("pimcore_ai_tools_frontend_configuration"),
      autoScroll: true,
      store: this.frontendStore,
      columns: {
        items: typesColumns,
        defaults: {
          renderer: Ext.util.Format.htmlEncode
        },
      },
      selModel: Ext.create('Ext.selection.RowModel', {}),
      plugins: [
        this.frontendRowEditing
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
          rowupdated: this.updateFrontendRows.bind(this),
          refresh: this.updateFrontendRows.bind(this)
        }
      }
    });

    this.frontendStore.on("update", this.updateFrontendRows.bind(this));
    this.frontendGrid.on("viewready", this.updateFrontendRows.bind(this));

    this.frontendStore.load();

    return this.frontendGrid;
  },

  getTranslationObjectRowEditor: function () {
    var itemsPerPage = pimcore.helpers.grid.getDefaultPageSize();
    this.translationObjectStore = pimcore.helpers.grid.buildDefaultStore(
      '/admin/pimcore-ai-tools/settings/translation-object-configuration',
      [
        'id',
        'className',
        'fields',
        'standardLanguage',
        'prompt',
        'provider'
      ],
      itemsPerPage,
      { storeId: 'pimcoreAiToolsTranslationObjectStore' }
    );

    this.pagingtoolbar = pimcore.helpers.grid.buildDefaultPagingToolbar(this.translationObjectStore);

    this.translationObjectFilterField = Ext.create("Ext.form.TextField", {
      width: 200,
      style: "margin: 0 10px 0 0;",
      enableKeyEvents: true,
      listeners: {
        "keydown": function (field, key) {
          if (key.getKey() === key.ENTER) {
            var input = field;
            var proxy = this.translationObjectStore.getProxy();
            proxy.extraParams.filter = input.getValue();
            this.translationObjectStore.load();
          }
        }.bind(this)
      }
    });

    var addButton = Ext.create('Ext.Button', {
      text: t("pimcore_ai_tools_add_object_translation"),
      iconCls: 'pimcore_icon_add',
      handler: function () {
        var rec = new this.translationObjectStore.model({
          id: null,
          className: '',
          fields: '',
          standardLanguage: '',
          prompt: '',
          provider: ''
        });

        this.openTranslationObjectModal(rec);
      }.bind(this)
    });

    var toolbar = Ext.create('Ext.Toolbar', {
      cls: 'pimcore_main_toolbar',
      items: [
        {
          text: t("filter") + "/" + t("search"),
          xtype: "tbtext",
          style: "margin: 0 10px 0 0;"
        },
        this.translationObjectFilterField,
        '->',
        addButton
      ]
    });

    var objectEditor = new Ext.form.ComboBox({
      store: this.classStore,
      displayField: 'text',
      valueField: 'id',
      queryMode: 'local',
      editable: false,
      emptyText: t("pimcore_ai_tools_object_translation_class_selection"),
    });

    objectEditor.on('change', function(combo, newValue) {
      if (newValue) {
        console.log(newValue);
        Ext.Ajax.request({
          url: '/admin/class/get',
          params: { id: newValue },
          success: function(response) {
            var data = Ext.decode(response.responseText);
            var localizedFields = [];

            function extractLocalizedFields(children) {
              children.forEach(function(field) {
                if (field.fieldtype === "localizedfields") {
                  field.children.forEach(function(localizedField) {
                    if (
                      localizedField.fieldtype === "fieldcontainer" ||
                      localizedField.fieldtype === "fieldset"
                    ) {
                      localizedField.children.forEach((field) => {
                        localizedFields.push({
                          name: field.name,
                          label: field.title || field.name
                        });
                      });                    } else {
                      localizedFields.push({
                        name: localizedField.name,
                        label: localizedField.title || localizedField.name
                      });
                    }
                  });
                } else if (field.children) {
                  extractLocalizedFields(field.children);
                }
              });
            }

            if (data.layoutDefinitions && data.layoutDefinitions.children) {
              extractLocalizedFields(data.layoutDefinitions.children);
            }

            this.localizedFieldStore.loadData(localizedFields);
          }.bind(this)
        });
      }
    }.bind(this));

    this.translationObjectGrid = Ext.create('Ext.grid.Panel', {
      store: this.translationObjectStore,
      columns: [
        { text: t("pimcore_ai_tools_object_translation_className"), dataIndex: 'className', flex: 1, sortable: true, editor: this.getObjectEditor() },
        { text: t("pimcore_ai_tools_object_translation_fields"), dataIndex: 'fields', flex: 1, sortable: true, editor: this.getFieldEditor() },
        { text: t("pimcore_ai_tools_object_translation_standardLanguage"), dataIndex: 'standardLanguage', flex: 1, sortable: true, editor: this.getLanguageEditor() },
        { text: t("pimcore_ai_tools_provider"), flex: 1, sortable: true, dataIndex: 'provider',
          editor: this.getTextProviderEditor(),
          renderer: function(value) {
            return (value && value.trim().length !== 0) ? value.split("\\").pop() : t("pimcore_ai_tools_provider_default");
          }
        },
        {
          xtype: 'actioncolumn',
          width: 30,
          items: [{
            iconCls: 'pimcore_icon_delete',
            tooltip: t("delete"),
            handler: function (grid, rowIndex) {
              const rec = grid.getStore().getAt(rowIndex);
              Ext.Msg.confirm(t("delete"), t("pimcore_ai_tools_object_translations_deletion"), function (btn) {
                if (btn === 'yes') {
                  Ext.Ajax.request({
                    url: '/admin/pimcore-ai-tools/settings/delete-translation-object',
                    method: 'POST',
                    jsonData: { id: rec.get('id') },
                    success: function () {
                      grid.getStore().remove(rec);
                      grid.getStore().reload();
                    },
                    failure: function () {
                      Ext.Msg.alert(
                        t("pimcore_ai_tools_defaults_form_failure"),
                        t("pimcore_ai_tools_object_translations_delete_success"));
                    }
                  });
                }
              });
            }
          }]
        }
      ],

      selModel: Ext.create('Ext.selection.RowModel', {}),
      plugins: [{ptype: 'rowediting', clicksToEdit: 1}],
      tbar: toolbar,
      bbar: this.pagingtoolbar,
      flex: 1
    });

    return this.translationObjectGrid;
  },

  updateTranslationObjectRows: function () {
    var rows = Ext.get(this.translationObjectGrid.getEl().dom).query(".x-grid-row");

    for (var i = 0; i < rows.length; i++) {
      let dd = new Ext.dd.DropZone(rows[i], {
        ddGroup: "element",
        getTargetFromEvent: function(e) {
          return this.getEl();
        },
      });
    }
  },

  updateFrontendRows: function () {
    var rows = Ext.get(this.frontendGrid.getEl().dom).query(".x-grid-row");

    for (var i = 0; i < rows.length; i++) {

      let dd = new Ext.dd.DropZone(rows[i], {
        ddGroup: "element",

        getTargetFromEvent: function(e) {
          return this.getEl();
        },
      });
    }
  },

  updateFrontend: function () {
    Ext.Ajax.request({
      url: '/admin/pimcore-ai-tools/settings/sync-frontend',
      method: 'POST',
      success: function(){
        Ext.getStore('pimcoreAiToolsFrontendStore').reload();
      }
    })
  },

  initializeClassStores: function () {
    this.classStore = Ext.create('Ext.data.Store', {
      fields: ['text', 'id'],
      proxy: {
        type: 'ajax',
        url: '/admin/class/get-tree',
        reader: {
          type: 'json',
          rootProperty: 'data'
        }
      },
      autoLoad: true
    });



    this.localizedFieldStore = Ext.create('Ext.data.Store', {
      fields: ['name', 'label'],
      proxy: {
        type: 'memory',
        reader: {
          type: 'json'
        }
      }
    });
  },

  initializeLanguageStores: function () {
    this.languageStore = Ext.create('Ext.data.Store', {
      fields: ['code', 'name'],
      proxy: {
        type: 'ajax',
        url: '/admin/settings/get-system',
        reader: {
          type: 'json'
        }
      },
      autoLoad: false,
    });
    Ext.Ajax.request({
      url: '/admin/settings/get-system',
      success: function (response) {
        var data = Ext.decode(response.responseText);
        var validLanguages = data?.values?.general.valid_languages || [];
        var defaultLanguage = data?.values?.general.default_language || validLanguages[0];

        var languageOptions = validLanguages.map(function (lang) {
          return { code: lang, name: lang.toUpperCase() };
        });

        this.languageStore.loadData(languageOptions);
        this.defaultLanguage = defaultLanguage;

      }.bind(this),
    });
  },

  initializeProviderStores: function () {
    if (!Ext.ClassManager.get('textProviderModel')) {
      Ext.define('textProviderModel', {
        extend: 'Ext.data.Model',
        fields: ['value', 'name'],
      });
    }

    this.textProviderStore = new Ext.data.Store({
      model: 'textProviderModel',
      proxy: {
        type: 'ajax',
        url: '/admin/pimcore-ai-tools/settings/get-text-providers',
        reader: {
          type: 'json',
          transform: function (data) {
            let providers = [];
            Object.keys(data).forEach(key => {
              if (!isNaN(key)) {
                providers.push(data[key]);
              }
            });
            return { data: providers };
          },
          rootProperty: 'data'
        },
      },
      autoLoad: true,
    });

    if (!Ext.ClassManager.get('imageProviderModel')) {
      Ext.define('imageProviderModel', {
        extend: 'Ext.data.Model',
        fields: ['value', 'name'],
      });
    }

    this.imageProviderStore = new Ext.data.Store({
      model: 'imageProviderModel',
      proxy: {
        type: 'ajax',
        url: '/admin/pimcore-ai-tools/settings/get-image-providers',
        reader: {
          type: 'json'
        },
      },
      fields: ['value', 'name'],
      autoLoad: true,
    });
  },

  getObjectEditor: function (config) {
    return Ext.create('Ext.form.ComboBox', Ext.apply({
      fieldLabel: t("className"),
      store: this.classStore,
      displayField: 'text',
      valueField: 'id',
      queryMode: 'local',
      editable: false,
      allowBlank: false,
      name: 'className',
      listeners: {
        change: function (combo, newValue) {
          if (newValue) {
            Ext.Ajax.request({
              url: '/admin/class/get',
              params: { id: newValue },
              success: function (response) {
                var data = Ext.decode(response.responseText);
                var localizedFields = [];

                function extractLocalizedFields(children) {
                  children.forEach(function (field) {
                    if (field.fieldtype === "localizedfields") {
                      field.children.forEach(function (localizedField) {
                        if (
                          localizedField.fieldtype === "fieldcontainer" ||
                          localizedField.fieldtype === "fieldset"
                        ) {
                          localizedField.children.forEach((field) => {
                            localizedFields.push({
                              name: field.name,
                              label: field.title || field.name
                            });
                          });
                        } else {
                          localizedFields.push({
                            name: localizedField.name,
                            label: localizedField.title || localizedField.name
                          });
                        }
                      });
                    } else if (field.children) {
                      extractLocalizedFields(field.children);
                    }
                  });
                }

                if (data.layoutDefinitions && data.layoutDefinitions.children) {
                  extractLocalizedFields(data.layoutDefinitions.children);
                }

                this.localizedFieldStore.loadData(localizedFields);
              }.bind(this)
            });
          }
        }.bind(this)
      }
    }, config));
  },

  getFieldEditor: function (config) {
    return Ext.create('Ext.form.ComboBox', Ext.apply({
      fieldLabel: t("pimcore_ai_tools_fieldName"),
      store: this.localizedFieldStore,
      displayField: 'label',
      valueField: 'name',
      queryMode: 'local',
      editable: false,
      multiSelect: true,
      allowBlank: false,
      name: 'field',
    }, config));
  },

  getLanguageEditor: function (config) {
    return Ext.create('Ext.form.ComboBox', Ext.apply({
      fieldLabel: t("pimcore_ai_tools_object_translation_standardLanguage"),
      store: this.languageStore,
      displayField: 'name',
      valueField: 'code',
      queryMode: 'local',
      editable: false,
      allowBlank: false,
      name: 'standardLanguage',
      value: this.defaultLanguage || null
    }, config));
  },

  getTextProviderEditor: function (config = {}) {
    return Ext.create('Ext.form.ComboBox', Ext.apply({
      fieldLabel: config.showLabel ? t("pimcore_ai_tools_provider") : null,
      store: this.textProviderStore,
      displayField: 'name',
      valueField: 'value',
      queryMode: 'local',
      editable: false,
      forceSelection: true,
      allowBlank: true,
      emptyText: t("pimcore_ai_tools_provider_default"),
      width: 400,
      listConfig: {
        minWidth: 200
      },
      name: 'provider',
      submitValue: true,
      getSubmitData: function() {
        let obj = {};
        obj[this.getName()] = this.getSubmitValue();
        return obj;
      },
      renderer: function (value) {
        if (!value || value.length === 0) {
          return t("pimcore_ai_tools_provider_default");
        }
        return value.split("\\").pop();
      }
    }, config));
  },

  openTranslationObjectModal: function (record) {
    var objectCombo = this.getObjectEditor({ value: record.get('className') || null });
    var fieldCombo = this.getFieldEditor({ value: record.get('fields') || null });
    var providerCombo = this.getTextProviderEditor({ value: record.get('provider') || null });

    var form = Ext.create('Ext.form.Panel', {
      bodyPadding: 10,
      items: [
        objectCombo,
        fieldCombo,
        this.getLanguageEditor({ value: record.get('standardLanguage') || null }),
        providerCombo
      ]
    });

    var window = Ext.create('Ext.window.Window', {
      title: t("pimcore_ai_tools_add_object_translation_title"),
      modal: true,
      layout: 'fit',
      width: 500,
      items: form,
      buttons: [
        {
          text: t("pimcore_ai_tools_object_translations_submit"),
          handler: function () {
            var values = form.getValues();
            var selectedObject = objectCombo.findRecordByValue(values.className);
            console.log('selectedObject', selectedObject);
            console.log('className', values.className);
            values.className = selectedObject ? selectedObject.get('text') : values.className;
            var selectedFields = values.field;
            if (Array.isArray(selectedFields)) {
              values.field = selectedFields.map(f => {
                var fieldRecord = fieldCombo.findRecordByValue(f);
                return fieldRecord ? fieldRecord.get('label') : f;
              });
            } else {
              var fieldRecord = fieldCombo.findRecordByValue(selectedFields);
              values.field = fieldRecord ? fieldRecord.get('label') : selectedFields;
            }

            var selectedProvider = providerCombo.findRecordByValue(values.provider);
            values.provider = selectedProvider ? selectedProvider.get('name') : '';

            Ext.Ajax.request({
              url: '/admin/pimcore-ai-tools/settings/save-translation-object',
              method: 'POST',
              jsonData: values,
              success: function (response) {
                var responseData = Ext.decode(response.responseText);
                if (responseData.success) {
                  Ext.Msg.alert(
                    t("pimcore_ai_tools_defaults_form_success"),
                    t("pimcore_ai_tools_object_translations_success_text"));
                  if (window.isVisible()) {
                    window.close();
                  }
                  this.translationObjectStore.reload();
                } else {
                  Ext.Msg.alert(
                    t("pimcore_ai_tools_defaults_form_failure"),
                    responseData.error || t("pimcore_ai_tools_object_translations_failure_text"));
                }
              }.bind(this),
              failure: function (response) {
                Ext.Msg.alert(
                  t("pimcore_ai_tools_defaults_form_failure"),
                  t("pimcore_ai_tools_object_translations_failure_text"));
              }
            });
          }.bind(this)
        },
        {
          text: t("pimcore_ai_tools_object_translations_cancel"),
          handler: function () {
            window.close();
          }
        }
      ]
    });

    window.show();
  }
});
