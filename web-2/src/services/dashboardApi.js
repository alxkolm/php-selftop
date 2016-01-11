import 'angular';

function DashboardApi(ApiBaseUrl, $http) {
    return {
        fetchData:          fetchData,
        getProcesses:       getProcesses,
        getProcessDuration: getProcessDuration,
        getTimeline:        getTimeline,
        getKeys:            getKeys,
        getClusters:        getClusters,
        getClusterDuration: getClusterDuration
    };

    function fetchData(date) {
        return $http
            .get(ApiBaseUrl + '/api/dashboard/index', {
                params: {date: date}
            })
            .then((response) => {
                return response.data;
            });
    }

    function getField(field, date) {
        var deferred = $q.defer();
        fetchData(date)
            .then((data) => {
                deferred.resolve(data[field]);
            })
            .catch((error) => {
                deferred.reject(error);
            });

        return deferred.promise;
    }

    function getProcesses(date) {
        return getField('processes', date);
    }

    function getProcessDuration(date) {
        return getField('processDuration', date);
    }

    function getTimeline(date) {
        return getField('timeLine', date);
    }

    function getKeys(date) {
        return getField('keys', date);
    }

    function getClusters(date) {
        return getField('clusters', date);
    }

    function getClusterDuration(date) {
        return getField('clusterDuration', date);
    }
}

angular
    .module('app')
    .factory('DashboardApi', ['ApiBaseUrl', '$http', DashboardApi]);

