class AddonConfigHook {

  static run(app) {
    app.controlPanel.addons().addLinkToPackage('stillat/meerkat', app.url('addons/meerkat/settings'));
  }

}

export default AddonConfigHook;
