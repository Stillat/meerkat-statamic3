import BlueprintHook from './Statamic/Hooks/blueprintHook';
import AddonConfigHook from './Statamic/Hooks/addonConfigHook';

let controlPanelHooks = [
  {
    'path': /^(.*?)\/fields\/blueprints(.*?)$/,
    'uses': BlueprintHook
  },
  {
    'path': /^(.*?)\/addons(.*?)$/,
    'uses': AddonConfigHook
  }
];

export {
  controlPanelHooks
};
