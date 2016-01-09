export function Route($routeProvider) {
    $routeProvider
        .when('/dashboard', {
            template: require('./templates/dashboard/dashboard.html')
        })
        .otherwise({
            redirectTo: '/dashboard'
        });
}

