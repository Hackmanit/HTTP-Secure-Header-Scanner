require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        show: {
            load: true,
            form: null,
            result: null
        },
        loadingMessage: "",
        toggleScans: true,

        singleRequest: {
            url: 'https://www.hackmanit.de'
        },

        result: {
            siteRating: '',
        },

        multipleRequest: {

        },

        crawlRequest: {
            url : 'https://www.hackmanit.de',
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
        }
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            app.crawlRequest.proxyAddress = "http://" + response.data.HOST_IP + ":8080",
            app.crawlRequest.limit = response.data.LIMIT,
            app.crawlRequest.scan.customJson = response.data.CUSTOM_JSON,
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
    },
    filters: {
    },
    methods: {
        getSingleReport() {
            app.show.load = true;
            app.show.form = false;
            this.loadingMessage = "Requesting your report... just a moment, pls."
            axios
                .get("/api/v1/rate?url=" + this.singleRequest.url)
                .then(response => [
                    this.result = response.data,
                    app.show.load = false,
                    app.show.result = true
                ])
                .catch(error => [
                    alert(error),

                ]) ;
        },
        getFirst(someString) {
            return someString.charAt(0);
        },
        nl2br(someString) {
            return (someString + '').replace(/\\n/g, "<br>");
        }

    }
});
