
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        toggleScans: true,
        show: {
            form: false,
            report: false,
        },
        formRequest: {
            url : 'https://www.hackmanit.de',
            whitelist: '',
            scan : {
                images: true,
                scripts: true,
                links: true,
                media: true,
                area: true,
                frames: true,
            },
            doNotCrawl: false,
            limitOn: false,
            limit: '',
            ignoreTLS: true,
            proxy: '',
            proxyAddress: ''
        },
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            this.formRequest.proxyAddress = "http://" + response.data.HOST_IP + ":8888",
            this.formRequest.limit = response.data.LIMIT,
            this.show.load = false,
            this.show.form = true,
        ]);
    },
    methods: {
        sendRequest () {
            axios.post()
        },
    }
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
