import 'angular';
var moment = require('moment');

function DashboardController(DashboardApi, $q, $scope){
    var vm = this;
    vm.isLoaded = false;
    vm.processDuration = null;
    vm.processList = [];
    vm.timeline = [];
    vm.timelineApi = null;
    vm.updateProcessList = (processList)=> {
        vm.processList = processList;
        $scope.$apply(); // why we need this?
    };
    vm.processDurationMouseHover = processDurationMouseHoverFn;
    init();

    function init(){
        return DashboardApi
            .fetchData(moment().format('YYYY-MM-DD'))
            .then((data) => {
                vm.timeline        = $q.when(data.timeLine);
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

    function processDurationMouseHoverFn(data) {
        console.log(data);
        switch (data.depth){
            case 1:
                vm.timelineApi.dimByProcess(data.process_id);
                break;
            case 2:
                vm.timelineApi.dimByWindow(data.window_id);
                break;
        }
    }
}

angular.module('app')
    .controller('DashboardController', [
        'DashboardApi',
        '$q',
        '$scope',
        DashboardController
    ]);