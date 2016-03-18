import 'angular';
var moment = require('moment');

function DashboardController(DashboardApi, $q, $scope){
    var vm = this;
    vm.isLoaded = false;
    vm.processDuration = null;
    vm.processList = [];
    vm.timeline = [];
    vm.timelineApi = null;
    vm.keys = [];
    vm.commonTimeDomain = null;
    vm.updateProcessList = (processList)=> {
        vm.processList = processList;
        $scope.$apply(); // why we need this?
    };
    vm.processDurationMouseHover = processDurationMouseHoverFn;
    vm.processDurationMouseLeave = processDurationMouseLeaveFn;
    init();

    function init(){
        return DashboardApi
            .fetchData(moment().format('YYYY-MM-DD'))
            .then((data) => {
                vm.timeline        = $q.when(data.timeLine);
                vm.processDuration = $q.when(data.processDuration);
                vm.clusterDuration = $q.when(data.clusterDuration);
                vm.keys            = $q.when(data.keys);
                $q.all([vm.timeline, vm.keys])
                    .then(getCommonTimeDomain)
                    .then((commonTimeDomain) => {
                        vm.commonTimeDomain = commonTimeDomain;
                        return commonTimeDomain;
                    });
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

    function processDurationMouseLeaveFn(data){
        vm.timelineApi.undim();
    }

    /**
     * Calculate common time domain
     * @returns {*[]}
     */
    function getCommonTimeDomain([timelineData, keysData]) {
        // Filter out zero values from keys data
        var keys = keysData.filter((a)=>{return a.count > 0});

        // Merge all dates and convert its to Date-objects
        var dates = [].concat(
            timelineData.reduce((result, item) => {
                result.push(new Date(item.start));
                result.push(new Date(item.end));
                return result;
            }, []),
            keys.map((a) => {return new Date(a.date)})
        );

        return [
            new Date(Math.min.apply(this, dates)),
            new Date(Math.max.apply(this, dates))
        ];
    }
}

angular.module('app')
    .controller('DashboardController', [
        'DashboardApi',
        '$q',
        '$scope',
        DashboardController
    ]);