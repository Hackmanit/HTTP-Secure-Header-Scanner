require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        toggleScans: true,
        show: {
            load: true,
            form: null,
        },
        formRequest: {
            url : 'https://www.hackmanit.de',
            whitelist: '',
            scan : {
                anchor: true,
                images: true,
                scripts: true,
                links: true,
                media: true,
                area: true,
                frames: true,
                custom: false,
                customJson: ''
            },
            doNotCrawl: false,
            limitOn: false,
            limit: '',
            ignoreTLS: true,
            proxy: '',
            proxyAddress: ''
        },
        report: {
            'id': null,
            'status': null,
            'data': null
        }
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            app.formRequest.proxyAddress = "http://" + response.data.HOST_IP + ":8080",
            app.formRequest.limit = response.data.LIMIT,
            app.formRequest.scan.customJson = response.data.CUSTOM_JSON,
            app.show.load = false,
            app.show.form = true,
        ]);
    },
    watch: {
        toggleScans: function () {
            app.formRequest.scan.anchor = app.toggleScans;
            app.formRequest.scan.images = app.toggleScans;
            app.formRequest.scan.frames = app.toggleScans;
            app.formRequest.scan.area = app.toggleScans;
            app.formRequest.scan.media = app.toggleScans;
            app.formRequest.scan.scripts = app.toggleScans;
            app.formRequest.scan.links = app.toggleScans;
        }
    },
    methods: {

    }
});
