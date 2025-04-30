const settings = window.wc.wcSettings.getSetting('interswitch_data', {});
const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('Interswitch Payment Gateway', 'interswitch-payment-gateway');
const Content = () => {
    return window.wp.htmlEntities.decodeEntities(settings.description || 'Pay securely via Interswitch payment gateway');
};

const Block_Gateway = {
    name: 'interswitch',
    label: label,
    content: Object(window.wp.element.createElement)(Content, null),
    edit: Object(window.wp.element.createElement)(Content, null),
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
    icon: settings.icon || null,
};

window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);