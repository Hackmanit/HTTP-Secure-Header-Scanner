require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        toggleScans: true,
        show: {
            load: true,
            form: null,
            report: null,
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
        report: "",
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            this.formRequest.proxyAddress = "http://" + response.data.HOST_IP + ":8888",
            this.formRequest.limit = response.data.LIMIT,
            this.formRequest.scan.customJson = response.data.CUSTOM_JSON,
            this.show.load = false,
            this.show.form = true,
        ]);
    },
    watch: {
        toggleScans: function () {
            this.formRequest.scan.anchor = this.toggleScans;
            this.formRequest.scan.images = this.toggleScans;
            this.formRequest.scan.frames = this.toggleScans;
            this.formRequest.scan.area = this.toggleScans;
            this.formRequest.scan.media = this.toggleScans;
            this.formRequest.scan.scripts = this.toggleScans;
            this.formRequest.scan.links = this.toggleScans;
        }
    },
    methods: {
        sendRequest (event) {
            event.preventDefault();
            this.show.form = false;
            this.show.load = true;
            console.log(JSON.stringify(this.formRequest.toString()));
            axios.post("/", this.formRequest)
                .then(function (response) {
                   console.log(response.data);
                   this.show.report = true;

                   this.report = response.data;
                });
        },
    }
});
