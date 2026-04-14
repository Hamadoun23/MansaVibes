import './bootstrap';

import Alpine from 'alpinejs';
import registerMeasurementTemplates from './measurementTemplates';
import registerInventoryFormTemplates from './inventoryFormTemplates';
import registerInventoryItemForm from './inventoryItemForm';
import registerOrderFormLines from './orderFormLines';

window.Alpine = Alpine;

registerMeasurementTemplates(Alpine);
registerInventoryFormTemplates(Alpine);
registerInventoryItemForm(Alpine);
registerOrderFormLines(Alpine);

Alpine.start();

let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredInstallPrompt = e;
  window.dispatchEvent(new CustomEvent('mansavibes:pwa-installable'));
});

window.mansaInstallPwa = async function mansaInstallPwa() {
  if (!deferredInstallPrompt) {
    return { ok: false, reason: 'unavailable' };
  }
  deferredInstallPrompt.prompt();
  const { outcome } = await deferredInstallPrompt.userChoice;
  deferredInstallPrompt = null;
  return { ok: outcome === 'accepted' };
};

if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker
      .register('/sw.js', { scope: '/' })
      .then((reg) => {
        reg.addEventListener('updatefound', () => {
          const nw = reg.installing;
          if (!nw) return;
          nw.addEventListener('statechange', () => {
            if (nw.state === 'installed' && navigator.serviceWorker.controller) {
              window.dispatchEvent(new CustomEvent('mansavibes:pwa-update'));
            }
          });
        });
      })
      .catch(() => {});
  });
}
