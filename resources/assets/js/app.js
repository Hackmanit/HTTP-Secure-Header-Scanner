
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */

//Vue.component('example', require('./components/Example.vue'));

var app = new Vue({
    el: '#app',
    data: {

        proxy: '',
    },
    /*
    methods: {
        getReport: _.debounce(function() {
          var app = this;
          app.message = "Requesting report...",
          axios.get('http://lednerb.dev/api/v1/analyze?url=' + app.url)
               .then(function (response) {
                   app.test = response.data;
                   response.data.forEach(function (report) {
                       if (report.status === "success") {
                           app.message = "Success!";
                           app.reports.unshift(report);
                       }
                       else
                           app.message = "SHIT!";
                   });
              });
        }, 500)
    }*/
});

$(document).ready(function () {
    $('#accordion').on('hidden.bs.collapse', toggleChevron);
    $('#accordion').on('shown.bs.collapse', toggleChevron);
})

function toggleChevron(e) {
    $(e.target)
        .prev('.panel-heading')
        .find("i.indicator")
        .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
}
