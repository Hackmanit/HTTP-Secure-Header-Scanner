require('./bootstrap');

import ReportAccordionPanel from "./components/ReportAccordionPanel.vue";

var app = new Vue({
    el: '#app',

    components: { "reportaccordionpanel" : ReportAccordionPanel  },
    data: {
        show: {
            load: true,
            report: null,
        },
        fullreport: {},
        headerRatings: {},
    },

    methods: {
        getReportDetails() {
            axios.get(window.location.pathname + '/details').then(function(response){
                app.doStuffOrReload(response);
            });
        },

        doStuffOrReload(response) {
            if (response.data.status.localeCompare("finished") === 0) {
                this.show.load = false;
                this.show.report = true;

                this.fullreport = response.data.fullreport;
                this.headerRatings = response.data.headerRatings;
            }
            else
                setTimeout(app.getReportDetails, 5000);
        }
    },
    mounted() {
        this.getReportDetails()
    },

});
