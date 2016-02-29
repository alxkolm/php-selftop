import 'angular';
import 'angular-route';
import {Route} from './app.route';

angular
    .module('app', ['ngRoute'])
    .constant('ApiBaseUrl', 'http://selftop.dev')
    .config(['$routeProvider', Route]);

require('./services/durationFilter');
require('./controllers/dashboard');
require('./services/dashboardApi');
require('./services/colorScale');
require('./directives/sunburst/directive');
require('./directives/color-strip/directive');
