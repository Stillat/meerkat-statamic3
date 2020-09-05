(function () {
    $(document).ready(function () {
        window.meerkat.Config.Environment.STATAMIC_CP_ROOT = window.Statamic.cp_url('/');
        window.meerkat.Config.Environment.STATAMIC_API_ROOT = window.meerkat.Types.Url.toAbsolute(
            window.Statamic.cp_url('/'), '../'
        );
        window.meerkat.Config.Environment.CONTEXT_WINDOW = window;
        window.meerkat.Config.Environment.CONTEXT_JQUERY = window.jQuery;
        window.meerkat.Config.Environment.CONTEXT_VUEJS = window.Vue;

        window.meerkatApplication = new window.meerkat.App.ControlPanelApplication();
        window.meerkatApplication.boot();
    });
})();
