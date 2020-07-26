(function () {
    $(document).ready(function () {
        window.meerkat.Config.Environment.STATAMIC_CP_ROOT = window.Statamic.cp_url('/');
        window.meerkatApplication = new window.meerkat.App.Application();
        window.meerkatApplication.boot();
    });
})();
