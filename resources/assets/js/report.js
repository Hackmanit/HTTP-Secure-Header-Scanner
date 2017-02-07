require('./bootstrap');

var app = new Vue({
    el: '#app',
    data: {
        show: {
            load: true,
            report: null,
        },
    },

    mounted() {
        axios.get('/jsConfig').then(response => [
            app.show.load = false,
            app.show.report = true,
        ]);
    },
});
