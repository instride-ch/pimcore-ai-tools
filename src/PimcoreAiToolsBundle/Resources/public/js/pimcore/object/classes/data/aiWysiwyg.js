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

pimcore.registerNS("pimcore.object.classes.data.aiWysiwyg");
/**
 * @private
 */
pimcore.object.classes.data.aiWysiwyg = Class.create(pimcore.object.classes.data.data, {

    type: "aiWysiwyg",
    /**
     * define where this datatype is allowed
     */
    allowIn: {
        object: true,
        objectbrick: true,
        fieldcollection: true,
        localizedfield: true,
        classificationstore : true,
        block: true,
        encryptedField: true
    },

    initialize: function (treeNode, initData) {
        this.type = "aiWysiwyg";

        this.initData(initData);

        // overwrite default settings
        this.availableSettingsFields = ["name","title","tooltip","mandatory","noteditable","invisible",
                                        "visibleGridView","visibleSearch","style"];

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("ai_wysiwyg");
    },

    getGroup: function () {
        return "other";
    },

    getIconClass: function () {
        return "pimcore_ai_tools_icon_ai_wysiwyg_grey";
    },

    getLayout: function ($super) {

        $super();

        this.specificPanel.removeAll();
        var specificItems = this.getSpecificPanelItems(this.datax);
        this.specificPanel.add(specificItems);

        return this.layout;
    },

    getSpecificPanelItems: function (datax, inEncryptedField) {
        const stylingItems = [
            {
                xtype: "textfield",
                fieldLabel: t("width"),
                name: "width",
                value: datax.width
            },
            {
                xtype: "displayfield",
                hideLabel: true,
                value: t('width_explanation')
            },
            {
                xtype: "textfield",
                fieldLabel: t("height"),
                name: "height",
                value: datax.height
            },
            {
                xtype: "displayfield",
                hideLabel: true,
                value: t('height_explanation')
            }
        ];

        if (this.isInCustomLayoutEditor()) {
            return stylingItems;
        }

        return stylingItems.concat([
            {
                xtype: "textarea",
                fieldLabel: t("editor_configuration"),
                name: "toolbarConfig",
                value: datax.toolbarConfig,
                width: 400,
                height: 150
            },
            {
                xtype: "checkbox",
                fieldLabel: t("exclude_from_search_index"),
                name: "excludeFromSearchIndex",
                checked: datax.excludeFromSearchIndex
            },
            {
                xtype: "textfield",
                fieldLabel: t("max_characters"),
                name: "maxCharacters",
                value: datax.maxCharacters
            }

        ]);
    },

    applySpecialData: function(source) {
        if (source.datax) {
            if (!this.datax) {
                this.datax =  {};
            }
            Ext.apply(this.datax,
                {
                    width: source.datax.width,
                    height: source.datax.height,
                    toolbarConfig: source.datax.toolbarConfig,
                    excludeFromSearchIndex : source.datax.excludeFromSearchIndex,
                    maxCharacters : source.datax.maxCharacters
                });
        }
    }
});
