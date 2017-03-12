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

        singleRequest: {
            url: ''
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
            urls: ''
        },

        crawlRequest: {
            url : '',
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

            downloadLinksUrl : '',
            amountUrlsCrawled : '-',
            amountUrlsToCrawl : '-',
            crawledLinks: ''
        }
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            app.crawlRequest.proxyAdress = "http://" + response.data.HOST_IP + ":8080",
            app.crawlRequest.limit = response.data.LIMIT,
            app.show.load = false,
            app.show.form = true,
        ]);
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
        getMultipleReport(runWithCrawledLinks = false) {
            app.loadingMessage = "We're dispatching your request to the backend.<br>This should take just a moment.";
            app.show.form = false;
            app.show.load = true;

            axios
                .post('/api/v1/multiple', {
                    urls: runWithCrawledLinks ? app.crawlRequest.crawledLinks : app.multipleRequest.urls.split('\n')
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

            axios
                .post('/api/v1/crawl', {
                    url: app.crawlRequest.url,

                    whitelist: app.crawlRequest.whitelist ? app.crawlRequest.whitelist.split('\n') : null,

                    anchor: app.crawlRequest.scan.anchor,
                    image: app.crawlRequest.scan.image,
                    media: app.crawlRequest.scan.media,
                    link: app.crawlRequest.scan.link,
                    script: app.crawlRequest.scan.script,
                    area: app.crawlRequest.scan.area,
                    frame: app.crawlRequest.scan.frame,

                    ignoreTlsErrors: app.crawlRequest.expertMode ? app.crawlRequest.ignoreTLS : false,
                    customElements: (app.crawlRequest.expertMode && app.crawlRequest.custom) ? app.crawlRequest.scan.customJson : null,
                    proxy: (app.crawlRequest.expertMode && app.crawlRequest.proxy) ? app.crawlRequest.proxyAdress : null,
                    limit: app.crawlRequest.limit
                })
                .then(response => [
                    app.loadingMessage = "Crawler job was successfully dispatched.",
                    app.crawlRequest.downloadLinksUrl = response.data.linksUrl,
                    app.getCrawledLinks()
                ])
                .catch(error => [
                    alert(error)
                ]);
        },

        getCrawledLinks() {
            axios
                .get(app.crawlRequest.downloadLinksUrl)
                .then(function(response){
                    app.dispatchReportWithCrawledLinksOrRelaod(response);
                });
        },

        dispatchReportWithCrawledLinksOrRelaod(response) {
            if (response.data.status.localeCompare("crawlerFinished") === 0) {
                app.loadingMessage = "Crawler has finished.<br>In the next step the report will be generated.";
                app.crawlRequest.crawledLinks = response.data.links;
                app.getMultipleReport(true);
            }
            else {
                app.crawlRequest.amountUrlsCrawled = response.data.amountUrlsCrawled;
                app.crawlRequest.amountUrlsToCrawl = response.data.amountUrlsToCrawl;
                app.loadingMessage = "Crawled Urls: "
                    + ((app.crawlRequest.amountUrlsCrawled !== null) ? app.crawlRequest.amountUrlsCrawled : '-')
                    + "<br>"
                    + "Not crawled yet: "
                    + (app.crawlRequest.amountUrlsToCrawl !== null ? app.crawlRequest.amountUrlsToCrawl : '-')
                    + "<br>"
                    + "Limit: " + app.crawlRequest.limit;
                setTimeout(app.getCrawledLinks, 3000);
            }
        },

        getReportDetails() {
            axios.get(app.fullReport.reportUrl).then(function(response){
                app.displayReportOrReload(response);
            });
        },

        displayReportOrReload(response) {
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
                    + (app.fullReport.amountGeneratedReports !== null ? app.fullReport.amountGeneratedReports : '-')
                    + " of "
                    + (app.fullReport.amountUrlsTotal !== null ? app.fullReport.amountUrlsTotal : '-');
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
