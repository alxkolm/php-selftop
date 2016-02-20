import 'angular';
var moment = require('moment');

function DashboardController(DashboardApi, $q, $scope){
    var vm = this;
    vm.isLoaded = false;
    vm.processDuration = null;
    vm.processList = [];
    vm.updateProcessList = (processList)=> {
        vm.processList = processList;
        $scope.$apply(); // why we need this?
    };
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
                console.log(error);
                return error;
            })
            .finally(()=>{
                vm.isLoaded = true;
            });
    }
}

angular.module('app')
    .controller('DashboardController', [
        'DashboardApi',
        '$q',
        '$scope',
        DashboardController
    ]);