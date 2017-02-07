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
            url : '',
            whitelist: '',
            scan : {
                anchor: true,
                image: true,
                script: true,
                link: true,
                media: true,
                area: true,
                frame: true,
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
            app.formRequest.scan.image = app.toggleScans;
            app.formRequest.scan.frame = app.toggleScans;
            app.formRequest.scan.area = app.toggleScans;
            app.formRequest.scan.media = app.toggleScans;
            app.formRequest.scan.script = app.toggleScans;
            app.formRequest.scan.link = app.toggleScans;
        }
    }
});
