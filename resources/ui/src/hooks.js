import BlueprintHook from './Statamic/Hooks/blueprintHook';

let controlPanelHooks = [
  {
    'path': /^(.*?)\/fields\/blueprints(.*?)$/,
    'uses': BlueprintHook
  }
];

export {
  controlPanelHooks
};
