/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 *
 */

pimcore.registerNS('coreshop.provider.gateways.wallee');
coreshop.provider.gateways.wallee = Class.create(coreshop.provider.gateways.abstract, {


    getLayout: function (config) {
        return [
            {
                xtype: 'textfield',
                fieldLabel: t('wallee.config.user_id'),
                name: 'gatewayConfig.config.user_id',
                length: 255,
                value: config.user_id ? config.user_id : ""
            },
            {
                xtype: 'textfield',
                fieldLabel: t('wallee.config.space_id'),
                name: 'gatewayConfig.config.space_id',
                length: 255,
                value: config.space_id ? config.space_id : ""
            },
            {
                xtype: 'textfield',
                fieldLabel: t('wallee.config.secret'),
                name: 'gatewayConfig.config.secret',
                length: 255,
                value: config.secret ? config.secret : ""
            },
        ];
    }
});
