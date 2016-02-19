import 'angular';
var moment = require('moment');

function DashboardController(ApiBaseUrl, DashboardApi, $q){
    var vm = this;
    vm.isLoaded = false;
    vm.processDuration = null;
    init();

    function init(){
        return DashboardApi
            .fetchData(moment().format('YYYY-MM-DD'))
            .then((data) => {

                vm.processDuration = $q.when(data.processDuration);
                vm.clusterDuration = $q.when(data.clusterDuration);
                return data;
            })
            .catch((error) => {
                processDurationDeferred.reject(error);
                return error;
            })
            .finally(()=>{
                vm.isLoaded = true;
            });
    }
}

angular.module('app')
    .controller('DashboardController', [
        'ApiBaseUrl',
        'DashboardApi',
        '$q',
        DashboardController
    ]);