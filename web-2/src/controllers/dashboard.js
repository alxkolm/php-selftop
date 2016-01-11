import 'angular';

function DashboardController(ApiBaseUrl, DashboardApi, $q){
    var vm = this;
    var processDurationDeferred = $q.defer();
    vm.processDuration = processDurationDeferred.promise;
    init();

    function init(){
        return DashboardApi
            .fetchData('2016-01-11')
            .then((data) => {
                processDurationDeferred.resolve(data.processDuration);
                return data;
            })
            .catch((error) => {
                processDurationDeferred.reject(error);
                return error;
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