require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        show: {
            load: true,
            form: null,
            singleReport: null,
            fullReport: null
        },
        loadingMessage: "",
        toggleScans: true,

        singleRequest: {
            url: 'https://www.hackmanit.de'
        },

        singleReport: {
            siteRating: '',
        },

        fullReport: {
            fullRating: '',
            ratings: {},
            amountUrlsTotal: '-',
            amountGeneratedReports: '-'
        },

        multipleRequest: {
            urls :"https://www.hackmanit.de/\nhttps://www.hackmanit.de/publikationen.html\nhttps://www.hackmanit.de/karriere.html\nhttps://www.hackmanit.de/img/christian_mainka.jpg\nhttps://www.hackmanit.de/impressum-en.html",
        },

        crawlRequest: {
            url : 'https://www.hackmanit.de',
            whitelist: '',
            scan : {
                anchor: true,
                image: false,
                script: false,
                link: false,
                media: false,
                area: false,
                frame: false,
                custom: false,
                customJson: ""
            },
            expertMode: false,
            proxy: false,
            proxyAdress: '',
            ignoreTLS: true,
        }
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            app.crawlRequest.proxyAdress = "http://" + response.data.HOST_IP + ":8080",
            app.crawlRequest.limit = response.data.LIMIT,
            app.crawlRequest.scan.customJson = response.data.CUSTOM_JSON,
            app.show.load = false,
            app.show.form = true,
        ]);
    },
    watch: {
        toggleScans: function () {
            app.crawlRequest.scan.anchor = app.toggleScans;
            app.crawlRequest.scan.image = app.toggleScans;
            app.crawlRequest.scan.frame = app.toggleScans;
            app.crawlRequest.scan.area = app.toggleScans;
            app.crawlRequest.scan.media = app.toggleScans;
            app.crawlRequest.scan.script = app.toggleScans;
            app.crawlRequest.scan.link = app.toggleScans;
        }
    },
    methods: {
        getSingleReport() {
            app.show.load = true;
            app.show.form = false;
            this.loadingMessage = "Requesting your report... just a moment, pls."
            axios
                .get("/api/v1/rate?url=" + this.singleRequest.url)
                .then(response => [
                    app.singleReport = response.data,
                    app.show.load = false,
                    app.show.singleReport = true
                ])
                .catch(error => [
                    alert(error),
                ]) ;
        },
        getMultipleReport() {
            app.loadingMessage = "We're dispatching your request to the backend.<br>This should take just a moment.";
            app.show.form = false;
            app.show.load = true;

            axios
                .post('/api/v1/multiple', {
                    urls: app.multipleRequest.urls.split('\n')
                })
                .then(response => [
                    app.fullReport.reportUrl = response.data.reportUrl,
                    app.loadingMessage = "",
                    app.getReportDetails()
                ])
                .catch(error => [
                   alert(error)
                ]);
        },
        getCrawledReport() {
            app.loadingMessage = "We're dispatching your request to the backend.<br>This should take just a moment.";
            app.show.form = false;
            app.show.load = true;

            /*axios
                .post('/api/v1/multiple', {
                    urls: app.multipleRequest.urls.split('\n')
                })
                .then(response => [
                    app.fullReport.reportUrl = response.data.reportUrl,
                    app.loadingMessage = "",
                    app.getReportDetails()
                ])
                .catch(error => [
                    alert(error)
                ]);*/
        },
        getReportDetails() {
            axios.get(app.fullReport.reportUrl).then(function(response){
                app.doStuffOrReload(response);
            });
        },

        doStuffOrReload(response) {
            if (response.data.status.localeCompare("finished") === 0) {
                app.show.load = false;
                app.show.fullReport = true;

                app.fullReport = response.data.fullReport;
                app.fullReport.amountUrlsTotal = response.data.amountUrlsTotal;
                app.fullReport.amountGeneratedReports = response.data.amountGeneratedReports;
            }
            else {
                app.fullReport.amountUrlsTotal = response.data.amountUrlsTotal;
                app.fullReport.amountGeneratedReports = response.data.amountGeneratedReports;
                app.loadingMessage = "Generating reports: "
                    + (app.fullReport.amountGeneratedReports != null ? app.fullReport.amountGeneratedReports : '-')
                    + " of "
                    + (app.fullReport.amountUrlsTotal != null ? app.fullReport.amountUrlsTotal : '-');
                setTimeout(app.getReportDetails, 3000);
            }
        },
        getFirst(someString) {
            return someString.charAt(0);
        },
        nl2br(someString) {
            return (someString + '').replace(/\\n/g, "<br>");
        },
        newScan() {
            app.show.load = false;
            app.show.singleReport = false;
            app.show.fullReport = false;
            app.show.form = true;
        }

    }
});
