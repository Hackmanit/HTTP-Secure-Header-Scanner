
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
    watch: {
        toggleScans: function () {
            // TODO: Per Schleife gescheit machen.
            this.formRequest.scan.images = this.toggleScans;
            this.formRequest.scan.frames = this.toggleScans;
            this.formRequest.scan.area = this.toggleScans;
            this.formRequest.scan.media = this.toggleScans;
            this.formRequest.scan.scripts = this.toggleScans;
            this.formRequest.scan.links = this.toggleScans;
        }
    },
    methods: {
        sendRequest () {
            axios.post()
                .then(function (response) {
                   // Do cool things
                });
        },
    }
});
