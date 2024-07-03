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

pimcore.registerNS("pimcore.bundle.pimcore_ai_tools.startup");

pimcore.bundle.pimcore_ai_tools.startup = Class.create({
    initialize: function () {
        document.addEventListener(pimcore.events.preMenuBuild, this.preMenuBuild.bind(this));
    },

    preMenuBuild: function (e) {
        let menu = e.detail.menu;

        const user = pimcore.globalmanager.get('user');
        const perspectiveCfg = pimcore.globalmanager.get("perspective");

        if (menu.settings && user.isAllowed("pimcore_ai_tools") && perspectiveCfg.inToolbar("settings.pimcore_ai_tools")) {
            menu.settings.items.push({
                text: t("pimcore_ai_tools_navigation"),
                iconCls: "pimcore_ai_tools_nav_icon",
                priority: 924,
                itemId: 'pimcore_menu_pimcore_ai_tools',
                handler: this.editPimcoreAiTools,
            });
        }
    },

    editPimcoreAiTools: function() {
        try {
            pimcore.globalmanager.get("bundle_pimcore_ai_tools").activate();
        } catch (e) {
            pimcore.globalmanager.add("bundle_pimcore_ai_tools", new pimcore.bundle.pimcore_ai_tools.settings());
        }
    }
});

const pimcoreBundlePimcoreAiTools = new pimcore.bundle.pimcore_ai_tools.startup();
