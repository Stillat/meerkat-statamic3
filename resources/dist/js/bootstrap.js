(function () {
    window.Statamic.booting(function () {
        window.meerkat.Config.Environment.MarkdownHandler = window.markdown;
        window.meerkat.Config.Environment.StatamicCpRoot = window.Statamic.cp_url('/');
        window.meerkat.Config.Environment.StatamicApiRoot = window.meerkat.Types.Url.toAbsolute(
            window.Statamic.cp_url('/'), '../'
        );
        window.meerkat.Config.Environment.ContextJquery = window.jQuery;
        window.meerkat.Config.Environment.ContextVueJs = window.Vue;
        window.meerkat.Config.Environment.ContextComponentRegister = window.Statamic.$components.register;

        window.meerkatApplication = new window.meerkat.App.ControlPanelApplication();
        window.meerkat.App.ControlPanelApplication.Instance = window.meerkatApplication;
        window.meerkatApplication.boot();
    });
})();
